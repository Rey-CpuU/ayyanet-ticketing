<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Support\TicketClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketClassifyController extends Controller
{
    /**
     * Suggest category and priority for a ticket that is being written.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        return response()->json(
            TicketClassifier::classify($validated['title'], $validated['description'] ?? '')
        );
    }
}
