<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketMessage;

class TicketMessageController extends Controller
{
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Simpan pesan ke database
        TicketMessage::create([
            'ticket_id' => $ticketId,
            'user_id'   => null, // Nanti diisi Auth::id() kalau fitur Login udah aktif
            'message'   => $request->message,
        ]);

        return back()->with('success', 'Pesan berhasil dikirim!');
    }
}
