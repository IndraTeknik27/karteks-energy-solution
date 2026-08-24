<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const POSITION_HOME_HERO = 'home_hero';
    public const POSITION_HOME_SECONDARY = 'home_secondary';
    public const POSITION_CATEGORY_TOP = 'category_top';
    public const POSITION_SIDEBAR = 'sidebar';
    public const POSITION_POPUP = 'popup';

    public const POSITIONS = [
        self::POSITION_HOME_HERO => 'Hero Carousel',
        self::POSITION_HOME_SECONDARY => 'Secondary Banner',
        self::POSITION_CATEGORY_TOP => 'Category Page Top',
        self::POSITION_SIDEBAR => 'Sidebar',
        self::POSITION_POPUP => 'Popup Modal',
    ];

    protected $fillable = [
        'title', 'subtitle', 'description',
        'image_desktop', 'image_mobile',
        'link_url', 'link_text', 'link_target',
        'position', 'sort', 'is_active',
        'starts_at', 'ends_at', 'click_count',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'click_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop')->singleFile();
        $this->addMediaCollection('mobile')->singleFile();
    }

    public function getDesktopImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('desktop') ?: $this->image_desktop;
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('mobile') ?: $this->image_mobile;
    }

    /**
     * Apakah banner sedang aktif & dalam window jadwal.
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }
        return true;
    }

    /**
     * Apakah banner punya CTA button yang valid.
     */
    public function getHasCtaAttribute(): bool
    {
        return ! empty($this->link_url) || ! empty($this->link_text);
    }

    /**
     * Increment click count atomically.
     */
    public function recordClick(): int
    {
        return $this->increment('click_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeScheduled($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('starts_at')->orWhereNotNull('ends_at');
        });
    }
}