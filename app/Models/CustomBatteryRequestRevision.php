<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomBatteryRequestRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', 'revision_number', 'requested_by',
        'admin_note', 'customer_response', 'field_changes', 'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'field_changes' => 'array',
            'responded_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomBatteryRequest::class, 'request_id');
    }
}