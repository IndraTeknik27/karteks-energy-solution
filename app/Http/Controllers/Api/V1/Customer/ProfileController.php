<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Customer\UpdateCustomerProfileRequest;
use App\Http\Requests\Api\V1\Customer\UploadAvatarRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'addresses']);

        $addresses = $user->addresses->map(fn ($addr) => [
            'id' => $addr->id,
            'label' => $addr->label,
            'recipient' => $addr->recipient,
            'is_primary' => (bool) $addr->is_primary,
            'city' => $addr->city,
            'province' => $addr->province,
        ])->values();

        return $this->success([
            'user' => new UserResource($user),
            'address_count' => $user->addresses->count(),
            'addresses' => $addresses,
        ], 'Data profil pelanggan.');
    }

    public function update(UpdateCustomerProfileRequest $request): JsonResponse
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

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->clearMediaCollection('avatar');

        $media = $user
            ->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        $user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'avatar' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'medium_url' => $media->getUrl('medium'),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ],
        ], 'Avatar berhasil diunggah.');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->clearMediaCollection('avatar');
        $user->load('roles');

        return $this->success(
            new UserResource($user),
            'Avatar berhasil dihapus.',
        );
    }
}