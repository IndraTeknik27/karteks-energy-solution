<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'url', 'route_name',
        'route_params', 'icon', 'target', 'sort', 'is_active', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'route_params' => 'array',
            'meta' => 'array',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->url) {
            return $this->url;
        }
        if ($this->route_name) {
            $params = $this->route_params ?? [];
            try {
                return route($this->route_name, $params);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }
}