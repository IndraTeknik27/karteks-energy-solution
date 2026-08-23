<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hybrid auth untuk cart (dan endpoint lain yang support guest + auth).
 * - Jika Bearer token valid → auth:sanctum berjalan, $request->user() terisi
 * - Jika tidak ada token atau token invalid → tetap guest, tidak error
 *
 * Middleware ini TIDAK mem-block anonymous request, hanya mencoba autentikasi
 * jika Authorization header mengandung Bearer token.
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            $request->setUserResolver(function () use ($request) {
                return \Laravel\Sanctum\Guard::class
                    ? \Auth::guard('sanctum')->user()
                    : null;
            });
            \Auth::guard('sanctum')->authenticate();
        }

        return $next($request);
    }
}