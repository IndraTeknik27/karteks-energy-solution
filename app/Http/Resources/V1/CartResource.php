<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items') ?? collect();

        $itemCount = (int) $items->sum('qty');

        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'customer_id' => $this->customer_id,
            'is_guest' => is_null($this->customer_id),
            'coupon_code' => $this->coupon_code,
            'item_count' => $itemCount,
            'item_unique_count' => $items->count(),
            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $this->subtotal, 0, ',', '.'),
            'discount' => (float) $this->discount,
            'discount_formatted' => 'Rp '.number_format((float) $this->discount, 0, ',', '.'),
            'tax' => (float) $this->tax,
            'tax_formatted' => 'Rp '.number_format((float) $this->tax, 0, ',', '.'),
            'shipping_cost' => (float) $this->shipping_cost,
            'shipping_cost_formatted' => 'Rp '.number_format((float) $this->shipping_cost, 0, ',', '.'),
            'total' => (float) $this->total,
            'total_formatted' => 'Rp '.number_format((float) $this->total, 0, ',', '.'),
            'currency' => 'IDR',
            'expires_at' => $this->expires_at?->toIso8601String(),
            'items' => CartItemResource::collection($items),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}