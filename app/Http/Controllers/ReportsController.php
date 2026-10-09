<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Support\DisplayTime;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __invoke(Request $request)
    {
        // Dates are calendar days in the display timezone ("Y-m-d"); anything else (arrays, garbage,
        // impossible dates) is ignored instead of reaching the database.
        $from = $this->dateInput($request, 'from');
        $to = $this->dateInput($request, 'to');
        $dateError = ($from === null && filled($request->query('from'))) || ($to === null && filled($request->query('to')))
            ? 'Format tanggal tidak valid, filter tanggal diabaikan.'
            : null;

        $query = Ticket::query();

        if ($from !== null) {
            $query->where('created_at', '>=', DisplayTime::dayBounds($from)[0]);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', DisplayTime::dayBounds($to)[1]);
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
            'dateError' => $dateError,
        ]);
    }

    /**
     * A valid "Y-m-d" date from the query string, or null.
     */
    private function dateInput(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            return null;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : null;
    }
}
