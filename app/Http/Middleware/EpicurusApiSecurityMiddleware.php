<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EpicurusApiSecurityMiddleware
{
    /**
     * Placeholder security middleware for Epicurus API endpoints.
     *
     * This is intentionally minimal for now and should be extended
     * once the concrete security model is decided (e.g. API keys,
     * OAuth tokens, signed requests, etc.).
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        print "SEC1";
        // TODO: Implement Epicurus API security checks (authentication/authorization).

        return $next($request);
    }
}

