<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Cache\RateLimiting\Limit;
use App\Http\Middleware\BotFilterMiddleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(BotFilterMiddleware::class);

        $middleware->alias([
            'api.key' => App\Http\Middleware\ValidateApiKey::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
