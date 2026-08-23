<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $itemable = $this->itemable;
        $subtotal = (float) ($this->qty * (float) $this->price_snapshot);

        $item = [
            'id' => $itemable?->id,
            'name' => $itemable?->name,
            'slug' => $itemable?->slug ?? null,
            'image_url' => null,
        ];

        if ($itemable && method_exists($itemable, 'getFirstMediaUrl')) {
            $item['image_url'] = $itemable->getFirstMediaUrl('images', 'thumb')
                ?: $itemable->getFirstMediaUrl('image', 'thumb');
        }

        return [
            'id' => $this->id,
            'itemable_type' => $this->itemable_type,
            'itemable_id' => $this->itemable_id,
            'item' => $item,
            'qty' => $this->qty,
            'price' => (float) $this->price_snapshot,
            'price_formatted' => 'Rp '.number_format((float) $this->price_snapshot, 0, ',', '.'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'notes' => $this->notes,
        ];
    }
}