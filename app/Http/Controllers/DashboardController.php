<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $channel = $request->query('channel', 'All');
        $status = $request->query('status', 'All');

        $tickets = $this->filtered(Ticket::with(['customer', 'creator', 'assignee'])->visibleTo($request->user()), $channel, $status)
            ->latest()
            ->get();

        $closedCount = Ticket::whereIn('status', ['Solved', 'Closed'])->count();
        $totalCount = Ticket::count();

        $summary = [
            'open' => Ticket::where('status', 'Open')->count(),
            'in_progress' => Ticket::whereIn('status', ['Checking', 'Waiting Customer'])->count(),
            'critical' => Ticket::where('priority', 'High')->count(),
            'resolved' => Ticket::where('status', 'Solved')->count(),
            'total' => $totalCount,
            'closed' => $closedCount,
        ];

        $monthlyStats = $this->monthlyStats();

        $recentTickets = $this->filtered(Ticket::with(['customer'])->visibleTo($request->user()), $channel, $status)
            ->latest()
            ->take(5)
            ->get();

        $priorityCounts = Ticket::query()
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $priorityStats = [
            'Low' => (int) ($priorityCounts['Low'] ?? 0),
            'Medium' => (int) ($priorityCounts['Medium'] ?? 0),
            'High' => (int) ($priorityCounts['High'] ?? 0),
        ];

        $selected = $tickets->first();

        return view('dashboard', compact('tickets', 'summary', 'selected', 'channel', 'status', 'monthlyStats', 'recentTickets', 'priorityStats'));
    }

    /**
     * Apply the channel/status filters in SQL (case-insensitive; a null category counts as Email).
     */
    private function filtered(Builder $query, ?string $channel, ?string $status): Builder
    {
        if ($channel !== 'All' && $channel !== null && $channel !== '') {
            $channel = strtolower($channel);

            $query->where(function (Builder $q) use ($channel) {
                $q->whereRaw('LOWER(category) = ?', [$channel]);

                if ($channel === 'email') {
                    $q->orWhereNull('category');
                }
            });
        }

        if ($status !== 'All' && $status !== null && $status !== '') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($status)]);
        }

        return $query;
    }

    /**
     * Ticket counts for the last six months, computed with a single grouped query.
     */
    private function monthlyStats(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            'sqlsrv' => "FORMAT(created_at, 'yyyy-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $counts = Ticket::query()
            ->where('created_at', '>=', $start)
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M'),
                'count' => (int) ($counts[$date->format('Y-m')] ?? 0),
            ];
        }

        return $monthlyStats;
    }
}
