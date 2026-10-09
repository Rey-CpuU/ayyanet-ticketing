<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark one notification as read and open the ticket it refers to.
     */
    public function read(Request $request, string $notification)
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $ticket = Ticket::find($notification->data['ticket_id'] ?? null);

        if ($ticket && $request->user()->can('view', $ticket)) {
            return redirect()->route('tickets.show', $ticket);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
