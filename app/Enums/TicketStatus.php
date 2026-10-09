<?php

namespace App\Enums;

/**
 * Ticket lifecycle and the single source of truth for allowed status transitions.
 *
 * Workflow rules:
 * - Every active status (Open, Checking, Waiting Customer, Escalated) can move to any other
 *   active status, so a ticket is never stuck (e.g. Escalated can go back to Checking once the
 *   NOC hands it back, or to Waiting Customer when a site visit must be scheduled).
 * - Every active status can be resolved directly (Solved). Open -> Solved covers first-contact
 *   resolution (e.g. a WiFi password reset over the phone).
 * - Every active status can be Closed directly, for duplicates, invalid reports or customers
 *   who never respond. Closing without a prior resolution requires a resolution note.
 * - Solved -> Closed once the customer confirms; Solved -> Open reopens the ticket.
 * - Closed is terminal except for reopening (-> Open).
 * - Moving to Solved always requires a resolution note.
 * - The SLA clock is paused while the ticket is in Waiting Customer.
 */
enum TicketStatus: string
{
    case Open = 'Open';
    case Checking = 'Checking';
    case WaitingCustomer = 'Waiting Customer';
    case Escalated = 'Escalated';
    case Solved = 'Solved';
    case Closed = 'Closed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<self>
     */
    public static function active(): array
    {
        return [self::Open, self::Checking, self::WaitingCustomer, self::Escalated];
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open, self::Checking, self::WaitingCustomer, self::Escalated => array_values(array_filter(
                self::cases(),
                fn (self $status) => $status !== $this,
            )),
            self::Solved => [self::Closed, self::Open],
            self::Closed => [self::Open],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isResolved(): bool
    {
        return $this === self::Solved || $this === self::Closed;
    }

    /**
     * Whether the SLA clock is stopped while a ticket has this status.
     */
    public function pausesSla(): bool
    {
        return $this === self::WaitingCustomer;
    }

    /**
     * Whether moving to this status needs a resolution note, given the note already on the ticket.
     */
    public function requiresResolutionNote(?string $existingNote = null): bool
    {
        return match ($this) {
            self::Solved => true,
            self::Closed => blank($existingNote),
            default => false,
        };
    }
}
