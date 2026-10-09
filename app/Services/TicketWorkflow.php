<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TicketSlaBreached;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Single entry point for creating tickets and changing their status, priority or assignee,
 * used by the web controllers. Each operation applies its side
 * effects: ticket numbering, SLA bookkeeping, the activity stream and notifications
 * (staff email/in-app, customer email, Telegram group; see TicketNotifier).
 *
 * SLA rules:
 * - A new ticket gets deadline = now + SLA hours of its priority (config/ticketing.php).
 * - Changing the priority shifts the deadline by the difference between both SLA windows,
 *   so time already spent paused is preserved.
 * - Waiting Customer pauses the clock; leaving it extends the deadline by the paused duration.
 * - Resolving (Solved/Closed) settles sla_status to met (on time) or breached (late).
 * - Reopening a resolved ticket starts a fresh SLA window.
 * - tickets:check-sla flags overdue active tickets as breached and notifies the assignee + admins.
 */
class TicketWorkflow
{
    private const DEFAULT_SLA_HOURS = ['high' => 5, 'medium' => 8, 'low' => 24];

    public function __construct(private TicketNotifier $notifier) {}

    /**
     * The public ticket number, derived from the row id (TKT-0001).
     */
    public static function numberFor(int $id): string
    {
        return 'TKT-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a ticket: always starts Open, gets a TKT number, an SLA window for its priority and a
     * "created" activity entry. Authorization is the caller's job (TicketPolicy::create).
     *
     * @param  array<string, mixed>  $attributes  customer_id, title, description, category, priority, olt, location, attachment_path
     */
    public function create(array $attributes, User $creator): Ticket
    {
        $priority = $attributes['priority'] ?? 'Medium';

        $ticket = DB::transaction(function () use ($attributes, $priority, $creator) {
            $ticket = Ticket::create([
                ...$attributes,
                // Unique placeholder; replaced below with a number derived from the new row id.
                'ticket_number' => 'TMP-'.Str::uuid(),
                'created_by' => $creator->id,
                'priority' => $priority,
                'status' => TicketStatus::Open->value,
                ...self::initialSla($priority),
            ]);

            $ticket->update(['ticket_number' => self::numberFor($ticket->id)]);

            $this->log($ticket, 'created', $creator, null, TicketStatus::Open->value);

            return $ticket;
        });

        $this->notifier->ticketCreated($ticket, $creator);

        return $ticket;
    }

    /**
     * Change (or clear) the assignee. Returns false when nothing changed.
     */
    public function assign(Ticket $ticket, ?User $assignee, ?User $actor = null): bool
    {
        $oldAssigneeId = $ticket->assigned_to === null ? null : (int) $ticket->assigned_to;

        if ($oldAssigneeId === $assignee?->id) {
            return false;
        }

        $oldName = $ticket->assignee?->name ?? 'Unassigned';
        $newName = $assignee?->name ?? 'Unassigned';

        DB::transaction(function () use ($ticket, $assignee, $actor, $oldName, $newName) {
            $ticket->update(['assigned_to' => $assignee?->id]);
            $ticket->setRelation('assignee', $assignee);

            $this->log($ticket, 'assignment', $actor, $oldName, $newName);
        });

        $this->notifier->assigned($ticket, $oldName, $assignee, $actor);

        return true;
    }

    /**
     * SLA window in minutes for a priority. Config values look like "5h", "90m" or "1d".
     */
    public static function slaMinutes(?string $priority): int
    {
        $key = strtolower($priority ?? 'medium');
        $configured = (string) config("ticketing.sla.{$key}", '');

        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([mhd]?)\s*$/i', $configured, $match)) {
            $multiplier = match (strtolower($match[2])) {
                'm' => 1,
                'd' => 1440,
                default => 60,
            };

            return (int) round((float) $match[1] * $multiplier);
        }

        return (self::DEFAULT_SLA_HOURS[$key] ?? self::DEFAULT_SLA_HOURS['medium']) * 60;
    }

    /**
     * SLA attributes for a ticket created now.
     *
     * @return array{sla_deadline: CarbonInterface, sla_status: string}
     */
    public static function initialSla(?string $priority, ?CarbonInterface $from = null): array
    {
        return [
            'sla_deadline' => ($from ?? now())->copy()->addMinutes(self::slaMinutes($priority)),
            'sla_status' => Ticket::SLA_ACTIVE,
        ];
    }

    /**
     * Validate a status change against the workflow rules.
     *
     * @return array<string, string>|null field => message, or null when the change is allowed
     */
    public function transitionError(Ticket $ticket, TicketStatus $to, ?string $resolutionNote): ?array
    {
        $from = $ticket->statusEnum();

        if ($from !== $to && ! $from->canTransitionTo($to)) {
            return ['status' => "Transisi status dari {$from->value} ke {$to->value} tidak diizinkan."];
        }

        if ($from !== $to && $to->requiresResolutionNote($ticket->resolution_note) && blank($resolutionNote)) {
            return ['resolution_note' => "Catatan penyelesaian wajib diisi untuk status {$to->value}."];
        }

        return null;
    }

