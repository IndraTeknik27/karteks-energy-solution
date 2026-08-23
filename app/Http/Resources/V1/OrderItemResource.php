<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'itemable_type' => $this->itemable_type,
            'itemable_id' => $this->itemable_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'image' => $this->image,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
            'qty' => $this->qty,
            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $this->subtotal, 0, ',', '.'),
            'variation_attributes' => $this->variation_attributes,
            'notes' => $this->notes,
        ];
    }
}