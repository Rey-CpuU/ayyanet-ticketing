<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class TicketAuditLogController extends Controller
{
    public function index(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $logs = $ticket->auditLogs()->with('user')->latest()->get();

        return view('tickets.audit-log', compact('ticket', 'logs'));
    }
}
