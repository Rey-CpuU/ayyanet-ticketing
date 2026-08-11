<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manage the action column CHECK constraint explicitly, because
     * Laravel's enum->change() generates an inline CHECK inside
     * ALTER COLUMN TYPE which PostgreSQL rejects. Driver-specific
     * quoting/syntax is needed for MySQL vs Postgres.
     */
    public function up(): void
    {
        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action')->default('reply')->change();
        });

        $this->syncActionCheck(['reply', 'status_change', 'internal_note', 'assignment']);
    }

    public function down(): void
    {
        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->string('action')->default('reply')->change();
        });

        $this->syncActionCheck(['reply', 'status_change', 'internal_note']);
    }

    /**
     * Replace the action CHECK constraint with the given allowed values.
     */
    protected function syncActionCheck(array $values): void
    {
        $list = implode("', '", $values);

        if (DB::getDriverName() === 'mysql') {
            $hasCheck = DB::select("select 1 from information_schema.check_constraints where constraint_schema = database() and constraint_name = 'ticket_activities_action_check'");

            if ($hasCheck) {
                DB::statement('alter table `ticket_activities` drop check `ticket_activities_action_check`');
            }

            DB::statement("alter table `ticket_activities` add constraint `ticket_activities_action_check` check (`action` in ('{$list}'))");
        } else {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('alter table "ticket_activities" drop constraint if exists "ticket_activities_action_check"');
                DB::statement("alter table \"ticket_activities\" add constraint \"ticket_activities_action_check\" check (\"action\" in ('{$list}'))");
            }
        }
    }
};
