<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'name', 'attributes',
        'price', 'sale_price',
        'stock_qty', 'reserved_qty',
        'weight', 'dimensions', 'image',
        'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'dimensions' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'stock_qty' => 'integer',
            'reserved_qty' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function getAvailableQtyAttribute(): int
    {
        return max(0, $this->stock_qty - $this->reserved_qty);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function getAttributeLabelAttribute(): string
    {
        if (! $this->attributes) {
            return '';
        }
        return collect($this->attributes)->map(fn ($v, $k) => "$k: $v")->implode(', ');
    }
}