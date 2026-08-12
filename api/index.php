<?php

/**
 * Vercel serverless entrypoint (vercel-php runtime).
 *
 * Boots Laravel for Vercel serverless functions with writable /tmp storage.
 */

define('LARAVEL_START', microtime(true));

// Configure writable storage path for Vercel read-only filesystem environment
$storagePath = '/tmp/storage';
@mkdir($storagePath . '/framework/views', 0755, true);
@mkdir($storagePath . '/framework/sessions', 0755, true);
@mkdir($storagePath . '/framework/cache/data', 0755, true);
@mkdir($storagePath . '/logs', 0755, true);

putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";

// Fallback to SQLite in /tmp if no DB host is configured
if (!getenv('DB_HOST') && getenv('DB_CONNECTION') !== 'sqlite') {
    $sqliteDb = '/tmp/database.sqlite';
    if (!file_exists($sqliteDb)) {
        @touch($sqliteDb);
    }
    putenv('DB_CONNECTION=sqlite');
    putenv("DB_DATABASE={$sqliteDb}");
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_ENV['DB_DATABASE'] = $sqliteDb;
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->useStoragePath($storagePath);

$app->handleRequest(\Illuminate\Http\Request::capture());



