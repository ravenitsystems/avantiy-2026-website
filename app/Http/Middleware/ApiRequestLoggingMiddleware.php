<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Shared request/response logging for API endpoints (e.g. Epicurus, Duda).
 *
 * All calls that go through this middleware are logged with the same code.
 * Replace the placeholder implementation with the final logging solution
 * (e.g. database table, external log service) when decided.
 */
class ApiRequestLoggingMiddleware
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        print "PRELOG";
        // TODO: Replace with the final API request logging implementation.
        Log::info('API request (placeholder logger)', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
        ]);

        $response = $next($request);

        print "POSTLOG";
        // TODO: Extend to capture full response details as required.
        Log::info('API response (placeholder logger)', [
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
