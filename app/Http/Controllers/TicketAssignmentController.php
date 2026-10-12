<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketAssignmentController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            // Only active staff accounts can be responsible for a ticket.
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('role', User::ROLES)->whereNull('deactivated_at'),
            ],
        ], [
            'assigned_to.exists' => 'Penanggung jawab tidak valid atau akunnya sudah dinonaktifkan.',
        ]);

        $assignee = isset($validated['assigned_to']) ? User::findOrFail($validated['assigned_to']) : null;

        // The workflow logs the activity and notifies the new assignee (in-app + email) and the Telegram group.
        $changed = $workflow->assign($ticket, $assignee, $request->user());
        $message = $changed ? 'Penanggung jawab ticket berhasil diperbarui.' : 'Penanggung jawab ticket tidak berubah.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'changed' => $changed,
                'message' => $message,
                'assignee' => $assignee ? ['id' => $assignee->id, 'name' => $assignee->name] : null,
            ]);
        }

        return back()->with('success', $message);
    }
}
