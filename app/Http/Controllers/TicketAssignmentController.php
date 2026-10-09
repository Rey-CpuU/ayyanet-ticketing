<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TicketAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketAssignmentController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $newAssigneeId = $validated['assigned_to'] ?? null;
        $newAssigneeId = $newAssigneeId === null ? null : (int) $newAssigneeId;
        $oldAssigneeId = $ticket->assigned_to === null ? null : (int) $ticket->assigned_to;

        if ($newAssigneeId === $oldAssigneeId) {
            return back()->with('success', 'Penanggung jawab ticket tidak berubah.');
        }

        $oldName = $ticket->assignee?->name ?? 'Unassigned';
        $newAssignee = $newAssigneeId ? User::findOrFail($newAssigneeId) : null;
        $newName = $newAssignee?->name ?? 'Unassigned';

        DB::transaction(function () use ($ticket, $newAssigneeId, $oldName, $newName) {
            $ticket->update(['assigned_to' => $newAssigneeId]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'assignment',
                'old_value' => $oldName,
                'new_value' => $newName,
            ]);
        });

        // Self-assignment needs no notification.
        if ($newAssignee && ! $newAssignee->is($request->user())) {
            $newAssignee->notify(new TicketAssigned($ticket, $request->user()));
        }

        return back()->with('success', 'Penanggung jawab ticket berhasil diperbarui.');
    }
}
