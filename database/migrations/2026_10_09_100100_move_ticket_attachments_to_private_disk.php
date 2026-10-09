<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Ticket attachments used to be stored on the public disk (reachable without login via
 * /storage/...). Move existing files to the private "local" disk; they are now served through
 * an authorized download route. Paths stay the same, only the disk changes. Missing files are
 * skipped, and the download route still falls back to the public disk for anything not moved.
 */
return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        DB::table('tickets')
            ->whereNotNull('attachment_path')
            ->orderBy('id')
            ->chunkById(200, function ($tickets) use ($public, $private) {
                foreach ($tickets as $ticket) {
                    $path = $ticket->attachment_path;

                    try {
                        if (! $public->exists($path) || $private->exists($path)) {
                            continue;
                        }

                        $stream = $public->readStream($path);
                        $written = $private->writeStream($path, $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }

                        if ($written && $private->exists($path)) {
                            $public->delete($path);
                        }
                    } catch (Throwable $e) {
                        Log::warning("Gagal memindahkan lampiran ticket #{$ticket->id}: {$e->getMessage()}");
                    }
                }
            });
    }

    public function down(): void
    {
        // Intentionally left as a no-op: files stay private; the app reads both disks.
    }
};
