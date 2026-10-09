<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Emails are now stored lowercase. Existing mixed-case rows are lowercased, except when the
 * lowercase form would collide with another row: those are skipped (and logged by id) so an
 * admin can resolve the duplicate by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'invitations'] as $table) {
            $this->lowercaseEmails($table);
        }
    }

    public function down(): void
    {
        // Irreversible data normalisation: the original casing is not kept.
    }

    private function lowercaseEmails(string $table): void
    {
        $emails = DB::table($table)->pluck('email', 'id');

        $counts = $emails->countBy(fn (string $email) => Str::lower(trim($email)));

        foreach ($emails as $id => $email) {
            $normalized = Str::lower(trim($email));

            if ($normalized === $email) {
                continue;
            }

            if ($counts[$normalized] > 1) {
                Log::warning("Email lowercase migration skipped {$table}#{$id}: the lowercase address collides with another row.");

                continue;
            }

            DB::table($table)->where('id', $id)->update(['email' => $normalized]);
        }
    }
};
