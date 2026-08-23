<?php

namespace App\Http\Requests\Api\V1\Booking;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'new_status' => ['nullable', 'string', 'in:confirmed,in_progress,completed'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'final_cost' => ['nullable', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_status.in' => 'Status tidak valid.',
        ];
    }
}