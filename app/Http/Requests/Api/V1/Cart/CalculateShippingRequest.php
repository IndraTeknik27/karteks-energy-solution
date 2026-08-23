<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CalculateShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => ['required', 'string', 'max:100'],
            'courier' => ['nullable', 'string', 'in:jne,pos,tiki,sicepat,jnt'],
        ];
    }

    public function messages(): array
    {
        return [
            'city.required' => 'Kota tujuan wajib diisi.',
            'courier.in' => 'Kurir tidak valid.',
        ];
    }
}