<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->toDateString(),
            'avatar_url' => $this->avatar_url ?? $this->getFirstMediaUrl('avatar', 'medium') ?: null,
            'is_active' => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'primary_role' => $this->whenLoaded('roles', fn () => $this->roles->first()?->name),
            'is_admin' => $this->isAdmin(),
            'is_customer' => $this->isCustomer(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}