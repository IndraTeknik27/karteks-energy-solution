<?php

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:191', "unique:users,email,{$userId}"],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/', "unique:users,phone,{$userId}"],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan user lain.',
            'phone.regex' => 'Nomor WhatsApp tidak valid.',
            'phone.unique' => 'Nomor WhatsApp sudah digunakan user lain.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ];
    }
}