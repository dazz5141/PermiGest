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
        RateLimiter::for('password-reset', function (Request $request) {
            $operator = $request->user()?->getAuthIdentifier() ?? 'guest';

            return Limit::perMinute(5)
                ->by($operator.'|'.$request->ip())
                ->response(fn (Request $request, array $headers) => back()
                    ->with('error', 'Demasiados intentos de restablecimiento. Intenta nuevamente en un minuto.')
                    ->withHeaders($headers));
        });
    }
}
