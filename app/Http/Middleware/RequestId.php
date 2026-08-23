<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generate unique request ID untuk tracing.
 * Header: X-Request-Id
 */
class RequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-Id') ?: (string) \Illuminate\Support\Str::uuid();
        $request->headers->set('X-Request-Id', $requestId);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        // Attach ke log context
        Log::withContext(['request_id' => $requestId]);

        return $response;
    }
}
