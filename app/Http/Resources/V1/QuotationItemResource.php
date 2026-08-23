<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quotation_id' => $this->quotation_id,
            'name' => $this->name,
            'description' => $this->description,
            'qty' => (int) $this->qty,
            'unit_price' => (float) $this->unit_price,
            'unit_price_formatted' => 'Rp '.number_format((float) $this->unit_price, 0, ',', '.'),
            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp '.number_format((float) $this->subtotal, 0, ',', '.'),
            'sort' => (int) $this->sort,
        ];
    }
}