<?php

namespace App\Services;

use App\Mail\TicketAssigned as TicketAssignedMail;
use App\Mail\TicketCreated;
use App\Mail\TicketStatusChanged;
use App\Mail\TicketUpdatedMail;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Side effects of ticket events: staff email + in-app notifications, the customer email and the
 * Telegram group alert. Everything is deferred until the surrounding DB transaction commits, so a
 * rolled-back change never notifies anybody, and a failing channel never breaks the request.
 */
class TicketNotifier
{
    public function ticketCreated(Ticket $ticket, ?User $actor): void
    {
        $this->afterCommit('ticket created', function () use ($ticket, $actor) {
            $staff = User::whereIn('role', ['admin', 'cs'])
                ->when($actor, fn ($query) => $query->whereKeyNot($actor->id))
                ->get();

            foreach ($staff as $user) {
                Mail::to($user)->queue(new TicketCreated($ticket));
            }

            Notification::send($staff, new TicketCreatedNotification($ticket));
        });

        $this->customerUpdate($ticket, 'Ticket baru telah dibuat');
        $this->telegram('ticket created', fn () => TelegramNotificationService::sendTicketCreated($ticket));
    }

    public function statusChanged(Ticket $ticket, string $from, string $to, ?User $actor): void
    {
        if (in_array($to, ['Solved', 'Closed'], true)) {
            $this->afterCommit('ticket resolved', function () use ($ticket) {
                $admins = User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    Mail::to($admin)->queue(new TicketStatusChanged($ticket));
                }

                Notification::send($admins, new TicketStatusChangedNotification($ticket));
            });
        }

        $this->telegram('status change', fn () => TelegramNotificationService::sendTicketStatusChanged(
            $ticket, $from, $to, $actor?->name ?? 'Sistem',
        ));
    }

    public function assigned(Ticket $ticket, ?string $fromName, ?User $assignee, ?User $actor): void
    {
        // Self-assignment needs no personal notification.
        if ($assignee && ! $assignee->is($actor)) {
            $this->afterCommit('ticket assigned', function () use ($ticket, $assignee, $actor) {
                $assignee->notify(new TicketAssigned($ticket, $actor));
                Mail::to($assignee)->queue(new TicketAssignedMail($ticket));
            });
        }

        $this->telegram('assignment', fn () => TelegramNotificationService::sendTicketAssigneeChanged(
            $ticket, $fromName ?? 'Unassigned', $assignee?->name ?? 'Unassigned', $actor?->name ?? 'Sistem',
        ));
    }

    /**
     * Email the customer about a change to their ticket, when an address is on file.
     */
    public function customerUpdate(Ticket $ticket, string $action): void
    {
        $email = $ticket->customer?->email;

        if (! $email) {
            return;
        }

        $this->afterCommit('customer email', fn () => Mail::to($email)->send(new TicketUpdatedMail($ticket, $action)));
    }

    private function telegram(string $event, callable $send): void
    {
        if (! TelegramNotificationService::isConfigured()) {
            return;
        }

        $this->afterCommit("telegram {$event}", $send);
    }

    private function afterCommit(string $event, callable $callback): void
    {
        DB::afterCommit(function () use ($event, $callback) {
            try {
                $callback();
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim notifikasi ({$event}): ".$e->getMessage());
            }
        });
    }
}
