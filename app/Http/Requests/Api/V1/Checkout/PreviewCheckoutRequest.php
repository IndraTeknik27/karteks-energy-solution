<?php

namespace App\Http\Requests\Api\V1\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PreviewCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'shipping_courier' => ['nullable', 'string', 'in:jne,pos,tiki,sicepat,jnt'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}