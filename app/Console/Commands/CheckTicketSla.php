<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketWorkflow;
use Illuminate\Console\Command;

class CheckTicketSla extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Tandai tiket yang melewati SLA sebagai breached dan kirim notifikasi';

    public function handle(TicketWorkflow $workflow): int
    {
        $breached = 0;

        Ticket::query()
            ->where('sla_status', Ticket::SLA_ACTIVE)
            ->whereNull('sla_paused_at')
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->whereIn('status', array_map(fn (TicketStatus $status) => $status->value, TicketStatus::active()))
            ->chunkById(100, function ($tickets) use ($workflow, &$breached) {
                foreach ($tickets as $ticket) {
                    if ($workflow->markBreached($ticket)) {
                        $breached++;
                    }
                }
            });

        $this->info("{$breached} tiket ditandai SLA breached.");

        return self::SUCCESS;
    }
}
