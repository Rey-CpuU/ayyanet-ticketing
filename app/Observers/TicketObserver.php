<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    /**
     * Stored previous attributes before update.
     */
    protected static array $previousState = [];

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        try {
            Log::info("TicketObserver created triggered for ticket #{$ticket->ticket_number}");
            TelegramNotificationService::sendTicketCreated($ticket);
        } catch (\Throwable $e) {
            Log::error('TicketObserver created notification error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Ticket "updating" event.
     */
    public function updating(Ticket $ticket): void
    {
        if ($ticket->isDirty('status')) {
            self::$previousState[$ticket->id]['status'] = $ticket->getOriginal('status') ?? 'Open';
        }
        if ($ticket->isDirty('assigned_to')) {
            self::$previousState[$ticket->id]['assigned_to'] = $ticket->getOriginal('assigned_to');
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        try {
            $updaterName = Auth::user()?->name ?? 'Staff / System';

            // 1. Status Change Notification
            if ($ticket->wasChanged('status')) {
                $oldStatus = self::$previousState[$ticket->id]['status'] ?? ($ticket->getOriginal('status') ?? 'Open');
                $newStatus = $ticket->status;
                Log::info("TicketObserver status change for ticket #{$ticket->ticket_number}: {$oldStatus} -> {$newStatus}");
                TelegramNotificationService::sendTicketStatusChanged($ticket, $oldStatus, $newStatus, $updaterName);
            }

            // 2. Assignment / Reassignment Notification
            if ($ticket->wasChanged('assigned_to')) {
                $oldAssigneeId = self::$previousState[$ticket->id]['assigned_to'] ?? null;
                $newAssigneeId = $ticket->assigned_to;
                $oldAssignee = $oldAssigneeId ? (User::find($oldAssigneeId)?->name ?? 'User #' . $oldAssigneeId) : 'Unassigned';
                $newAssignee = $newAssigneeId ? (User::find($newAssigneeId)?->name ?? 'User #' . $newAssigneeId) : 'Unassigned';

                Log::info("TicketObserver assignee change for ticket #{$ticket->ticket_number}: {$oldAssignee} -> {$newAssignee}");
                TelegramNotificationService::sendTicketAssigneeChanged($ticket, $oldAssignee, $newAssignee, $updaterName);
            }
        } catch (\Throwable $e) {
            Log::error('TicketObserver updated notification error: ' . $e->getMessage());
        } finally {
            unset(self::$previousState[$ticket->id]);
        }
    }
}
