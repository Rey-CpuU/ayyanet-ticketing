<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The activity stream of a ticket (replies, notes, status/priority/assignee changes, SLA events).
 * Field-level edit diffs are still kept in ticket_audit_logs for the audit-log page.
 */
class TicketActivity extends Model
{
    public const ACTIONS = [
        'created' => 'Tiket dibuat',
        'reply' => 'Balasan',
        'internal_note' => 'Catatan internal',
        'status_change' => 'Status diubah',
        'resolution_note' => 'Catatan penyelesaian',
        'priority_change' => 'Prioritas diubah',
        'assignment' => 'Penugasan',
        'sla_breached' => 'SLA terlampaui',
    ];

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function label(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Whether the activity describes a transition (old -> new) rather than carrying content.
     */
    public function isTransition(): bool
    {
        return in_array($this->action, ['status_change', 'priority_change', 'assignment'], true);
    }
}
