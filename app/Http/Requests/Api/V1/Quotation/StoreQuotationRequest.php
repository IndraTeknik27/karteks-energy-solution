<?php

namespace App\Http\Requests\Api\V1\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'quotable_type' => ['nullable', 'string', 'in:App\Models\CustomBatteryRequest'],
            'quotable_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:5000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'terms_conditions' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:191'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer wajib dipilih.',
            'title.required' => 'Judul quotation wajib diisi.',
            'items.required' => 'Minimal 1 item harus ada.',
            'items.min' => 'Minimal 1 item harus ada.',
            'items.*.name.required' => 'Nama item wajib diisi.',
            'items.*.qty.required' => 'Jumlah item wajib diisi.',
            'items.*.unit_price.required' => 'Harga satuan item wajib diisi.',
        ];
    }
}