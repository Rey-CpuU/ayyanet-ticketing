<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('ticket-writes', fn (Request $request) => Limit::perMinute(20)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('ticket-messages', fn (Request $request) => Limit::perMinute(30)
            ->by($request->user()?->id ?: $request->ip()));

        // The create form asks for suggestions while the agent types (debounced client-side).
        RateLimiter::for('ticket-classify', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('exports', fn (Request $request) => Limit::perMinute(5)
            ->by($request->user()?->id ?: $request->ip()));

        // Enforce a strong password policy globally.
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        // App\Listeners\SendLockoutAlert (repeated failed logins) is registered by Laravel's
        // event discovery; registering it here as well would send every alert twice.

        // Use a connector that forwards the Neon "options" query parameter
        // (endpoint ID for SNI-less clients such as the vercel-php runtime)
        // from the DB_URL query string into the PDO DSN.
        $this->app->bind('db.connector.pgsql', function () {
            return new NeonPostgresConnector;
        });
    }
}
