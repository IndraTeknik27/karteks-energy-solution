<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API middleware group - Sanctum untuk SPA & mobile
        $middleware->statefulApi();

        // Trust Cloudflare proxy headers (production only)
        // Override default trustProxies(at: '*') yang trust SEMUA proxy.
        // Middleware ini cuma trust X-Forwarded-* kalau REMOTE_ADDR ada di
        // Cloudflare IP ranges, dan pakai CF-Connecting-IP sebagai real client IP.
        $middleware->trustProxies(at: '*');
        $middleware->prepend(\App\Http\Middleware\CloudflareProxyTrust::class);

        // FASE 4.8: Global security headers (apply to ALL web routes)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // FASE 4.8: Alias for validate.file.upload middleware
        $middleware->alias([
            'file.validate' => \App\Http\Middleware\ValidateFileUpload::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // CORS untuk API consumer (Flutter, dll)
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/v1/payments/midtrans/notification',
            'banners/*/click', // fire-and-forget tracking endpoint
        ]);

        // API rate limit aliases
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ApiResponseHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON response untuk semua API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Custom JSON error responses untuk API
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'errors' => ['auth' => ['Authentication required.']],
                ], 401);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'errors' => [],
                ], $e->getStatusCode());
            }
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Bersihkan Midtrans expired transactions tiap 30 menit
        $schedule->command('midtrans:expire-pending')->everyThirtyMinutes()->withoutOverlapping();

        // Bersihkan audit logs lebih dari 1 tahun
        $schedule->command('audit:prune --days=365')->daily();
    })
    ->create();
