<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketMessageController extends Controller
{
    public function store(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $request->validate([
            'message' => 'required|string',
        ]);

        $isInternal = $request->boolean('is_internal');

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'message'     => $request->message,
            'is_internal' => $isInternal,
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => $isInternal ? 'internal_note' : 'reply',
            'new_value' => $request->message,
        ]);

        return back()->with('success', $isInternal
            ? 'Internal note ditambahkan.'
            : 'Pesan berhasil dikirim!');
    }
}
