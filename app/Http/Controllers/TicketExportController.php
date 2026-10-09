<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Support\DisplayTime;
use App\Support\TicketFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketExportController extends Controller
{
    private const CHUNK_SIZE = 500;

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('export', Ticket::class);

        $query = $this->exportQuery($request);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="tickets-'.DisplayTime::format(now(), 'Y-m-d').'.csv"',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ticket Number', 'Customer', 'Title', 'Status', 'Priority', 'Category', 'Created At', 'Created By']);

            // lazyByIdDesc() streams (newest first) the result set in chunks (with eager loads) instead of loading every row at once.
            foreach ($query->lazyByIdDesc(self::CHUNK_SIZE) as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_number ?? 'TKT-'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT),
                    $this->sanitizeCell($ticket->customer->name ?? 'N/A'),
                    $this->sanitizeCell($ticket->title),
                    $ticket->status,
                    $ticket->priority,
                    $ticket->category,
                    DisplayTime::format($ticket->created_at, 'd M Y, H:i', '-'),
                    $this->sanitizeCell($ticket->creator->name ?? 'N/A'),
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('export', Ticket::class);

        return view('tickets.exports.pdf', [
            // Newest first, fetched chunk by chunk while the view iterates.
            'tickets' => $this->exportQuery($request)->lazyByIdDesc(self::CHUNK_SIZE),
            'generatedAt' => DisplayTime::format(now(), 'd M Y, H:i T'),
        ]);
    }

    /**
     * Same scope and filters as the list page the export link came from (see tickets/index).
     */
    private function exportQuery(Request $request): Builder
    {
        $user = $request->user();
        $query = Ticket::with(['customer', 'creator'])->visibleTo($user);

        if ($request->query('mine') === '1') {
            TicketFilters::onlyMine($query, $user);
        }

        return TicketFilters::apply($query, TicketFilters::fromRequest($request));
    }

    /**
     * Neutralise spreadsheet formula injection (cells starting with =, +, -, @).
     */
    private function sanitizeCell(?string $value): ?string
    {
        if ($value !== null && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
