<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\TicketWorkflow;
use App\Support\DisplayTime;
use App\Support\Search;
use App\Support\TicketFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /** Sort options for the ticket list: key => label. */
    public const SORTS = TicketFilters::SORTS;

    /** Fields whose changes are written to the audit log on update. */
    private const AUDITED_FIELDS = ['title', 'description', 'priority', 'status', 'category', 'assigned_to'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $query = Ticket::with(['customer', 'creator', 'assignee'])
            ->visibleTo($request->user());

        return $this->listView($request, $query, 'All Tickets', false);
    }

    public function myTickets(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $query = TicketFilters::onlyMine(Ticket::with(['customer', 'assignee']), $request->user());

        return $this->listView($request, $query, 'My Tickets', true);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Ticket::class);

        // Only a plain numeric id preselects a customer (?customer_id[]=1 or garbage is ignored);
        // soft-deleted customers cannot be picked for a new ticket.
        $customerId = $request->old('customer_id', $request->query('customer_id'));
        $selectedCustomer = is_scalar($customerId) && ctype_digit((string) $customerId)
            ? Customer::find((int) $customerId)
            : null;

        return view('tickets.create', compact('selectedCustomer'));
    }

    public function store(StoreTicketRequest $request, TicketWorkflow $workflow)
    {
        $data = [
            'customer_id' => $request->customer_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category ?? 'Email',
            'priority' => $request->priority ?? 'Medium',
            'olt' => $request->olt,
            'location' => $request->location,
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $this->storeAttachment($request->file('attachment'));
        }

        // Numbering, SLA, the activity entry and notifications are handled by the workflow.
        // New tickets always start as Open.
        $ticket = $workflow->create($data, $request->user());

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil dibuat');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->touchVisited();

        $ticket->load([
            'customer',
            'assignee',
            'messages.user',
            'activities' => fn ($query) => $query->with('user')->latest('id'),
        ]);

        $attachmentAvailable = $this->attachmentDisk($ticket->attachment_path) !== null;
        $allowedStatuses = $ticket->statusEnum()->allowedTransitions();
        $assignableUsers = $request->user()->can('assign', $ticket)
            ? User::active()->whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name', 'role'])
            : collect();

        return view('tickets.show', compact('ticket', 'attachmentAvailable', 'allowedStatuses', 'assignableUsers'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $selectedCustomer = Customer::withTrashed()->find(old('customer_id', $ticket->customer_id));
        $statusOptions = [$ticket->statusEnum(), ...$ticket->statusEnum()->allowedTransitions()];

        return view('tickets.edit', compact('ticket', 'selectedCustomer', 'statusOptions'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $newStatus = TicketStatus::from($request->validated('status') ?? $ticket->status);
        $resolutionNote = $request->validated('resolution_note');
        // Field technicians (lapangan) may only change status, OLT/location and add notes;
        // UpdateTicketRequest drops every other field from validated() for them.
        $canEditDetails = $request->canEditDetails();

        if ($error = $workflow->transitionError($ticket, $newStatus, $resolutionNote)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => collect($error)->flatten()->first(), 'errors' => $error], 422);
            }

            return back()->withErrors($error)->withInput();
        }

        $updateData = $request->safe()->only($canEditDetails
            ? ['customer_id', 'title', 'description', 'category', 'olt', 'location']
            : ['olt', 'location']);

        $oldAttachment = null;

        if ($canEditDetails && $request->hasFile('attachment')) {
            $oldAttachment = $ticket->attachment_path;
            $updateData['attachment_path'] = $this->storeAttachment($request->file('attachment'));
        }

        $priority = $canEditDetails ? $request->validated('priority') : null;
        $actor = $request->user();

        try {
            DB::transaction(function () use ($ticket, $updateData, $priority, $newStatus, $resolutionNote, $workflow, $actor) {
                $oldValues = $ticket->only(self::AUDITED_FIELDS);

                $ticket->update($updateData);

                // Priority and status go through the workflow so SLA, the activity stream and the
                // notifications (incl. the customer email on a real status change) stay consistent.
                if (filled($priority)) {
                    $workflow->changePriority($ticket, $priority, $actor);
                }

                if ($newStatus !== $ticket->statusEnum()) {
                    $workflow->changeStatus($ticket, $newStatus, $resolutionNote, $actor);
                } elseif ($newStatus->isResolved() && filled($resolutionNote) && $resolutionNote !== $ticket->resolution_note) {
                    $ticket->update(['resolution_note' => $resolutionNote]);
                }

                $newValues = $ticket->only(self::AUDITED_FIELDS);

                foreach (self::AUDITED_FIELDS as $field) {
                    if ($oldValues[$field] !== $newValues[$field]) {
                        // The model casts these columns to JSON, so arrays are passed (no json_encode).
                        TicketAuditLog::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $actor->id,
                            'action' => 'updated',
                            'old_value' => [$field => $oldValues[$field]],
                            'new_value' => [$field => $newValues[$field]],
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            // The row still points at the old file; drop the upload that never got attached.
            if (isset($updateData['attachment_path'])) {
                $this->deleteAttachment($updateData['attachment_path']);
            }

            throw $e;
        }

        // The replaced file is only removed once the new path is committed.
        $this->deleteAttachment($oldAttachment);

        if ($request->expectsJson()) {
            $ticket->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Ticket berhasil diupdate',
                'ticket' => $ticket->only(['id', 'title', 'description', 'status', 'priority', 'category', 'olt', 'location', 'resolution_note']),
                'allowed_statuses' => array_column($ticket->statusEnum()->allowedTransitions(), 'value'),
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil diupdate');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dihapus');
    }

    public function restore(Ticket $ticket)
    {
        $this->authorize('restore', $ticket);

        $ticket->restore();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dipulihkan');
    }

    public function forceDelete(Ticket $ticket)
    {
        $this->authorize('forceDelete', $ticket);

        $attachmentPath = $ticket->attachment_path;

        $ticket->forceDelete();

        // Remove the file only after the row is gone, so a failed delete never orphans the record.
        $this->deleteAttachment($attachmentPath);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dihapus secara permanen');
    }

    /**
     * Serve a ticket attachment to users who may view the ticket.
     */
    public function downloadAttachment(Ticket $ticket): StreamedResponse
    {
        $this->authorize('view', $ticket);

        $disk = $this->attachmentDisk($ticket->attachment_path);

        abort_if($disk === null, 404, 'Lampiran tidak ditemukan.');

        return Storage::disk($disk)->download($ticket->attachment_path, basename($ticket->attachment_path));
    }

    /**
     * Dashboard live search: up to 10 visible tickets matching number, title, category or customer.
     */
    public function liveSearch(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $q = Search::input($request, 'q');

        if ($q === '') {
            return response()->json([]);
        }

        $tickets = Ticket::visibleTo($request->user())
            ->with(['customer:id,name,customer_id,phone,deleted_at', 'assignee:id,name'])
            ->where(fn (Builder $query) => Search::whereContains($query, ['ticket_number', 'title', 'category'], $q)
                ->orWhereHas('customer', fn (Builder $c) => Search::whereContains($c, ['name', 'customer_id', 'phone'], $q)))
            ->latest()
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Ticket $t) => [
                'id' => $t->id,
                'url' => route('tickets.show', $t),
                'ticket_number' => $t->ticket_number,
                'title' => $t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'category' => $t->category,
                'customer_name' => $t->customer->name ?? 'Unknown',
                'customer_deleted' => (bool) $t->customer?->trashed(),
                'customer_phone' => $t->customer->phone ?? null,
                'customer_id' => $t->customer->customer_id ?? null,
                'initial' => strtoupper(substr($t->customer->name ?? '?', 0, 2)),
                'assignee_name' => $t->assignee->name ?? 'Belum ditugaskan',
                'created_at' => $t->created_at?->diffForHumans(),
            ]);

        return response()->json($tickets);
    }

    /**
     * JSON payload for the dashboard quick-view modal and quick-chat popover.
     */
    public function quickDetails(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->touchVisited();
        $ticket->load([
            'customer',
            'assignee:id,name',
            'messages' => fn ($query) => $query->with('user:id,name'), // chronological: see Ticket::messages()
            'activities' => fn ($query) => $query->with('user:id,name')->latest('id')->limit(30),
        ]);

        $user = $request->user();
        $sla = $ticket->slaSummary();

        return response()->json([
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'category' => $ticket->category,
            'olt' => $ticket->olt,
            'location' => $ticket->location,
            'customer_id' => $ticket->customer_id,
            'resolution_note' => $ticket->resolution_note,
            'created_at' => DisplayTime::format($ticket->created_at),
            'created_human' => $ticket->created_at?->diffForHumans(),
            'edit_url' => route('tickets.edit', $ticket),
            'show_url' => route('tickets.show', $ticket),
            'update_url' => route('tickets.update', $ticket),
            'message_url' => route('tickets.quick-message', $ticket),
            'customer' => [
                'name' => $ticket->customer->name ?? '-',
                'deleted' => (bool) $ticket->customer?->trashed(),
                'customer_id' => $ticket->customer->customer_id ?? '-',
                'phone' => $ticket->customer->phone ?? '-',
                'address' => $ticket->customer->address ?? '-',
            ],
            'assignee' => [
                'id' => $ticket->assigned_to,
                'name' => $ticket->assignee->name ?? 'Belum ditugaskan',
            ],
            'sla' => $sla ? [
                'state' => $sla['state'],
                'label' => $sla['label'],
                'running' => $sla['running'],
                'deadline' => $sla['deadline']->toIso8601String(),
                'deadline_label' => DisplayTime::format($sla['deadline']),
            ] : null,
            'allowed_statuses' => array_column($ticket->statusEnum()->allowedTransitions(), 'value'),
            'categories' => array_values(array_unique(array_filter([...Ticket::CATEGORIES, $ticket->category]))),
            'can' => [
                'update' => $user->can('update', $ticket),
                'update_details' => $user->can('updateDetails', $ticket),
                'reply' => $user->can('reply', $ticket),
                'internal_note' => $user->can('addInternalNote', $ticket),
            ],
            'messages' => $ticket->messages->map(fn ($msg) => self::messagePayload($msg))->values(),
            'activities' => $ticket->activities->map(fn ($activity) => [
                'id' => $activity->id,
                'user_name' => $activity->user->name ?? 'Sistem',
                'action' => $activity->action,
                'action_label' => $activity->label(),
                'old_value' => $activity->old_value,
                'new_value' => $activity->new_value,
                'created_at' => DisplayTime::format($activity->created_at),
            ])->values(),
        ]);
    }

    /**
     * Shape of a message in the quick view / quick chat JSON.
     *
     * @return array<string, mixed>
     */
    public static function messagePayload(TicketMessage $msg): array
    {
        $sender = $msg->user->name ?? 'Customer';

        return [
            'id' => $msg->id,
            'user_name' => $sender,
            'user_initials' => strtoupper(substr($sender, 0, 2)),
            'is_internal' => (bool) $msg->is_internal,
            'message' => $msg->message,
            'created_at' => DisplayTime::format($msg->created_at, 'H:i'),
            'date_str' => DisplayTime::format($msg->created_at),
        ];
    }

    /**
     * Apply search, filters, sorting and pagination, and render the ticket list.
     */
    private function listView(Request $request, Builder $query, string $pageTitle, bool $showMyTicketsOnly)
    {
        $user = $request->user();
        $filters = TicketFilters::fromRequest($request);

        $query->withCount('messages');
        TicketFilters::apply($query, $filters);
        TicketFilters::sort($query, $filters['sort']);

        return view('tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'pageTitle' => $pageTitle,
            'showMyTicketsOnly' => $showMyTicketsOnly,
            'filters' => $filters,
            // Export links carry the active filters (and the "my tickets" scope).
            'exportParameters' => TicketFilters::queryParameters($filters) + ($showMyTicketsOnly ? ['mine' => '1'] : []),
            'categories' => Ticket::visibleTo($user)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            // Filter list: deactivated accounts stay so their past tickets remain filterable.
            'assignees' => User::whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name', 'deactivated_at']),
            'sorts' => self::SORTS,
        ]);
    }

    private function storeAttachment(UploadedFile $file): string
    {
        return $file->store('attachments', Ticket::ATTACHMENT_DISK);
    }

    /**
     * Resolve which disk holds the attachment. Older uploads were stored on the public disk,
     * so fall back to it until they have been moved to private storage.
     */
    private function attachmentDisk(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        foreach ([Ticket::ATTACHMENT_DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    private function deleteAttachment(?string $path): void
    {
        if (! $path) {
            return;
        }

        foreach ([Ticket::ATTACHMENT_DISK, 'public'] as $disk) {
            Storage::disk($disk)->delete($path);
        }
    }
}
