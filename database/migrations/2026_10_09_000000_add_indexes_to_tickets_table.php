<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
            $table->index('sla_deadline');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['sla_deadline']);
            $table->dropIndex(['created_by']);
        });
    }
};
