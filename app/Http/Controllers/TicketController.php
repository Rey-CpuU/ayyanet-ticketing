<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Customer;
use App\Models\TicketActivity;
use App\Models\User;
use App\Support\TicketClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Ticket::create([
            'ticket_number' => 'TCK-' . time(),
            'customer_id'   => $request->customer_id,
            'created_by'    => Auth::id() ?? 1,
            'title'         => $request->title,
            'description'   => $request->description,
            'category'      => $request->category ?: $auto['category'],
            'priority'      => $request->priority ?: $auto['priority'],
            'impact'        => $request->impact ?: $auto['impact'],
            'status'        => 'Open',
            'olt'           => $request->olt,
            'location'      => $request->location,
        ]);

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
        }

        return back()->with('success', "Status diubah: {$old} → {$new}");
    }

    /**
     * Place a ticket with a responsible agent. Passing an empty value unassigns.
     */
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

        $newAssignee = $assignedTo
            ? (User::find($assignedTo)->name ?? 'Unknown')
            : 'Unassigned';

        $ticket->update(['assigned_to' => $assignedTo ?: null]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'assignment',
            'old_value' => $oldAssignee,
            'new_value' => $newAssignee,
        ]);

        return back()->with('success', $assignedTo
            ? "Tiket ditugaskan ke {$newAssignee}."
            : 'Tiket dilepas (belum ada penanggung jawab).');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['customer', 'messages', 'messages.user', 'activities.user', 'assignee'])->findOrFail($id);

        $visibleMessages = auth()->check()
            ? $ticket->messages
            : $ticket->messages->where('is_internal', false);

        $assignableUsers = User::whereNotNull('role')->orderBy('name')->get();

        return view('tickets.show', compact('ticket', 'visibleMessages', 'assignableUsers'));
    }

    public function edit(Ticket $ticket)
    {
        //
    }

    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    public function destroy(Ticket $ticket)
    {
        //
    }
}
