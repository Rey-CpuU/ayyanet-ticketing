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

    public function quickStore(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $isInternal = $request->boolean('is_internal');

        $msg = TicketMessage::create([
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

        $sender = Auth::user()->name ?? 'CS Ayyanet';

        return response()->json([
            'success' => true,
            'message' => [
                'id'            => $msg->id,
                'user_name'     => $sender,
                'user_initials'   => strtoupper(substr($sender, 0, 2)),
                'is_internal'   => (bool) $msg->is_internal,
                'message'       => $msg->message,
                'created_at'    => $msg->created_at->format('H:i'),
                'date_str'      => $msg->created_at->format('d M Y, H:i'),
            ],
        ]);
    }
}
