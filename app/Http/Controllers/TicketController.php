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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['customer', 'creator', 'assignee'])->latest()->get();
        $assignableUsers = User::whereNotNull('role')->orderBy('name')->get();
        $uniqueCustomers = Customer::orderBy('name')->get();

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
            'impact'        => $request->impact ?: $auto['impact'],
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

        return back()->with('success', $assignedTo
            ? "Tiket ditugaskan ke {$newAssigneeName}."
            : 'Tiket dilepas (belum ada penanggung jawab).');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['customer', 'messages', 'messages.user', 'activities.user', 'assignee'])->findOrFail($id);

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
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'nullable|string',
            'priority'    => 'required|in:Low,Medium,High',
            'impact'      => 'nullable|in:Low,Medium,High,Critical',
            'status'      => 'required|in:Open,Checking,Waiting Customer,Escalated,Solved,Closed',
            'olt'         => 'nullable|string',
            'location'    => 'nullable|string',
        ]);

        $ticket->update($request->only([
            'title', 'description', 'category', 'priority', 'impact', 'status', 'olt', 'location',
        ]));

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil diperbarui');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket berhasil dihapus');
    }
}
