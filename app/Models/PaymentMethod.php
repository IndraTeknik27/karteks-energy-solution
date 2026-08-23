<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PaymentMethod extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'code', 'name', 'type', 'logo',
        'fee_percent', 'fee_fixed', 'config',
        'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'fee_percent' => 'decimal:2',
            'fee_fixed' => 'decimal:2',
            'config' => 'array',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: $this->logo;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}