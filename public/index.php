<?php

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Manually load .env before Laravel bootstraps to avoid OneDrive/lock issues
try {
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->safeLoad();
} catch (InvalidPathException $e) {
    // If .env cannot be loaded, ensure APP_KEY is set manually
    $_ENV['APP_KEY'] = 'base64:6G1VCrCrJSq/3h7wUoH+8erNHuM6/YiBrLm+CBeVkgU=';
    putenv('APP_KEY=base64:6G1VCrCrJSq/3h7wUoH+8erNHuM6/YiBrLm+CBeVkgU=');
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
