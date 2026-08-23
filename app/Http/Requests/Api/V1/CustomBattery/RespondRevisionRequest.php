<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class RespondRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_response' => ['required', 'string', 'min:5', 'max:2000'],
            'updated_fields' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_response.required' => 'Tanggapan revisi wajib diisi.',
            'customer_response.min' => 'Tanggapan minimal 5 karakter.',
        ];
    }
}