<?php

namespace App\Http\Requests\Api\V1\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_courier' => ['required', 'string', 'in:jne,pos,tiki,sicepat,jnt'],
            'shipping_service' => ['required', 'string', 'in:REG,YES,OKE'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:midtrans,bank_transfer,manual'],

            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:191'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],

            'customer_notes' => ['nullable', 'string', 'max:1000'],

            'billing_same_as_shipping' => ['nullable', 'boolean'],
            'billing_address' => ['nullable', 'array'],
            'billing_address.recipient' => ['required_with:billing_address', 'string', 'max:100'],
            'billing_address.phone' => ['required_with:billing_address', 'string'],
            'billing_address.address_line_1' => ['required_with:billing_address', 'string'],
            'billing_address.city' => ['required_with:billing_address', 'string'],
            'billing_address.province' => ['required_with:billing_address', 'string'],
            'billing_address.postal_code' => ['required_with:billing_address', 'string'],

            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address_id.required' => 'Alamat pengiriman wajib dipilih.',
            'shipping_courier.required' => 'Kurir pengiriman wajib dipilih.',
            'shipping_service.required' => 'Layanan pengiriman wajib dipilih.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid.',
        ];
    }
}