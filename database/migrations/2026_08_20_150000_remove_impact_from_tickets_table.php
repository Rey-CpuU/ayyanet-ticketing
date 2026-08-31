<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'impact')) {
                try {
                    $table->dropIndex(['impact']);
                } catch (\Throwable $e) {
                    // Ignore if index doesn't exist
                }
                $table->dropColumn('impact');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('impact', ['Low', 'Medium', 'High', 'Critical'])->default('Medium')->after('priority');
            $table->index('impact');
        });
    }
};
