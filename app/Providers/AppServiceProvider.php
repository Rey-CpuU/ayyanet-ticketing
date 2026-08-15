<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Support\ServiceProvider;

use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strong password policy globally
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        // Listen to failed login Lockout events to notify users/admins
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Lockout::class,
            \App\Listeners\SendLockoutAlert::class
        );

        // Use a connector that forwards the Neon "options" query parameter
        // (endpoint ID for SNI-less clients such as the vercel-php runtime)
        // from the DB_URL query string into the PDO DSN.
        $this->app->bind('db.connector.pgsql', function () {
            return new NeonPostgresConnector;
        });
    }
}
