<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $status = Password::sendResetLink(
            ['email' => $email],
            function (User $user, string $token) {
                $user->sendPasswordResetNotification($token);
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->success(
                null,
                'Jika email terdaftar di sistem kami, tautan reset password akan segera dikirim.',
            );
        }

        return $this->success(
            ['email' => $email],
            'Tautan reset password telah dikirim ke email Anda.',
        );
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return $this->error(
            'Token tidak valid atau sudah kadaluarsa.',
            422,
            ['token' => [__($status)]],
        );
    }
}