<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function __invoke(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Ticket::query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $tickets = $query->get();

        $metrics = [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 'Open')->count(),
            'in_progress' => $tickets->whereIn('status', ['Checking', 'Waiting Customer'])->count(),
            'solved' => $tickets->where('status', 'Solved')->count(),
            'closed' => $tickets->where('status', 'Closed')->count(),
            'escalated' => $tickets->where('status', 'Escalated')->count(),
            'high_priority' => $tickets->where('priority', 'High')->count(),
                'avg_resolution' => $tickets->where('status', 'Solved')
                ->avg(fn ($t) => $t->created_at->diffInHours($t->updated_at)) ?? 0,
        ];

        $byChannel = $tickets->groupBy('category')->map->count();
        $byPriority = $tickets->groupBy('priority')->map->count();
        $byStatus = $tickets->groupBy('status')->map->count();

        return view('reports.index', [
            'metrics' => $metrics,
            'byChannel' => $byChannel,
            'byPriority' => $byPriority,
            'byStatus' => $byStatus,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
