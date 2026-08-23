<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceBooking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RESCHEDULED = 'rescheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_number', 'customer_id', 'service_id', 'technician_id',
        'customer_name', 'customer_phone', 'customer_email',
        'scheduled_at', 'duration_minutes', 'location_type',
        'location_address', 'location_coordinates',
        'customer_notes', 'admin_notes',
        'estimated_cost', 'final_cost', 'status',
        'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'location_coordinates' => 'array',
            'estimated_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id');
    }

    public function quotations(): MorphMany
    {
        return $this->morphMany(Quotation::class, 'quotable');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now());
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED], true)
            && $this->scheduled_at?->isFuture();
    }

    public function getIsCancellableAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED,
        ], true);
    }

    public function getEndsAtAttribute(): ?\Illuminate\Support\Carbon
    {
        if (! $this->scheduled_at || ! $this->duration_minutes) {
            return null;
        }

        return $this->scheduled_at->copy()->addMinutes((int) $this->duration_minutes);
    }
}