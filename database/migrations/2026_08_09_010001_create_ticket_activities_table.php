<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['reply', 'status_change', 'internal_note'])->default('reply');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        Schema::dropIfExists('ticket_progress');
    }

    public function down(): void
    {
        Schema::create('ticket_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('progress');
            $table->timestamps();
        });

        Schema::dropIfExists('ticket_activities');
    }
};
