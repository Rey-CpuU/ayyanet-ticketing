<?php

namespace App\Http\Controllers;

use App\Mail\TicketAssigned;
use App\Mail\TicketCreated;
use App\Mail\TicketStatusChanged;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Support\TicketClassifier;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['customer:id,customer_id,name,phone,address', 'creator:id,name', 'assignee:id,name'])->latest()->get();
        $assignableUsers = User::select('id', 'name', 'role')->whereNotNull('role')->orderBy('name')->get();
        $uniqueCustomers = Customer::select('id', 'name')->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'assignableUsers', 'uniqueCustomers'));
    }

    public function create()
    {
        $customers = Customer::all();

        return view('tickets.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'title'       => 'required',
            'description' => 'required',
        ]);

        $auto = TicketClassifier::classify($request->title, $request->description ?? '');

        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . time(),
            'customer_id'   => $request->customer_id,
            'created_by'    => Auth::id(),
            'title'         => $request->title,
            'description'   => $request->description,
            'category'      => $request->category ?: $auto['category'],
            'priority'      => $request->priority ?: $auto['priority'],
            'status'        => 'Open',
            'olt'           => $request->olt,
            'location'      => $request->location,
        ]);

        $staffUsers = User::whereIn('role', ['admin', 'cs'])->get();
        foreach ($staffUsers as $user) {
            Mail::to($user->email)->queue(new TicketCreated($ticket));
            $user->notify(new \App\Notifications\TicketCreatedNotification($ticket));
        }

        return redirect('/tickets')
            ->with('success', 'Ticket berhasil dibuat');
    }

    public function classify(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        return response()->json(
            TicketClassifier::classify($request->title, $request->description ?? '')
        );
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $allowed = ['Open', 'Checking', 'Waiting Customer', 'Escalated', 'Solved', 'Closed'];

        $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowed)],
        ]);

        $old = $ticket->status;
        $new = $request->status;

        if ($old !== $new) {
            $ticket->update(['status' => $new]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'action'    => 'status_change',
                'old_value' => $old,
                'new_value' => $new,
            ]);

            if (in_array($new, ['Solved', 'Closed'])) {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(new TicketStatusChanged($ticket));
                    $admin->notify(new \App\Notifications\TicketStatusChangedNotification($ticket));
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Status diubah: {$old} → {$new}"]);
        }

        return back()->with('success', "Status diubah: {$old} → {$new}");
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $oldAssignee = $ticket->assignee->name ?? 'Unassigned';
        $assignedTo  = $request->input('assigned_to');

        if ((int) $ticket->assigned_to === (int) $assignedTo) {
            return back();
        }

        $newAssigneeUser = $assignedTo ? User::find($assignedTo) : null;
        $newAssigneeName = $newAssigneeUser ? $newAssigneeUser->name : 'Unassigned';

        $ticket->update(['assigned_to' => $assignedTo ?: null]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'assignment',
            'old_value' => $oldAssignee,
            'new_value' => $newAssigneeName,
        ]);

        if ($newAssigneeUser) {
            Mail::to($newAssigneeUser->email)->queue(new TicketAssigned($ticket));
            $newAssigneeUser->notify(new \App\Notifications\TicketAssignedNotification($ticket));
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $assignedTo
                ? "Tiket ditugaskan ke {$newAssigneeName}."
                : 'Tiket dilepas (belum ada penanggung jawab).']);
        }

        return back()->with('success', $assignedTo
            ? "Tiket ditugaskan ke {$newAssigneeName}."
            : 'Tiket dilepas (belum ada penanggung jawab).');
    }

    public function show(int|string $id)
    {
        $ticket = Ticket::with(['customer', 'messages', 'messages.user', 'activities.user', 'assignee'])->findOrFail($id);
        $ticket->touchVisited();

        $visibleMessages = Auth::check()
            ? $ticket->messages
            : $ticket->messages->where('is_internal', false);

        $assignableUsers = User::whereNotNull('role')->orderBy('name')->get();

        return view('tickets.show', compact('ticket', 'visibleMessages', 'assignableUsers'));
    }

    public function edit(Ticket $ticket)
    {
        $customers = Customer::all();

        return view('tickets.edit', compact('ticket', 'customers'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'nullable|string',
            'priority'    => 'required|in:Low,Medium,High',
            'status'      => 'required|in:Open,Checking,Waiting Customer,Escalated,Solved,Closed',
            'olt'         => 'nullable|string',
            'location'    => 'nullable|string',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $request->status;

        $ticket->update($request->only([
            'customer_id', 'title', 'description', 'category', 'priority', 'status', 'olt', 'location',
        ]));

        if ($oldStatus !== $newStatus) {
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'action'    => 'status_change',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);

            if (in_array($newStatus, ['Solved', 'Closed'])) {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(new TicketStatusChanged($ticket));
                    $admin->notify(new \App\Notifications\TicketStatusChangedNotification($ticket));
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Tiket berhasil diperbarui']);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil diperbarui');
    }

    public function liveSearch(Request $request)
    {
        $q = trim($request->input('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $tickets = Ticket::with(['customer', 'assignee'])
            ->where(function ($query) use ($q) {
                $query->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                           ->orWhere('customer_id', 'like', "%{$q}%")
                           ->orWhere('phone', 'like', "%{$q}%");
                    });
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($t) {
                return [
                    'id'            => $t->id,
                    'url'           => route('tickets.show', $t),
                    'ticket_number' => $t->ticket_number,
                    'title'         => $t->title,
                    'status'        => $t->status,
                    'priority'      => $t->priority,
                    'category'      => $t->category,
                    'customer_name'  => $t->customer->name ?? 'Unknown',
                    'customer_phone' => $t->customer->phone ?? null,
                    'customer_id'    => $t->customer->customer_id ?? null,
                    'initial'        => strtoupper(substr($t->customer->name ?? '?', 0, 2)),
                    'assignee_name'  => $t->assignee->name ?? 'Unassigned',
                    'created_at'     => $t->created_at->diffForHumans(),
                ];
            });

        return response()->json($tickets);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket berhasil dihapus');
    }

    public function quickDetails(Ticket $ticket)
    {
        $ticket->touchVisited();
        $ticket->load(['customer', 'messages.user', 'assignee', 'activities.user']);

        $messages = $ticket->messages->map(function ($msg) {
            $sender = $msg->user->name ?? 'CS Ayyanet';
            $msgDate = \Illuminate\Support\Carbon::parse($msg->created_at);
            return [
                'id'            => $msg->id,
                'user_name'     => $sender,
                'user_initials' => strtoupper(substr($sender, 0, 2)),
                'is_internal'   => (bool) $msg->is_internal,
                'message'       => $msg->message,
                'created_at'    => $msgDate->format('H:i'),
                'date_str'      => $msgDate->format('d M Y, H:i'),
            ];
        });

        $createdAt = \Illuminate\Support\Carbon::parse($ticket->created_at);

        return response()->json([
            'id'            => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'title'         => $ticket->title,
            'description'   => $ticket->description,
            'status'        => $ticket->status,
            'priority'      => $ticket->priority,
            'category'      => $ticket->category,
            'olt'           => $ticket->olt,
            'location'      => $ticket->location,
            'created_at'    => $createdAt->format('d M Y, H:i'),
            'created_human' => $createdAt->diffForHumans(),
            'edit_url'      => route('tickets.edit', $ticket),
            'show_url'      => route('tickets.show', $ticket),
            'customer'      => [
                'name'        => $ticket->customer->name ?? 'N/A',
                'customer_id' => $ticket->customer->customer_id ?? 'N/A',
                'phone'       => $ticket->customer->phone ?? 'N/A',
                'address'     => $ticket->customer->address ?? 'N/A',
            ],
            'assignee'      => [
                'id'   => $ticket->assigned_to,
                'name' => $ticket->assignee->name ?? 'Unassigned',
            ],
            'messages'      => $messages->values(),
            'activities'    => $ticket->activities->map(function ($activity) {
                $actionLabels = [
                    'status_change' => 'Status Changed',
                    'assignment'    => 'Assignment Updated',
                    'created'       => 'Ticket Created',
                ];
                $actDate = \Illuminate\Support\Carbon::parse($activity->created_at);
                return [
                    'id'           => $activity->id,
                    'user_name'    => $activity->user->name ?? 'System',
                    'action'       => $activity->action,
                    'action_label' => $actionLabels[$activity->action] ?? $activity->action,
                    'old_value'    => $activity->old_value,
                    'new_value'    => $activity->new_value,
                    'created_at'   => $actDate->format('d M Y, H:i'),
                ];
            })->values(),
        ]);
    }
}
