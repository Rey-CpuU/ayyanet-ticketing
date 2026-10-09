<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketStatusController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket, TicketWorkflow $workflow)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(TicketStatus::values())],
            'resolution_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $to = TicketStatus::from($validated['status']);
        $note = $validated['resolution_note'] ?? null;

        if ($ticket->statusEnum() === $to) {
            return $this->respond($request, $ticket, 'Status ticket tidak berubah.');
        }

        if ($error = $workflow->transitionError($ticket, $to, $note)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => collect($error)->flatten()->first(), 'errors' => $error], 422);
            }

            return back()->withErrors($error)->withInput();
        }

        $workflow->changeStatus($ticket, $to, $note, $request->user());

        return $this->respond($request, $ticket, "Status ticket diubah ke {$to->value}.");
    }

    private function respond(Request $request, Ticket $ticket, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $ticket->status,
                'allowed_statuses' => array_column($ticket->statusEnum()->allowedTransitions(), 'value'),
            ]);
        }

        return back()->with('success', $message);
    }
}
