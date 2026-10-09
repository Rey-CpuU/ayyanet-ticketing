<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Support\DisplayTime;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Resolved statuses; everything else counts as an open ticket. */
    private const RESOLVED = ['Solved', 'Closed'];

    public function __invoke(Request $request)
    {
        $user = $request->user();
        $filters = [
            'search' => Search::input($request, 'search'),
            'status' => Search::input($request, 'status', 50),
            'priority' => in_array($request->query('priority'), Ticket::PRIORITIES, true) ? $request->query('priority') : '',
            'channel' => Search::input($request, 'channel', 50),
        ];

        // Every number and list on the dashboard is limited to the tickets the user may see.
        $stats = $this->stats($user);

        // Ticket queue: most recently visited/updated first, paginated.
        $tickets = $this->filtered(Ticket::visibleTo($user), $filters)
            ->with(['customer:id,name,customer_id,phone,deleted_at', 'assignee:id,name'])
            ->withCount('messages')
            ->orderByRaw('COALESCE(last_visited_at, updated_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Queue tab counters: open tickets over the whole filtered set (not just the current page),
        // so without filters they match the "belum ditugaskan" chip in the header.
        $queueCounts = $this->queueCounts($user, $filters, $tickets->total());

        // "Recently Visited" panel: the five tickets (within the active filters) opened most recently;
        // TicketController::show stamps last_visited_at, never-visited tickets fall back to creation time.
        $recentTickets = $this->filtered(Ticket::visibleTo($user), $filters)
            ->with(['customer:id,name,customer_id,deleted_at', 'assignee:id,name'])
            ->orderByRaw('COALESCE(last_visited_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Only admin/cs may reassign (TicketPolicy::assign); others get a read-only chip.
        $assignableUsers = $user->hasRole('admin', 'cs')
            ? User::whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name', 'role'])
            : collect();

        return view('dashboard', compact('tickets', 'stats', 'queueCounts', 'recentTickets', 'assignableUsers', 'filters'));
    }

    /**
     * @return array<string, int>
     */
    private function stats(User $user): array
    {
        $row = Ticket::visibleTo($user)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'Open' THEN 1 END) as open,
                COUNT(CASE WHEN status = 'Checking' THEN 1 END) as checking,
                COUNT(CASE WHEN status = 'Waiting Customer' THEN 1 END) as waiting,
                COUNT(CASE WHEN status = 'Escalated' THEN 1 END) as escalated,
                COUNT(CASE WHEN status = 'Solved' THEN 1 END) as solved,
                COUNT(CASE WHEN status = 'Closed' THEN 1 END) as closed,
                COUNT(CASE WHEN assigned_to IS NULL AND status NOT IN ('Solved', 'Closed') THEN 1 END) as unassigned,
                COUNT(CASE WHEN assigned_to = ? THEN 1 END) as mine,
                COUNT(CASE WHEN priority = 'High' AND status NOT IN ('Solved', 'Closed') THEN 1 END) as high,
                COUNT(CASE WHEN status IN ('Solved', 'Closed') AND resolved_at >= ? THEN 1 END) as solved_week
            ", [$user->id, DisplayTime::startOfWeek()])
            ->toBase()
            ->first();

        return array_map('intval', (array) $row);
    }

    /**
     * @param  array<string, string>  $filters
     * @return array{unassigned: int, mine: int, all: int}
     */
    private function queueCounts(User $user, array $filters, int $total): array
    {
        $row = $this->filtered(Ticket::visibleTo($user), $filters)
            ->whereNotIn('status', self::RESOLVED)
            ->selectRaw('
                COUNT(CASE WHEN assigned_to IS NULL THEN 1 END) as unassigned,
                COUNT(CASE WHEN assigned_to = ? THEN 1 END) as mine
            ', [$user->id])
            ->toBase()
            ->first();

        return [
            'unassigned' => (int) ($row->unassigned ?? 0),
            'mine' => (int) ($row->mine ?? 0),
            'all' => $total,
        ];
    }

    /**
     * Apply the search/status/priority/channel filters in SQL (search, status and channel are
     * case-insensitive; a null category counts as Email).
     *
     * @param  array<string, string>  $filters
     */
    private function filtered(Builder $query, array $filters): Builder
    {
        if ($filters['search'] !== '') {
            $term = $filters['search'];

            $query->where(fn (Builder $q) => Search::whereContains($q, ['ticket_number', 'title'], $term)
                ->orWhereHas('customer', fn (Builder $c) => Search::whereContains($c, ['name'], $term)));
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'All') {
            $query->whereRaw('LOWER(status) = ?', [mb_strtolower($filters['status'])]);
        }

        if ($filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }

        if ($filters['channel'] !== '' && $filters['channel'] !== 'All') {
            $channel = mb_strtolower($filters['channel']);

            $query->where(function (Builder $q) use ($channel) {
                $q->whereRaw('LOWER(category) = ?', [$channel]);

                if ($channel === 'email') {
                    $q->orWhereNull('category');
                }
            });
        }

        return $query;
    }
}
