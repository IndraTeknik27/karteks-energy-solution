<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Audit log untuk admin actions.
 * Catat request method, URL, user, IP, user agent ke audit_logs table.
 */
class AuditLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        // Hanya log authenticated admin actions (bukan static assets)
        if (
            $request->user()
            && $request->user('web') // Filament pakai web guard
            && ! $request->is('admin/livewire/*', 'admin/_debugbar/*')
            && ! str_starts_with($request->path(), 'admin/_')
        ) {
            // Catat ke log channel dulu; tabel audit_logs diisi di FASE 1
            \Illuminate\Support\Facades\Log::channel(config('karteks.audit.log_channel', 'stack'))
                ->info('admin.action', [
                    'user_id' => $request->user()->id,
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status' => $response->getStatusCode(),
                    'duration_ms' => $duration,
                ]);

            // Permanent record ke audit_logs table jika model sudah ada (FASE 1)
            if (class_exists(\App\Models\AuditLog::class)) {
                \App\Models\AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => strtoupper($request->method()).' '.$request->path(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status_code' => $response->getStatusCode(),
                    'duration_ms' => $duration,
                    'metadata' => [
                        'referer' => $request->headers->get('referer'),
                        'content_length' => $request->headers->get('content-length'),
                    ],
                    'created_at' => now(),
                ]);
            }
        }

        return $response;
    }
}
