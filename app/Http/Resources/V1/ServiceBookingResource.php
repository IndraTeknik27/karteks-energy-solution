<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
            ],
            'service' => [
                'id' => $this->service_id,
                'name' => $this->whenLoaded('service', fn () => $this->service?->name),
                'slug' => $this->whenLoaded('service', fn () => $this->service?->slug),
                'pricing_type' => $this->whenLoaded('service', fn () => $this->service?->pricing_type),
            ],
            'technician' => $this->whenLoaded('technician', fn () => $this->technician ? [
                'id' => $this->technician->id,
                'name' => $this->technician->name,
            ] : null),

            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'scheduled_at_formatted' => $this->scheduled_at?->format('d F Y, H:i'),
            'duration_minutes' => (int) $this->duration_minutes,
            'ends_at' => $this->ends_at?->toIso8601String(),

            'location_type' => $this->location_type,
            'location_address' => $this->location_address,
            'location_coordinates' => $this->location_coordinates,

            'customer_notes' => $this->customer_notes,
            'admin_notes' => $this->admin_notes,

            'estimated_cost' => $this->estimated_cost !== null ? (float) $this->estimated_cost : null,
            'estimated_cost_formatted' => $this->estimated_cost !== null
                ? 'Rp '.number_format((float) $this->estimated_cost, 0, ',', '.')
                : null,
            'final_cost' => $this->final_cost !== null ? (float) $this->final_cost : null,
            'final_cost_formatted' => $this->final_cost !== null
                ? 'Rp '.number_format((float) $this->final_cost, 0, ',', '.')
                : null,

            'status' => $this->status,
            'is_upcoming' => (bool) $this->is_upcoming,
            'is_cancellable' => (bool) $this->is_cancellable,

            'timestamps' => [
                'started_at' => $this->started_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}