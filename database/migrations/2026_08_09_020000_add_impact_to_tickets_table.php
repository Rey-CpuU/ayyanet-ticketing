<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operational impact of the ticket, used to prevent ownerless tickets
     * from sitting unnoticed in the queue.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('impact', [
                'Critical',
                'High',
                'Medium',
                'Low',
            ])->default('Medium')->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('impact');
        });
    }
};
