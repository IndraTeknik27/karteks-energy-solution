<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set long-term Cache-Control headers for media library assets
 * (images, conversions, downloads from /storage/* path).
 *
 * Why: Cloudflare + browser cache assets 1 year. File mtime acts as ETag
 * via Last-Modified header — saves bandwidth and improves TTFB on repeat visits.
 *
 * Applies to GET /storage/* requests that resolve to existing files on
 * the public disk. Web routes serving HTML/Blade/CSS/JS should NOT pass
 * through this middleware (they have their own caching strategy).
 *
 * NOTE: AAPanel Nginx already has `expires 1y` for /storage/* but this
 * middleware adds an extra safety net if assets are served via Laravel.
 */
class StaticAssetCache
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $response = $next($request);

        // Only apply to /storage/* paths (media library served via public/storage symlink)
        if (! $request->is('storage/*')) {
            return $response;
        }

        // Only cache successful responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Long cache — 1 year. Media files are immutable (when regenerated they get a new path).
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

        // Last-Modified from file mtime if we can find the underlying file
        if ($response instanceof BinaryFileResponse) {
            $file = $response->getFile();
            if ($file && $file->isFile()) {
                $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $file->getMTime()).' GMT');
            }
        }

        return $response;
    }
}
