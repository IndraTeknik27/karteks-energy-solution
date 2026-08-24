<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'notification_type', 'channels', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isChannelEnabled(string $channel): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        return in_array($channel, $this->channels ?? [], true);
    }
}