<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('resolution_note')->nullable()->after('status');
            $table->timestamp('resolved_at')->nullable()->after('resolution_note');
            // Set while the ticket waits on the customer; the SLA clock is stopped during that time.
            $table->timestamp('sla_paused_at')->nullable()->after('sla_status');
            $table->index(['sla_status', 'sla_deadline']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['sla_status', 'sla_deadline']);
            $table->dropColumn(['resolution_note', 'resolved_at', 'sla_paused_at']);
        });
    }
};
