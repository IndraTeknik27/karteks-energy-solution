<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'sort',
        'is_filterable', 'is_visible', 'is_variation',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_filterable' => 'boolean',
            'is_visible' => 'boolean',
            'is_variation' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}