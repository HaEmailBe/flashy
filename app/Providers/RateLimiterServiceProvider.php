<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-key', function (Request $request) {
            $apiKey = $request->header('X-Api-Key');

            if (!$apiKey) {
                return Limit::none(); // or handle as needed
            }

            $maxAttempts = (int) (env('app.api_key_max_attempts') ?? 30);

            return Limit::perMinute($maxAttempts)
                ->by($apiKey)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });
    }
}
