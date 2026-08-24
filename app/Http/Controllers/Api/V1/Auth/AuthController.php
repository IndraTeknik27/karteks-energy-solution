<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\V1\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $deviceName = $data['device_name'] ?? 'mobile-app';

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'is_active' => true,
            ]);

            $user->assignRole('customer');

            return $user;
        });

        $user->load('roles');

        $token = $user->createToken(
            name: $deviceName,
            abilities: ['*'],
            expiresAt: now()->addDays(7),
        );

        $user->update(['last_login_at' => now()]);

        $user->sendEmailVerificationNotification();

        $sessionId = $request->header('X-Session-Id');
        $mergedCart = null;
        if ($sessionId && Str::isUuid($sessionId)) {
            try {
                $this->cartService->mergeGuestCartOnLogin($user, $sessionId);
                $mergedCart = $user->carts()->with('items.itemable')->latest('updated_at')->first();
            } catch (\Throwable $e) {
                $mergedCart = null;
            }
        }

        $response = $this->buildAuthResponse(
            $user,
            $token->plainTextToken,
            'Registrasi berhasil. Silakan cek email untuk verifikasi.',
            201,
        );

        if ($mergedCart) {
            $payload = $response->getData(true);
            $payload['data']['merged_cart'] = (new \App\Http\Resources\V1\CartResource($mergedCart))->resolve($request);
            $response->setData($payload);
        }

        return $response;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $deviceName = $data['device_name'] ?? 'mobile-app';
        $remember = (bool) ($data['remember'] ?? false);
        $email = strtolower(trim($data['email']));
        $ip = $request->ip();

        // FASE 4.8: Brute force protection — block jika email atau IP throttled
        if (LoginAttempt::isEmailThrottled($email, maxAttempts: 5, minutes: 15)) {
            throw ValidationException::withMessages([
                'email' => ['Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'],
            ]);
        }
        if (LoginAttempt::isIpThrottled($ip, maxAttempts: 20, minutes: 15)) {
            throw ValidationException::withMessages([
                'email' => ['Terlalu banyak percobaan login dari IP ini. Coba lagi nanti.'],
            ]);
        }

        $throttleKey = Str::transliterate($email.'|'.$ip);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."],
            ]);
        }

        $user = User::where('email', $email)->first();
        $passwordValid = $user && Hash::check($data['password'], $user->password);

        // FASE 4.8: Record login attempt (success or failure)
        LoginAttempt::record($email, $ip, $passwordValid, $request->userAgent());

        if (! $passwordValid) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda dinonaktifkan. Hubungi customer service.'],
            ]);
        }

        RateLimiter::clear($throttleKey);
        LoginAttempt::where('email', $email)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes(15))
            ->delete();

        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        $abilities = $user->isAdmin() ? ['*'] : ['customer'];
        $expiresAt = $remember
            ? now()->addDays(30)
            : now()->addDays(7);

        $token = $user->createToken($deviceName, $abilities, $expiresAt);

        $user->update(['last_login_at' => now()]);
        $user->load('roles');

        $sessionId = $request->header('X-Session-Id');
        $mergedCart = null;
        if ($sessionId && Str::isUuid($sessionId)) {
            try {
                $this->cartService->mergeGuestCartOnLogin($user, $sessionId);
                $mergedCart = $user->carts()->with('items.itemable')->latest('updated_at')->first();
            } catch (\Throwable $e) {
                $mergedCart = null;
            }
        }

        $response = $this->buildAuthResponse(
            $user,
            $token->plainTextToken,
            'Login berhasil.',
        );

        if ($mergedCart) {
            $payload = $response->getData(true);
            $payload['data']['merged_cart'] = (new \App\Http\Resources\V1\CartResource($mergedCart))->resolve($request);
            $response->setData($payload);
        }

        return $response;
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil. Token telah dicabut.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $count = $request->user()->tokens()->count();
        $request->user()->tokens()->delete();

        return $this->success(
            ['tokens_revoked' => $count],
            'Semua sesi telah diakhiri.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return $this->success(
            new UserResource($user),
            'Data pengguna saat ini.',
        );
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        $deviceName = $currentToken->name ?? 'mobile-app';
        $abilities = $currentToken->abilities ?? ['customer'];

        $newToken = $user->createToken($deviceName, $abilities, now()->addDays(7));

        $currentToken->delete();

        return $this->success([
            'access_token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $newToken->accessToken->expires_at?->toIso8601String(),
        ], 'Token berhasil diperbarui.');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->fill($data)->save();
        $user->load('roles');

        return $this->success(
            new UserResource($user),
            'Profil berhasil diperbarui.',
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password berhasil diperbarui. Sesi lain telah diakhiri.');
    }

    protected function buildAuthResponse(User $user, string $token, string $message, int $status = 200): JsonResponse
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $user->tokens()->where('name', '!=', null)->latest('created_at')->first()?->expires_at?->toIso8601String(),
            'abilities' => $user->isAdmin() ? ['*'] : ['customer'],
            'user' => new UserResource($user),
        ], $message, $status);
    }
}