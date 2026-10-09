<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ticket_activities becomes the single activity stream for a ticket:
 * - action is a plain string (see App\Models\TicketActivity::ACTIONS) so new event types
 *   do not need an enum migration each time;
 * - user_id is nullable for system events (e.g. the scheduled SLA breach check);
 * - old/new values are text, because replies and notes can be up to 2000 characters.
 *
 * On MySQL/PostgreSQL, 2026_08_09_020001 guards `action` with a named CHECK constraint
 * (ticket_activities_action_check); it is dropped here so the new action types are accepted.
 * On SQLite the column change rebuilds the table, which removes the inline CHECK.
 */
return new class extends Migration
{
    private const LEGACY_ACTIONS = ['reply', 'status_change', 'internal_note', 'assignment'];

    public function up(): void
    {
        $this->dropActionCheck();

        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action', 50)->default('reply')->change();
            $table->foreignId('user_id')->nullable()->change();
            $table->text('old_value')->nullable()->change();
            $table->text('new_value')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('ticket_activities')
            ->whereNotIn('action', self::LEGACY_ACTIONS)
            ->delete();
        DB::table('ticket_activities')->whereNull('user_id')->delete();

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('ticket_activities', function (Blueprint $table) {
                $table->enum('action', self::LEGACY_ACTIONS)->default('reply')->change();
                $table->foreignId('user_id')->nullable(false)->change();
                $table->string('old_value')->nullable()->change();
                $table->string('new_value')->nullable()->change();
            });

            return;
        }

        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action')->default('reply')->change();
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('old_value')->nullable()->change();
            $table->string('new_value')->nullable()->change();
        });

        $list = implode("', '", self::LEGACY_ACTIONS);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("alter table `ticket_activities` add constraint `ticket_activities_action_check` check (`action` in ('{$list}'))");
        } else {
            DB::statement("alter table \"ticket_activities\" add constraint \"ticket_activities_action_check\" check (\"action\" in ('{$list}'))");
        }
    }

    private function dropActionCheck(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $hasCheck = DB::select("select 1 from information_schema.check_constraints where constraint_schema = database() and constraint_name = 'ticket_activities_action_check'");

            if ($hasCheck) {
                DB::statement('alter table `ticket_activities` drop check `ticket_activities_action_check`');
            }
        } elseif ($driver === 'pgsql') {
            DB::statement('alter table "ticket_activities" drop constraint if exists "ticket_activities_action_check"');
        }
    }
};
