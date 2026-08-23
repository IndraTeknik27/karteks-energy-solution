<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Service extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'category_id', 'name', 'slug',
        'short_description', 'description',
        'pricing_type', 'base_price', 'starting_price',
        'duration_minutes', 'image',
        'features', 'requirements',
        'is_active', 'is_featured', 'sort',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'requirements' => 'array',
            'base_price' => 'decimal:2',
            'starting_price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image') ?: $this->image;
    }

    public function getPriceLabelAttribute(): string
    {
        return match ($this->pricing_type) {
            'fixed' => 'Rp '.number_format((float) $this->base_price, 0, ',', '.'),
            'starting_price' => 'Mulai Rp '.number_format((float) $this->starting_price, 0, ',', '.'),
            'quotation' => 'Berdasarkan Quotation',
            'free' => 'Gratis',
            default => 'Hubungi Kami',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}