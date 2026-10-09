<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Flag overdue tickets and notify the assignee + admins. Requires `php artisan schedule:run` via cron.
Schedule::command('tickets:check-sla')->everyFiveMinutes()->withoutOverlapping();

// Public registration is closed, so the first admin has to be promoted from the CLI.
Artisan::command('user:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("User dengan email {$email} tidak ditemukan.");

        return 1;
    }

    $user->update(['role' => 'admin']);
    $this->info("{$user->name} sekarang memiliki role admin.");

    return 0;
})->purpose('Promote an existing user to the admin role');
