<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $source = $model->slugSource() ?? $model->name ?? $model->title ?? null;
                if ($source) {
                    $model->slug = static::generateUniqueSlug($model, $source);
                }
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->slugSource() ?? 'name') && empty($model->slug)) {
                $source = $model->{$model->slugSource() ?? 'name'};
                $model->slug = static::generateUniqueSlug($model, $source, $model->id);
            }
        });
    }

    protected function slugSource(): ?string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'name';
    }

    protected static function generateUniqueSlug($model, string $source, ?int $ignoreId = null): string
    {
        $slug = Str::slug($source);
        $original = $slug;
        $count = 1;
        $column = 'slug';
        $table = $model->getTable();

        $query = static::where($column, $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $original.'-'.$count++;
            $query = static::where($column, $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}