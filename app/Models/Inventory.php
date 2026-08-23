<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'variation_id', 'warehouse_id',
        'qty', 'reserved_qty', 'low_stock_threshold', 'location_code',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'reserved_qty' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getAvailableQtyAttribute(): int
    {
        return max(0, $this->qty - $this->reserved_qty);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_qty <= $this->low_stock_threshold;
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('(qty - reserved_qty) <= low_stock_threshold');
    }
}