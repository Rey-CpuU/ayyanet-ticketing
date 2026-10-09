<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketMessageController extends Controller
{
    public function store(Request $request, Ticket $ticket)
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

        // Messages are never emailed to the customer here; internal notes in particular stay in-app only.
        DB::transaction(function () use ($ticket, $validated, $isInternal) {
            $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'is_internal' => $isInternal,
                'type' => $isInternal ? 'internal' : 'external',
            ]);

            $ticket->activities()->create([
                'user_id' => Auth::id(),
                'action' => $isInternal ? 'internal_note' : 'reply',
                'new_value' => $validated['message'],
            ]);
        });

        return back()->with('success', $isInternal ? 'Catatan internal berhasil disimpan' : 'Pesan berhasil dikirim');
    }
}
