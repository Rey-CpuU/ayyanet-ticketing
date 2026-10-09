<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    /** Mirrors App\Enums\TicketStatus, which owns the transition rules. */
    public const STATUSES = ['Open', 'Checking', 'Waiting Customer', 'Escalated', 'Solved', 'Closed'];

    public const PRIORITIES = ['Low', 'Medium', 'High'];

    public const SLA_ACTIVE = 'active';

    public const SLA_MET = 'met';

    public const SLA_BREACHED = 'breached';

    public const CATEGORIES = ['Email', 'Live Chat', 'WhatsApp', 'Web Form', 'Portal'];

    public const ATTACHMENT_MIMES = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

    /** Maximum attachment size in kilobytes (5 MB). */
    public const ATTACHMENT_MAX_KB = 5120;

    /** Attachments live on the private disk and are served through an authorized route. */
    public const ATTACHMENT_DISK = 'local';

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'category',
        'olt',
        'location',
        'priority',
        'status',
        'resolution_note',
        'resolved_at',
        'attachment_path',
        'sla_deadline',
        'sla_status',
        'sla_paused_at',
        'last_visited_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(TicketAuditLog::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class);
    }

    /**
     * Stamp the ticket as just visited (feeds the dashboard's "recently visited" panel) without
     * touching updated_at or firing model events.
     */
    public function touchVisited(): void
    {
        if (! $this->exists) {
            return;
        }

        $now = now();

        static::withTrashed()->whereKey($this->getKey())->toBase()->update(['last_visited_at' => $now]);

        $this->last_visited_at = $now;
        $this->syncOriginalAttribute('last_visited_at');
    }

    public function statusEnum(): TicketStatus
    {
        return TicketStatus::from($this->status);
    }

    public function isSlaPaused(): bool
    {
        return $this->sla_paused_at !== null;
    }

    /**
     * Display state of the SLA clock, shared by the ticket page, the dashboard and the quick view.
     *
     * "remaining" is the frozen number of seconds left while the clock is paused, null otherwise.
     *
     * @return array<string, mixed>|null
     */
    public function slaSummary(): ?array
    {
        if (! $this->sla_deadline) {
            return null;
        }

        $paused = $this->isSlaPaused();

        [$state, $label] = match (true) {
            $this->sla_status === self::SLA_MET => ['met', 'Terpenuhi'],
            $this->sla_status === self::SLA_BREACHED, ! $paused && $this->sla_deadline->isPast() => ['breached', 'Terlampaui'],
            $paused => ['paused', 'Dijeda'],
            default => ['active', 'Berjalan'],
        };

        return [
            'state' => $state,
            'label' => $label,
            'running' => ! $this->statusEnum()->isResolved() && ! $paused,
            'deadline' => $this->sla_deadline,
            // While paused the remaining time is frozen at the moment the clock stopped.
            'remaining' => $paused ? (int) $this->sla_paused_at->diffInSeconds($this->sla_deadline, false) : null,
        ];
    }

    /**
     * Restrict a query to the tickets the given user may see: field technicians (lapangan)
     * only see tickets assigned to or created by them; other staff see everything.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user->hasRole('lapangan')) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('assigned_to', $user->id)
            ->orWhere('created_by', $user->id));
    }

    protected function casts(): array
    {
        return [
            'sla_deadline' => 'datetime',
            'sla_paused_at' => 'datetime',
            'resolved_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }
}
