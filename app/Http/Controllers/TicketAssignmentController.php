<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketWorkflow;
use Illuminate\Http\Request;

class TicketAssignmentController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $assignee = isset($validated['assigned_to']) ? User::findOrFail($validated['assigned_to']) : null;

        // The workflow logs the activity and notifies the new assignee (in-app + email) and Telegram.
        if (! $workflow->assign($ticket, $assignee, $request->user())) {
            return back()->with('success', 'Penanggung jawab ticket tidak berubah.');
        }

        return back()->with('success', 'Penanggung jawab ticket berhasil diperbarui.');
    }
}
