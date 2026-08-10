<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's enum->change() generates an inline CHECK inside
        // ALTER COLUMN TYPE, which PostgreSQL rejects. Change the column
        // type first, then manage the CHECK constraint explicitly.
        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action')->default('reply')->change();
        });

        DB::statement('alter table "ticket_activities" drop constraint if exists "ticket_activities_action_check"');
        DB::statement("alter table \"ticket_activities\" add constraint \"ticket_activities_action_check\" check (\"action\" in ('reply', 'status_change', 'internal_note', 'assignment'))");
    }

    public function down(): void
    {
        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action')->default('reply')->change();
        });

        DB::statement('alter table "ticket_activities" drop constraint if exists "ticket_activities_action_check"');
        DB::statement("alter table \"ticket_activities\" add constraint \"ticket_activities_action_check\" check (\"action\" in ('reply', 'status_change', 'internal_note'))");
    }
};
