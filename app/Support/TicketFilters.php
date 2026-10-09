<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filters of the ticket list, shared by the list pages and the CSV/PDF exports so an export
 * contains exactly the tickets the user was looking at. Unknown or malformed values (including
 * arrays) are ignored instead of failing.
 */
final class TicketFilters
{
    /** Sort options for the ticket list: key => label. */
    public const SORTS = [
        'newest' => 'Terbaru',
        'oldest' => 'Terlama',
        'priority' => 'Prioritas tertinggi',
        'sla' => 'SLA terdekat',
        'updated' => 'Terakhir diperbarui',
    ];

    /**
     * @return array<string, string>
     */
    public static function fromRequest(Request $request): array
    {
        $pick = fn (string $key, array $allowed) => in_array($request->query($key), $allowed, true) ? $request->query($key) : '';
        $assignedTo = Search::input($request, 'assigned_to');

        return [
            'q' => Search::input($request, 'q'),
            'status' => $pick('status', Ticket::STATUSES),
            'priority' => $pick('priority', Ticket::PRIORITIES),
            'category' => Search::input($request, 'category'),
            'assigned_to' => $assignedTo === 'unassigned' || ctype_digit($assignedTo) ? $assignedTo : '',
            'sort' => $pick('sort', array_keys(self::SORTS)) ?: 'newest',
            // Deleted tickets are only visible to admins.
            'trashed' => $request->user()?->isAdmin() ? $pick('trashed', ['with', 'only']) : '',
        ];
    }

    /**
     * "My tickets": field technicians see their visible tickets, other staff the ones they created.
     */
    public static function onlyMine(Builder $query, User $user): Builder
    {
        return $user->hasRole('lapangan') ? $query->visibleTo($user) : $query->where('created_by', $user->id);
    }

    /**
     * Apply the search and filters (not the sort order).
     *
     * @param  array<string, string>  $filters
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        if ($filters['trashed'] === 'with') {
            $query->withTrashed();
        } elseif ($filters['trashed'] === 'only') {
            $query->onlyTrashed();
        }

        if ($filters['q'] !== '') {
            $term = $filters['q'];

            $query->where(fn (Builder $q) => Search::whereContains($q, ['ticket_number', 'title'], $term)
                ->orWhereHas('customer', fn (Builder $c) => Search::whereContains($c, ['name'], $term)));
        }

        foreach (['status', 'priority', 'category'] as $field) {
            if ($filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        if ($filters['assigned_to'] === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($filters['assigned_to'] !== '') {
            $query->where('assigned_to', (int) $filters['assigned_to']);
        }

        return $query;
    }

    public static function sort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest()->oldest('id'),
            'priority' => $query->orderByRaw("CASE priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END")->latest(),
            'sla' => $query->orderByRaw('sla_deadline IS NULL')->orderBy('sla_deadline'),
            'updated' => $query->latest('updated_at'),
            default => $query->latest()->latest('id'),
        };
    }

    /**
     * Query-string parameters of the active filters (for export links).
     *
     * @param  array<string, string>  $filters
     * @return array<string, string>
     */
    public static function queryParameters(array $filters): array
    {
        return array_filter($filters, fn (string $value, string $key) => $value !== '' && $key !== 'sort', ARRAY_FILTER_USE_BOTH);
    }
}
