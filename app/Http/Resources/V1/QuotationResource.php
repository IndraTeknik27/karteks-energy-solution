<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->resource->relationLoaded('items') ? $this->items : collect();

        return [
            'id' => $this->id,
            'quotation_number' => $this->quotation_number,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->whenLoaded('customer', fn () => $this->customer?->name),
                'email' => $this->whenLoaded('customer', fn () => $this->customer?->email),
            ],
            'created_by' => $this->created_by,
            'creator_name' => $this->whenLoaded('creator', fn () => $this->creator?->name),

            'quotable_type' => $this->quotable_type,
            'quotable_id' => $this->quotable_id,
            'quotable' => $this->whenLoaded('quotable', function () {
                if (! $this->quotable) return null;
                return [
                    'type' => $this->quotable_type,
                    'id' => $this->quotable_id,
                    'display' => $this->quotable_type === \App\Models\CustomBatteryRequest::class
                        ? $this->quotable->request_number
                        : '#'.$this->quotable_id,
                ];
            }),

            'title' => $this->title,
            'description' => $this->description,
            'terms_conditions' => $this->terms_conditions,
            'notes' => $this->notes,

            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $this->subtotal, 0, ',', '.'),
            'discount' => (float) $this->discount,
            'discount_formatted' => 'Rp '.number_format((float) $this->discount, 0, ',', '.'),
            'tax' => (float) $this->tax,
            'tax_formatted' => 'Rp '.number_format((float) $this->tax, 0, ',', '.'),
            'total' => (float) $this->total,
            'total_formatted' => 'Rp '.number_format((float) $this->total, 0, ',', '.'),
            'currency' => 'IDR',

            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'is_expired' => (bool) $this->is_expired,

            'status' => $this->status,

            'items_count' => (int) $items->count(),
            'items' => QuotationItemResource::collection($items),

            'rejection_reason' => $this->rejection_reason,

            'timestamps' => [
                'sent_at' => $this->sent_at?->toIso8601String(),
                'viewed_at' => $this->viewed_at?->toIso8601String(),
                'accepted_at' => $this->accepted_at?->toIso8601String(),
                'rejected_at' => $this->rejected_at?->toIso8601String(),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}