<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The base users migration already defines `role`; only add it on databases created before that.
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'cs', 'lapangan'])->default('cs')->after('email');
        });
    }

    public function down(): void
    {
        // No-op: `role` belongs to the base users table and must not be dropped here.
    }
};
