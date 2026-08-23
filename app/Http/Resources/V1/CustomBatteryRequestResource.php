<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomBatteryRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $files = $this->resource->relationLoaded('files') ? $this->files : collect();
        $revisions = $this->resource->relationLoaded('revisions') ? $this->revisions : collect();

        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->whenLoaded('customer', fn () => $this->customer?->name),
                'email' => $this->whenLoaded('customer', fn () => $this->customer?->email),
            ],
            'assigned_to' => $this->assigned_to,
            'assigned_to_name' => $this->whenLoaded('assignedTo', fn () => $this->assignedTo?->name),

            'chemistry' => $this->chemistry,
            'voltage' => $this->voltage,
            'capacity' => $this->capacity,
            'kwh' => $this->kwh !== null ? (float) $this->kwh : null,
            'application' => $this->application,
            'current_load' => $this->current_load,
            'dimensions' => $this->dimensions,
            'quantity' => (int) $this->quantity,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'description' => $this->description,
            'customer_notes' => $this->customer_notes,
            'admin_notes' => $this->admin_notes,

            'status' => $this->status,
            'is_open' => (bool) $this->is_open,
            'revision_count' => (int) $this->revision_count,

            'estimated_price' => $this->estimated_price !== null ? (float) $this->estimated_price : null,
            'estimated_price_formatted' => $this->estimated_price !== null
                ? 'Rp '.number_format((float) $this->estimated_price, 0, ',', '.')
                : null,
            'final_price' => $this->final_price !== null ? (float) $this->final_price : null,
            'final_price_formatted' => $this->final_price !== null
                ? 'Rp '.number_format((float) $this->final_price, 0, ',', '.')
                : null,

            'files_count' => (int) $files->count(),
            'files' => CustomBatteryRequestFileResource::collection($files),

            'revisions' => CustomBatteryRequestRevisionResource::collection($revisions),

            'timestamps' => [
                'assigned_at' => $this->assigned_at?->toIso8601String(),
                'quoted_at' => $this->quoted_at?->toIso8601String(),
                'approved_at' => $this->approved_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}