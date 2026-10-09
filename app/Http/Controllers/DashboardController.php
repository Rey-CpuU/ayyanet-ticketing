<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $filters = [
            'search' => Str::limit(trim((string) $request->query('search', '')), 100, ''),
            'status' => (string) $request->query('status', ''),
            'priority' => in_array($request->query('priority'), Ticket::PRIORITIES, true) ? $request->query('priority') : '',
            'channel' => (string) $request->query('channel', ''),
        ];

        // Every number and list on the dashboard is limited to the tickets the user may see.
        $stats = $this->stats($user);

        // Ticket queue: most recently visited/updated first, paginated.
        $tickets = $this->filtered(Ticket::visibleTo($user), $filters)
            ->with(['customer:id,name,customer_id,phone', 'assignee:id,name'])
            ->withCount('messages')
            ->orderByRaw('COALESCE(last_visited_at, updated_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // "Recently Visited" panel: the five tickets (within the active filters) opened most recently;
        // TicketController::show stamps last_visited_at, never-visited tickets fall back to creation time.
        $recentTickets = $this->filtered(Ticket::visibleTo($user), $filters)
            ->with(['customer:id,name,customer_id', 'assignee:id,name'])
            ->orderByRaw('COALESCE(last_visited_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Only admin/cs may reassign (TicketPolicy::assign); others get a read-only chip.
        $assignableUsers = $user->hasRole('admin', 'cs')
            ? User::whereIn('role', User::ROLES)->orderBy('name')->get(['id', 'name', 'role'])
            : collect();

        return view('dashboard', compact('tickets', 'stats', 'recentTickets', 'assignableUsers', 'filters'));
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
            ", [$user->id, now()->startOfWeek()])
            ->toBase()
            ->first();

        return array_map('intval', (array) $row);
    }

    /**
     * Apply the search/status/priority/channel filters in SQL (status and channel are
     * case-insensitive; a null category counts as Email).
     */
    private function filtered(Builder $query, array $filters): Builder
    {
        if ($filters['search'] !== '') {
            $term = '%'.$filters['search'].'%';

            $query->where(fn (Builder $q) => $q
                ->where('ticket_number', 'like', $term)
                ->orWhere('title', 'like', $term)
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', $term)));
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'All') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        if ($filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }

        if ($filters['channel'] !== '' && $filters['channel'] !== 'All') {
            $channel = strtolower($filters['channel']);

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
