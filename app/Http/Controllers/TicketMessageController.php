<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketMessageController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $message = $this->persist($request, $ticket);

        return back()->with('success', $message->is_internal ? 'Catatan internal berhasil disimpan' : 'Pesan berhasil dikirim');
    }

    /**
     * JSON variant used by the dashboard quick-view modal and quick-chat popover.
     */
    public function quickStore(Request $request, Ticket $ticket): JsonResponse
    {
        $message = $this->persist($request, $ticket);
        $message->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => TicketController::messagePayload($message),
        ]);
    }

    /**
     * Authorize, validate and store a reply or internal note together with its activity entry.
     */
    private function persist(Request $request, Ticket $ticket): TicketMessage
    {
        $this->authorize('reply', $ticket);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'in:internal,external'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $isInternal = $request->boolean('is_internal') || ($validated['type'] ?? null) === 'internal';

        if ($isInternal) {
            $this->authorize('addInternalNote', $ticket);
        }

        $userId = $request->user()->id;

        // Messages are never emailed to the customer here; internal notes in particular stay in-app only.
        return DB::transaction(function () use ($ticket, $validated, $isInternal, $userId) {
            $message = $ticket->messages()->create([
                'user_id' => $userId,
                'message' => $validated['message'],
                'is_internal' => $isInternal,
                'type' => $isInternal ? 'internal' : 'external',
            ]);

            $ticket->activities()->create([
                'user_id' => $userId,
                'action' => $isInternal ? 'internal_note' : 'reply',
                'new_value' => $validated['message'],
            ]);

            return $message;
        });
    }
}
