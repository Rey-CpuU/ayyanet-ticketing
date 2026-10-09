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
 */
return new class extends Migration
{
    public function up(): void
    {
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
            ->whereNotIn('action', ['reply', 'status_change', 'internal_note', 'assignment'])
            ->delete();
        DB::table('ticket_activities')->whereNull('user_id')->delete();

        Schema::table('ticket_activities', function (Blueprint $table) {
            $table->enum('action', ['reply', 'status_change', 'internal_note', 'assignment'])->default('reply')->change();
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('old_value')->nullable()->change();
            $table->string('new_value')->nullable()->change();
        });
    }
};
