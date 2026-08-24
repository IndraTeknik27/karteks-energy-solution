<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers middleware.
 *
 * Adds headers untuk protect against common web attacks (XSS, clickjacking, dll).
 * Apply to ALL web routes via global middleware di bootstrap/app.php.
 *
 * Disabled untuk webhook routes (Midtrans, dll) yang mungkin strict pada header.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip untuk webhook endpoints yang strict (Midtrans signature validation)
        if ($request->is('*/webhook/*') || $request->is('*/midtrans/notification')) {
            return $next($request);
        }

        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking (iframe attack)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // XSS Protection (legacy header, masih bagus untuk old browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy (privacy)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy (block unused features)
        $response->headers->set('Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()'
        );

        // HSTS untuk production (360 days, include subdomain, preload-eligible)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content-Security-Policy (basic — adjust per app needs)
        // NOTE: Filament admin mungkin butuh unsafe-eval / unsafe-inline untuk Livewire
        $csp = $this->buildCsp($request);
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Prevent IE from opening downloaded files directly (XSS via download)
        $response->headers->set('X-Download-Options', 'noopen');

        return $response;
    }

    protected function buildCsp(Request $request): string
    {
        // Default CSP: allow same-origin + inline (Blade + Livewire require unsafe-inline)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.sandbox.midtrans.com https://*.midtrans.com https://*.vercel.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.googleapis.com",
            "connect-src 'self' https://api.rajaongkir.com https://api.biteship.com https://*.midtrans.com wss: https://*.pusher.com",
            "frame-src 'self' https://app.sandbox.midtrans.com https://*.midtrans.com",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ];

        return implode('; ', $csp);
    }
}