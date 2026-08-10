<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferSqliteToPostgres extends Command
{
    protected $signature = 'db:transfer-sqlite {--source=database/database.sqlite : Path to the SQLite file to read from}';

    protected $description = 'Copy data from the local SQLite database into the current Postgres connection.';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('This command must run while DB_CONNECTION=pgsql (pointing at the target Postgres database).');
            $this->error('Run it with Postgres env vars set, e.g. DB_CONNECTION=pgsql DB_HOST=... DB_DATABASE=...');

            return 1;
        }

        $source = base_path($this->option('source'));

        if (! file_exists($source)) {
            $this->error("SQLite source not found: {$source}");

            return 1;
        }

        config([
            'database.connections.legacy' => [
                'driver'   => 'sqlite',
                'database' => $source,
            ],
        ]);

        $tables = [
            'users',
            'customers',
            'tickets',
            'ticket_progress',
            'ticket_messages',
            'status_banners',
            'ticket_activities',
        ];

        foreach ($tables as $table) {
            DB::connection()->transaction(function () use ($table) {
                $rows = DB::connection('legacy')
                    ->table($table)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();

                $sourceCount = count($rows);

                if ($sourceCount === 0) {
                    $this->info("{$table}: skipped (0 rows)");

                    return;
                }

                DB::table($table)->insertOrIgnore($rows);

                $sequence = DB::selectOne("SELECT pg_get_serial_sequence('\"{$table}\"', 'id') AS seq");

                if ($sequence && $sequence->seq) {
                    DB::statement("SELECT setval('{$sequence->seq}', COALESCE((SELECT MAX(id) FROM \"{$table}\"), 1), (SELECT MAX(id) FROM \"{$table}\") IS NOT NULL)");
                }

                $this->info("{$table}: imported {$sourceCount} rows");
            });
        }

        $this->newLine();
        $this->info('Transfer complete.');
        $this->warn('Make sure migrations were already run on the target Postgres database before this command.');

        return 0;
    }
}
