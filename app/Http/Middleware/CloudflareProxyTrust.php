<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloudflare proxy trust middleware.
 *
 * Production di belakang Cloudflare Tunnel + Cloudflare CDN:
 * - Hanya trust X-Forwarded-* headers dari Cloudflare IP ranges
 * - Pakai CF-Connecting-IP untuk real client IP (override $request->ip())
 * - Pakai CF-Visitor untuk deteksi HTTPS (kalau Flexible SSL)
 * - Pakai X-Forwarded-Proto untuk scheme detection
 *
 * Tanpa middleware ini, attacker bisa spoof X-Forwarded-For / X-Forwarded-Proto
 * dari koneksi langsung ke server AAPanel dan bypass HTTPS detection / rate limit
 * berbasis IP asli.
 *
 * Cloudflare IP list: https://www.cloudflare.com/ips/
 * Di-fetch sekali dan di-hardcode di sini. Update kalau Cloudflare nambah range.
 */
class CloudflareProxyTrust
{
    /**
     * Cloudflare IPv4 ranges (per https://www.cloudflare.com/ips-v4).
     * Update via Cloudflare API atau curl https://www.cloudflare.com/ips-v4 saat deploy.
     */
    public const CLOUDFLARE_IPV4 = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
    ];

    /**
     * Cloudflare IPv6 ranges (per https://www.cloudflare.com/ips-v6).
     * Include untuk server yang bisa di-reach via IPv6.
     */
    public const CLOUDFLARE_IPV6 = [
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Local/dev: skip proxy logic, pakai REMOTE_ADDR langsung
        if (! app()->environment('production')) {
            return $next($request);
        }

        $remoteIp = $request->server('REMOTE_ADDR');

        // Hanya trust proxy headers kalau request beneran dari Cloudflare
        if ($this->isFromCloudflare($remoteIp)) {
            $cfConnectingIp = $request->header('CF-Connecting-IP');
            if ($cfConnectingIp && filter_var($cfConnectingIp, FILTER_VALIDATE_IP)) {
                // Override IP request ke real client IP (untuk rate limiter, audit log, dll)
                $request->server->set('REMOTE_ADDR', $cfConnectingIp);
                $request->headers->set('X-Forwarded-For', $cfConnectingIp);
            }

            // Cloudflare sets X-Forwarded-Proto based on visitor's protocol
            $cfProto = $request->header('X-Forwarded-Proto');
            if ($cfProto === 'https' || $cfProto === 'http') {
                $request->server->set('HTTPS', $cfProto === 'https' ? 'on' : 'off');
            }
        }

        return $next($request);
    }

    protected function isFromCloudflare(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        // IPv4 check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return IpUtils::checkIp($ip, self::CLOUDFLARE_IPV4);
        }

        // IPv6 check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return IpUtils::checkIp($ip, self::CLOUDFLARE_IPV6);
        }

        return false;
    }
}
