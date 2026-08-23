<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class TransitionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'new_status' => ['required', 'string', 'in:under_review,quoted,approved,rejected,in_production,completed'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_status.required' => 'Status baru wajib diisi.',
            'new_status.in' => 'Status baru tidak valid.',
        ];
    }
}