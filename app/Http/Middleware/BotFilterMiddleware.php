<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class BotFilterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->header('User-Agent');

        if ($this->isKnownBot($userAgent)) {
            \Log::warning('Blocked bot with User-Agent: ' . $userAgent);

            return abort(403, 'Access denied for this user agent.');
        }

        return $next($request);
    }

    private function isKnownBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $badBotKeywords = [
            'seznam',
            'flexbot',
            'mail.ru',
            'dotbot',
            'mj12bot',
            'spbot',
            'ahrefsbot',
        ];

        foreach ($badBotKeywords as $keyword) {
            if (Str::contains(strtolower($userAgent), strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
