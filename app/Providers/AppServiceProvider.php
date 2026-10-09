<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
    }
}
