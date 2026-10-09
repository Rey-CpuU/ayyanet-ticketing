<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_number')->unique();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->foreignId('assigned_to')->nullable()->constrained('users');
   $table->string('title');
   $table->text('description');
   $table->string('category')->nullable();
   $table->string('olt')->nullable();
   $table->string('location')->nullable();

   $table->enum('priority', [
        'Low',
        'Medium',
        'High'
    ])->default('Medium');
    $table->enum('status', [
        'Open',
        'Checking',
        'Waiting Customer',
        'Escalated',
        'Solved',
        'Closed'
    ])->default('Open');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
