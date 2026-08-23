<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomBatteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'chemistry' => ['sometimes', 'string'],
            'voltage' => ['sometimes', 'string'],
            'capacity' => ['sometimes', 'nullable', 'string', 'max:50'],
            'kwh' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
            'application' => ['sometimes', 'string'],
            'current_load' => ['sometimes', 'nullable', 'string', 'max:100'],
            'dimensions' => ['sometimes', 'nullable', 'array'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'deadline' => ['sometimes', 'nullable', 'date'],
            'description' => ['sometimes', 'string', 'min:20', 'max:5000'],
            'customer_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}