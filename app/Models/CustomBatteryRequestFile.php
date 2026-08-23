<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomBatteryRequestFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', 'file_path', 'original_name',
        'mime_type', 'file_size', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomBatteryRequest::class, 'request_id');
    }

    public function getSizeKbAttribute(): float
    {
        return round($this->file_size / 1024, 2);
    }
}