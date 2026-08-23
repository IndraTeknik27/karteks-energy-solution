<?php

namespace App\Http\Requests\Api\V1\CustomBattery;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'admin_note' => ['required', 'string', 'min:10', 'max:2000'],
            'field_changes' => ['nullable', 'array'],
            'field_changes.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_note.required' => 'Catatan revisi wajib diisi.',
            'admin_note.min' => 'Catatan revisi minimal 10 karakter.',
        ];
    }
}