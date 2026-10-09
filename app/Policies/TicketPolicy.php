<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin', 'cs') || $this->ownsAsFieldTechnician($user, $ticket);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'cs');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin', 'cs') || $this->ownsAsFieldTechnician($user, $ticket);
    }

    /**
     * Change the assignee of a ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin', 'cs');
    }

    /**
     * Post a reply/note on a ticket.
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    /**
     * Internal notes are restricted to staff accounts.
     */
    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() && $this->reply($user, $ticket);
    }

    /**
     * Export the ticket list (lapangan exports are scoped to their own tickets).
     */
    public function export(User $user): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    private function ownsAsFieldTechnician(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('lapangan')
            && ((int) $ticket->assigned_to === $user->id || (int) $ticket->created_by === $user->id);
    }
}
