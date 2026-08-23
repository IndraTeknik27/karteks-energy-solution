<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CustomBatteryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number', 'customer_id', 'assigned_to',
        'chemistry', 'voltage', 'capacity', 'kwh', 'application',
        'current_load', 'dimensions', 'quantity', 'deadline',
        'description', 'customer_notes', 'admin_notes',
        'status', 'estimated_price', 'final_price',
        'revision_count', 'assigned_at', 'quoted_at', 'approved_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'dimensions' => 'array',
            'kwh' => 'decimal:2',
            'quantity' => 'integer',
            'revision_count' => 'integer',
            'deadline' => 'date',
            'estimated_price' => 'decimal:2',
            'final_price' => 'decimal:2',
            'assigned_at' => 'datetime',
            'quoted_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CustomBatteryRequestFile::class, 'request_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CustomBatteryRequestRevision::class, 'request_id');
    }

    public function quotations(): MorphMany
    {
        return $this->morphMany(Quotation::class, 'quotable');
    }

    public function getIsOpenAttribute(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled', 'rejected']);
    }
}