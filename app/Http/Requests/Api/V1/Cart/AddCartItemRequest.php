<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'itemable_type' => ['required', 'string', 'in:product,variation'],
            'itemable_id' => ['required', 'integer', 'min:1'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'itemable_type.required' => 'Tipe item wajib diisi.',
            'itemable_type.in' => 'Tipe item harus "product" atau "variation".',
            'itemable_id.required' => 'ID produk wajib diisi.',
            'itemable_id.integer' => 'ID produk harus angka.',
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.min' => 'Jumlah minimal 1.',
            'qty.max' => 'Jumlah maksimal 999.',
        ];
    }
}