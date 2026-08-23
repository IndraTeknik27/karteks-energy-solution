<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return $this->error(
                'Tautan verifikasi tidak valid atau sudah kadaluarsa.',
                403,
            );
        }

        $user = User::find($id);

        if (! $user) {
            return $this->notFound('Pengguna tidak ditemukan.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return $this->error(
                'Hash verifikasi tidak cocok.',
                403,
            );
        }

        if ($user->hasVerifiedEmail()) {
            $user->load('roles');

            return $this->success(
                new UserResource($user),
                'Email sudah diverifikasi sebelumnya.',
            );
        }

        if ($user->markEmailAsVerified()) {
            $user->load('roles');

            return $this->success(
                new UserResource($user),
                'Email berhasil diverifikasi. Akun Anda sudah aktif.',
            );
        }

        return $this->error('Gagal memverifikasi email. Silakan coba lagi.', 500);
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email Anda sudah diverifikasi sebelumnya.', 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->success(
            ['email' => $user->email],
            'Tautan verifikasi telah dikirim ulang ke email Anda.',
        );
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'email' => $user->email,
            'verified' => $user->hasVerifiedEmail(),
            'verified_at' => $user->email_verified_at?->toIso8601String(),
        ], 'Status verifikasi email.');
    }
}