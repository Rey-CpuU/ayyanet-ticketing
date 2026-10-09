<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customers and tickets are soft-deleted; a hard delete of a customer must never cascade
 * into (and silently purge) its tickets. Switch the FK from CASCADE to RESTRICT.
 *
 * On SQLite, changing a foreign key rebuilds the table (Laravel copies the rows into a new
 * table with FK enforcement disabled). The rebuild would drop the CHECK constraints that back
 * the enum columns, so they are re-declared in the same operation. (The impact column was
 * removed by 2026_08_20_150000 and is no longer re-declared.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->setCustomerForeignKey('restrict');
    }

    public function down(): void
    {
        $this->setCustomerForeignKey('cascade');
    }

    private function setCustomerForeignKey(string $onDelete): void
    {
        Schema::table('tickets', function (Blueprint $table) use ($onDelete) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete($onDelete);

            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium')->change();
                $table->enum('status', ['Open', 'Checking', 'Waiting Customer', 'Escalated', 'Solved', 'Closed'])->default('Open')->change();
            }
        });
    }
};
