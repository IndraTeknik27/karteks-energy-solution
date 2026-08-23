<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'payment_number', 'gateway', 'transaction_id',
        'payment_type', 'va_number', 'bank',
        'gross_amount', 'fee_amount', 'net_amount', 'status',
        'fraud_status', 'signature_key',
        'raw_request', 'raw_response', 'redirect_url', 'snap_token',
        'paid_at', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'raw_request' => 'array',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return in_array($this->status, ['settlement', 'captured']);
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsFailedAttribute(): bool
    {
        return in_array($this->status, ['denied', 'cancelled', 'expired', 'failed']);
    }
}