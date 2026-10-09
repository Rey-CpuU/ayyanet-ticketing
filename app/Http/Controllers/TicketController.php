<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\User;
use App\Services\TicketNotifier;
use App\Services\TicketWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /** Sort options for the ticket list: key => label. */
    public const SORTS = [
        'newest' => 'Terbaru',
        'oldest' => 'Terlama',
        'priority' => 'Prioritas tertinggi',
        'sla' => 'SLA terdekat',
        'updated' => 'Terakhir diperbarui',
    ];

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

        $user = $request->user();
        $query = Ticket::with(['customer', 'assignee']);

        if ($user->hasRole('lapangan')) {
            $query->visibleTo($user);
        } else {
            $query->where('created_by', $user->id);
        }

        return $this->listView($request, $query, 'My Tickets', true);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $selectedCustomer = Customer::find($request->old('customer_id', $request->query('customer_id')));

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

        // Numbering, SLA, the activity entry and notifications are handled by the workflow
        // (shared with the Telegram bot). New tickets always start as Open.
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
            ? User::whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name', 'role'])
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
        $newStatus = TicketStatus::from($request->status ?? $ticket->status);
        $resolutionNote = $request->validated('resolution_note');

        if ($error = $workflow->transitionError($ticket, $newStatus, $resolutionNote)) {
            return back()->withErrors($error)->withInput();
        }

        $updateData = $request->safe()->only([
            'customer_id',
            'title',
            'description',
            'category',
            'olt',
            'location',
        ]);

        if ($request->hasFile('attachment')) {
            $this->deleteAttachment($ticket->attachment_path);
            $updateData['attachment_path'] = $this->storeAttachment($request->file('attachment'));
        }

        $actor = $request->user();

        DB::transaction(function () use ($ticket, $updateData, $request, $newStatus, $resolutionNote, $workflow, $actor) {
            $oldValues = $ticket->only(self::AUDITED_FIELDS);

            $ticket->update($updateData);

            // Priority and status go through the workflow so SLA and the activity stream stay consistent.
            if ($request->filled('priority')) {
                $workflow->changePriority($ticket, $request->priority, $actor);
            }

            if ($newStatus !== $ticket->statusEnum()) {
                $workflow->changeStatus($ticket, $newStatus, $resolutionNote, $actor);
            } elseif ($newStatus->isResolved() && filled($resolutionNote) && $resolutionNote !== $ticket->resolution_note) {
                $ticket->update(['resolution_note' => $resolutionNote]);
            }

            $newValues = $ticket->only(self::AUDITED_FIELDS);

            foreach (self::AUDITED_FIELDS as $field) {
                if ($oldValues[$field] !== $newValues[$field]) {
                    TicketAuditLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $actor->id,
                        'action' => 'updated',
                        'old_value' => json_encode([$field => $oldValues[$field]]),
                        'new_value' => json_encode([$field => $newValues[$field]]),
                    ]);
                }
            }
        });

        app(TicketNotifier::class)->customerUpdate($ticket, 'Status ticket diperbarui ke '.$newStatus->value);

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
     * Apply search, filters, sorting and pagination, and render the ticket list.
     */
    private function listView(Request $request, Builder $query, string $pageTitle, bool $showMyTicketsOnly)
    {
        $user = $request->user();
        $filters = $this->listFilters($request);

        if ($filters['trashed'] === 'with') {
            $query->withTrashed();
        } elseif ($filters['trashed'] === 'only') {
            $query->onlyTrashed();
        }

        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';

            $query->where(fn (Builder $q) => $q
                ->where('ticket_number', 'like', $term)
                ->orWhere('title', 'like', $term)
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', $term)));
        }

        foreach (['status', 'priority', 'category'] as $field) {
            if ($filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        if ($filters['assigned_to'] === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($filters['assigned_to'] !== '') {
            $query->where('assigned_to', (int) $filters['assigned_to']);
        }

        match ($filters['sort']) {
            'oldest' => $query->oldest()->oldest('id'),
            'priority' => $query->orderByRaw("CASE priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END")->latest(),
            'sla' => $query->orderByRaw('sla_deadline IS NULL')->orderBy('sla_deadline'),
            'updated' => $query->latest('updated_at'),
            default => $query->latest()->latest('id'),
        };

        return view('tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'pageTitle' => $pageTitle,
            'showMyTicketsOnly' => $showMyTicketsOnly,
            'filters' => $filters,
            'categories' => Ticket::visibleTo($user)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'assignees' => User::whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name']),
            'sorts' => self::SORTS,
        ]);
    }

    /**
     * Read list filters from the query string; unknown values are ignored instead of failing.
     *
     * @return array<string, string>
     */
    private function listFilters(Request $request): array
    {
        $pick = fn (string $key, array $allowed) => in_array($request->query($key), $allowed, true) ? $request->query($key) : '';
        $assignedTo = (string) $request->query('assigned_to', '');

        return [
            'q' => Str::limit(trim((string) $request->query('q', '')), 100, ''),
            'status' => $pick('status', Ticket::STATUSES),
            'priority' => $pick('priority', Ticket::PRIORITIES),
            'category' => is_string($request->query('category')) ? Str::limit($request->query('category'), 100, '') : '',
            'assigned_to' => $assignedTo === 'unassigned' || ctype_digit($assignedTo) ? $assignedTo : '',
            'sort' => $pick('sort', array_keys(self::SORTS)) ?: 'newest',
            // Deleted tickets are only visible to admins.
            'trashed' => $request->user()->isAdmin() ? $pick('trashed', ['with', 'only']) : '',
        ];
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
