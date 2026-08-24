<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * FASE 4.8: Rate limiter registry dengan named limiters per use case.
 *
 * Pakai pattern:
 *   Route::middleware('throttle:auth-login')->post('/login', ...)
 *
 * Limit combinations (per minute):
 * - public: 60/menit (default public)
 * - auth-strict: 5/menit (login, register, forgot password)
 * - auth-write: 10/menit (update profile, password change)
 * - checkout: 10/menit (per user) — anti-bot checkout spam
 * - order-create: 5/menit (per user) — anti bot order creation
 * - contact: 5/jam (per IP) — anti spam contact form
 * - newsletter: 3/jam (per email+IP) — anti spam subscribe
 * - api-write: 30/menit (per user)
 * - api-read: 120/menit (per user)
 * - admin: unlimited (atau 600/menit)
 * - webhook: unlimited (gateway IP trust)
 */
class RouteServiceProvider extends ServiceProvider
{
    public const PUBLIC_LIMIT = 'public';
    public const AUTH_STRICT = 'auth-strict';
    public const AUTH_WRITE = 'auth-write';
    public const CHECKOUT = 'checkout';
    public const ORDER_CREATE = 'order-create';
    public const CONTACT = 'contact';
    public const NEWSLETTER = 'newsletter';
    public const API_WRITE = 'api-write';
    public const API_READ = 'api-read';
    public const ADMIN = 'admin';

    public function boot(): void
    {
        $this->registerRateLimiters();
    }

    public function register(): void
    {
        //
    }

    protected function registerRateLimiters(): void
    {
        // Public default (per IP) — 60 req/menit
        RateLimiter::for(self::PUBLIC_LIMIT, function (Request $request) {
            return Limit::perMinute(60)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak request. Silakan coba lagi dalam 1 menit.',
                    'error' => 'rate_limit_exceeded',
                ], 429);
            });
        });

        // Auth strict (login/register/forgot) — 5/menit per email+IP
        RateLimiter::for(self::AUTH_STRICT, function (Request $request) {
            $key = 'auth-strict:' . strtolower($request->input('email', 'unknown')) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.',
                    'error' => 'auth_rate_limit',
                ], 429);
            });
        });

        // Auth write (profile update, password change) — 10/menit per user
        RateLimiter::for(self::AUTH_WRITE, function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            return Limit::perMinute(10)->by('auth-write:'.$userId);
        });

        // Checkout (per user) — 10/menit
        RateLimiter::for(self::CHECKOUT, function (Request $request) {
            return Limit::perMinute(10)->by('checkout:'.($request->user()?->id ?? $request->ip()));
        });

        // Order creation (per user, anti-bot) — 5/menit
        RateLimiter::for(self::ORDER_CREATE, function (Request $request) {
            return Limit::perMinute(5)->by('order:'.($request->user()?->id ?? $request->ip()));
        });

        // Contact form (per IP) — 5/jam
        RateLimiter::for(self::CONTACT, function (Request $request) {
            return Limit::perHour(5)->by('contact:'.$request->ip());
        });

        // Newsletter (per email+IP) — 3/jam
        RateLimiter::for(self::NEWSLETTER, function (Request $request) {
            $email = strtolower($request->input('email', $request->ip()));
            return Limit::perHour(3)->by('newsletter:'.$email.'|'.$request->ip());
        });

        // API write (per user) — 30/menit
        RateLimiter::for(self::API_WRITE, function (Request $request) {
            return Limit::perMinute(30)->by('api-write:'.($request->user()?->id ?? $request->ip()));
        });

        // API read (per user) — 120/menit
        RateLimiter::for(self::API_READ, function (Request $request) {
            return Limit::perMinute(120)->by('api-read:'.($request->user()?->id ?? $request->ip()));
        });

        // Admin (high limit) — 300/menit
        RateLimiter::for(self::ADMIN, function (Request $request) {
            return Limit::perMinute(300)->by('admin:'.$request->ip());
        });
    }
}