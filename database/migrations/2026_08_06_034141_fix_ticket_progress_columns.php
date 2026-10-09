<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_progress', function (Blueprint $table) {
            $table->string('status')->after('ticket_id');
            $table->text('note')->nullable()->after('status');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('note');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id', 'progress');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->after('ticket_id');
            $table->text('progress')->after('user_id');
            $table->dropForeign(['updated_by']);
            $table->dropColumn('status', 'note', 'updated_by');
        });
    }
};