    /**
     * Move a ticket to a new status. Callers must run transitionError() first; this method only
     * applies the change.
     */
    public function changeStatus(Ticket $ticket, TicketStatus $to, ?string $resolutionNote = null, ?User $actor = null): void
    {
        $from = $ticket->statusEnum();

        if ($from === $to) {
            return;
        }

        DB::transaction(function () use ($ticket, $from, $to, $resolutionNote, $actor) {
            $now = now();

            $this->resumeSla($ticket, $now);

            if ($to->isResolved()) {
                if (! $from->isResolved()) {
                    $ticket->resolved_at = $now;
                    $this->settleSla($ticket, $now);
                }

                if (filled($resolutionNote)) {
                    $ticket->resolution_note = $resolutionNote;
                }
            } elseif ($from->isResolved()) {
                // Reopened: the previous resolution no longer applies and a fresh SLA window starts.
                $ticket->resolved_at = null;
                $ticket->resolution_note = null;
                $ticket->fill(self::initialSla($ticket->priority, $now));
            }

            if ($to->pausesSla() && $ticket->sla_status === Ticket::SLA_ACTIVE && $ticket->sla_deadline) {
                $ticket->sla_paused_at = $now;
            }

            $ticket->status = $to->value;
            $ticket->save();

            $this->log($ticket, 'status_change', $actor, $from->value, $to->value);

            if ($to->isResolved() && filled($resolutionNote)) {
                $this->log($ticket, 'resolution_note', $actor, null, $resolutionNote);
            }
        });

        $this->notifier->statusChanged($ticket, $from->value, $to->value, $actor);
    }

    /**
     * Change the priority and shift the SLA deadline to the new priority's window.
     */
    public function changePriority(Ticket $ticket, string $priority, ?User $actor = null): void
    {
        $oldPriority = $ticket->priority;

        if ($oldPriority === $priority) {
            return;
        }

        DB::transaction(function () use ($ticket, $oldPriority, $priority, $actor) {
            if ($ticket->sla_deadline && ! $ticket->statusEnum()->isResolved()) {
                $delta = self::slaMinutes($priority) - self::slaMinutes($oldPriority);
                $ticket->sla_deadline = $ticket->sla_deadline->copy()->addMinutes($delta);

                // A breach that the new, longer window no longer covers is lifted again.
                if ($ticket->sla_status === Ticket::SLA_BREACHED && $ticket->sla_deadline->isFuture()) {
                    $ticket->sla_status = Ticket::SLA_ACTIVE;
                }
            }

            $ticket->priority = $priority;
            $ticket->save();

            $this->log($ticket, 'priority_change', $actor, $oldPriority, $priority);
        });
    }

    /**
     * Flag an overdue ticket as breached and notify its assignee and all admins.
     * Returns false when another process already flagged it.
     */
    public function markBreached(Ticket $ticket): bool
    {
        $flagged = Ticket::whereKey($ticket->id)
            ->where('sla_status', Ticket::SLA_ACTIVE)
            ->update(['sla_status' => Ticket::SLA_BREACHED]);

        if ($flagged === 0) {
            return false;
        }

        $ticket->sla_status = Ticket::SLA_BREACHED;
        $ticket->syncOriginalAttribute('sla_status');

        $this->log($ticket, 'sla_breached', null, Ticket::SLA_ACTIVE, $ticket->sla_deadline?->format('d M Y, H:i'));

        $recipients = User::where('role', 'admin')
            ->when($ticket->assigned_to, fn ($query) => $query->orWhere('id', $ticket->assigned_to))
            ->get();

        Notification::send($recipients, new TicketSlaBreached($ticket));

        return true;
    }

    private function resumeSla(Ticket $ticket, CarbonInterface $now): void
    {
        if (! $ticket->sla_paused_at) {
            return;
        }

        if ($ticket->sla_deadline) {
            $pausedSeconds = (int) $ticket->sla_paused_at->diffInSeconds($now, true);
            $ticket->sla_deadline = $ticket->sla_deadline->copy()->addSeconds($pausedSeconds);
        }

        $ticket->sla_paused_at = null;
    }

    private function settleSla(Ticket $ticket, CarbonInterface $now): void
    {
        if (! $ticket->sla_deadline || $ticket->sla_status === Ticket::SLA_BREACHED) {
            return;
        }

        $ticket->sla_status = $now->lessThanOrEqualTo($ticket->sla_deadline)
            ? Ticket::SLA_MET
            : Ticket::SLA_BREACHED;
    }

    private function log(Ticket $ticket, string $action, ?User $actor, ?string $old, ?string $new): void
    {
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $actor?->id,
            'action' => $action,
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }
}
