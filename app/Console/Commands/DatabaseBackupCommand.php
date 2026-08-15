<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=7 : Number of days to keep old backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a secure JSON/SQL snapshot backup of database tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup process...');

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $tables = [
            'users',
            'customers',
            'tickets',
            'ticket_messages',
            'ticket_activities',
            'invitations',
            'status_banners',
        ];

        $data = [
            'created_at' => now()->toIso8601String(),
            'app'        => config('app.name'),
            'tables'     => [],
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $rows = DB::table($table)->get();
                $data['tables'][$table] = $rows;
                $this->line("  ✓ Exported table: {$table} (" . $rows->count() . " rows)");
            }
        }

        $filename = 'backup-' . date('Y-m-d_His') . '.json';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        File::put($filepath, json_encode($data, JSON_PRETTY_PRINT));

        $this->info("Backup successfully saved to: {$filepath}");

        // Cleanup old backups
        $keepDays = (int) $this->option('keep');
        $files = File::files($backupDir);
        $deletedCount = 0;

        foreach ($files as $file) {
            if (time() - $file->getMTime() > ($keepDays * 86400)) {
                File::delete($file->getRealPath());
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->comment("Cleaned up {$deletedCount} old backup file(s) older than {$keepDays} days.");
        }

        return Command::SUCCESS;
    }
}
