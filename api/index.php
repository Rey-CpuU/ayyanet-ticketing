<?php

/**
 * Vercel serverless entrypoint (vercel-php runtime).
 *
 * Boots Laravel for Vercel serverless functions with writable /tmp storage.
 */

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Configure writable storage path for Vercel read-only filesystem environment
$storagePath = '/tmp/storage';
@mkdir($storagePath . '/framework/views', 0755, true);
@mkdir($storagePath . '/framework/sessions', 0755, true);
@mkdir($storagePath . '/framework/cache/data', 0755, true);
@mkdir($storagePath . '/logs', 0755, true);

$app->useStoragePath($storagePath);

// Create SQLite database in /tmp if no external database host is provided
if (!env('DB_HOST') && env('DB_CONNECTION') !== 'sqlite') {
    $sqliteDb = '/tmp/database.sqlite';
    if (!file_exists($sqliteDb)) {
        touch($sqliteDb);
    }
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $sqliteDb,
    ]);
}

$app->handleRequest(\Illuminate\Http\Request::capture());


