<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),

            'gateway' => $this->gateway,
            'transaction_id' => $this->transaction_id,
            'payment_type' => $this->payment_type,
            'bank' => $this->bank,
            'va_number' => $this->va_number,

            'gross_amount' => (float) $this->gross_amount,
            'gross_amount_formatted' => 'Rp '.number_format((float) $this->gross_amount, 0, ',', '.'),
            'fee_amount' => (float) $this->fee_amount,
            'fee_amount_formatted' => 'Rp '.number_format((float) $this->fee_amount, 0, ',', '.'),
            'net_amount' => $this->net_amount !== null ? (float) $this->net_amount : null,
            'net_amount_formatted' => $this->net_amount !== null
                ? 'Rp '.number_format((float) $this->net_amount, 0, ',', '.')
                : null,

            'status' => $this->status,
            'is_successful' => (bool) $this->is_successful,
            'is_pending' => (bool) $this->is_pending,
            'is_failed' => (bool) $this->is_failed,
            'fraud_status' => $this->fraud_status,

            'snap_token' => $this->when(
                $request->user() && $request->user()->id === $this->order?->customer_id,
                fn () => $this->snap_token
            ),
            'redirect_url' => $this->when(
                $request->user() && $request->user()->id === $this->order?->customer_id,
                fn () => $this->redirect_url
            ),
            'client_key' => $this->when(
                $request->user() && $request->user()->id === $this->order?->customer_id,
                fn () => app(\App\Services\V1\MidtransService::class)->clientKey()
            ),

            'paid_at' => $this->paid_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
