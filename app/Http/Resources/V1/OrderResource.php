<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items') ?? collect();
        $statusHistories = $this->whenLoaded('statusHistories') ?? collect();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->status,
            'is_paid' => (bool) $this->is_paid,
            'is_pending_payment' => (bool) $this->is_pending_payment,
            'is_completed' => (bool) $this->is_completed,
            'is_cancelled' => (bool) $this->is_cancelled,

            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],

            'payment_method' => $this->payment_method,

            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $this->subtotal, 0, ',', '.'),
            'discount' => (float) $this->coupon_discount,
            'discount_formatted' => 'Rp '.number_format((float) $this->coupon_discount, 0, ',', '.'),
            'tax' => (float) $this->tax,
            'tax_formatted' => 'Rp '.number_format((float) $this->tax, 0, ',', '.'),
            'shipping_cost' => (float) $this->shipping_cost,
            'shipping_cost_formatted' => 'Rp '.number_format((float) $this->shipping_cost, 0, ',', '.'),
            'total' => (float) $this->total,
            'total_formatted' => 'Rp '.number_format((float) $this->total, 0, ',', '.'),
            'currency' => 'IDR',

            'coupon_code' => $this->coupon_code,

            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'shipping_courier' => $this->shipping_courier,
            'shipping_service' => $this->shipping_service,
            'shipping_tracking_number' => $this->shipping_tracking_number,

            'customer_notes' => $this->customer_notes,

            'item_count' => (int) $items->sum('qty'),
            'items' => OrderItemResource::collection($items),

            'timestamps' => [
                'paid_at' => $this->paid_at?->toIso8601String(),
                'shipped_at' => $this->shipped_at?->toIso8601String(),
                'delivered_at' => $this->delivered_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
                'cancelled_at' => $this->cancelled_at?->toIso8601String(),
                'expires_at' => $this->expires_at?->toIso8601String(),
            ],

            'status_history' => $statusHistories->map(fn ($h) => [
                'from_status' => $h->from_status,
                'to_status' => $h->to_status,
                'note' => $h->note,
                'changed_by' => $h->changed_by,
                'changed_by_role' => $h->changed_by_role,
                'created_at' => $h->created_at?->toIso8601String(),
            ])->values(),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}