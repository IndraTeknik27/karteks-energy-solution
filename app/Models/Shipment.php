<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number', 'order_id', 'warehouse_id',
        'courier_code', 'courier_name', 'courier_service',
        'tracking_number', 'weight', 'cost', 'status',
        'origin_address', 'destination_address',
        'packed_at', 'shipped_at', 'delivered_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'cost' => 'decimal:2',
            'origin_address' => 'array',
            'destination_address' => 'array',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(ShipmentTracking::class)->orderBy('occurred_at');
    }

    public function getLatestTrackingAttribute(): ?ShipmentTracking
    {
        return $this->trackings()->orderByDesc('occurred_at')->first();
    }
}