<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Double-submit cookie CSRF protection untuk web forms.
 *
 * Untuk state-changing web requests (POST/PUT/PATCH/DELETE) yang BUKAN
 * dari Livewire (Filament sudah handle sendiri), validate bahwa:
 * 1. Request memiliki X-CSRF-Token header atau _token field
 * 2. Token match dengan XSRF-TOKEN cookie
 *
 * Web forms di Blade (non-Filament) sudah otomatis protected oleh Laravel's
 * default VerifyCsrfToken middleware via session. Middleware ini sebagai
 * extra layer untuk endpoint web custom (mis. newsletter subscribe dari public).
 */
class VerifyCsrfHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip untuk safe methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // Skip untuk API routes (sudah ada Sanctum auth + csrf exclude di bootstrap)
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip untuk Filament (sudah handle CSRF via Livewire)
        if ($request->is('admin/*') || $request->is('livewire/*')) {
            return $next($request);
        }

        $token = $request->header('X-CSRF-Token') ?? $request->input('_token');
        $cookieToken = $request->cookie('XSRF-TOKEN');

        if (! $token || ! $cookieToken || ! hash_equals($cookieToken, $token)) {
            abort(419, 'CSRF token mismatch.');
        }

        return $next($request);
    }
}