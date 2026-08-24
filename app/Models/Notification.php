<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'channel', 'type', 'title', 'message',
        'data', 'icon', 'action_url',
        'read_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    // ---- Channel Constants (FASE 4.6) ----

    public const CHANNEL_DATABASE = 'database';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_BROADCAST = 'broadcast';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_FCM = 'fcm';

    public const CHANNELS = [
        self::CHANNEL_DATABASE => 'In-App Database',
        self::CHANNEL_EMAIL => 'Email',
        self::CHANNEL_BROADCAST => 'WebSocket Broadcast',
        self::CHANNEL_WHATSAPP => 'WhatsApp Link',
        self::CHANNEL_FCM => 'Flutter Push (FCM)',
    ];

    // ---- Notification Type Constants (untuk preferences) ----

    public const TYPE_ORDER_PLACED = 'order_placed';
    public const TYPE_ORDER_PAID = 'order_paid';
    public const TYPE_ORDER_SHIPPED = 'order_shipped';
    public const TYPE_ORDER_DELIVERED = 'order_delivered';
    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    public const TYPE_CUSTOM_BATTERY_SUBMITTED = 'custom_battery_submitted';
    public const TYPE_CUSTOM_BATTERY_STATUS = 'custom_battery_status';
    public const TYPE_CUSTOM_BATTERY_REVISION = 'custom_battery_revision';

    public const TYPE_QUOTATION_SENT = 'quotation_sent';
    public const TYPE_QUOTATION_ACCEPTED = 'quotation_accepted';
    public const TYPE_QUOTATION_REJECTED = 'quotation_rejected';

    public const TYPE_BOOKING_CREATED = 'booking_created';
    public const TYPE_BOOKING_CONFIRMED = 'booking_confirmed';
    public const TYPE_BOOKING_RESCHEDULED = 'booking_rescheduled';
    public const TYPE_BOOKING_CANCELLED = 'booking_cancelled';

    public const TYPE_CONTACT_NEW_MESSAGE = 'contact_new_message';
    public const TYPE_NEWSLETTER_WELCOME = 'newsletter_welcome';

    // ---- Relations ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---- Accessors ----

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    // ---- Scopes ----

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ---- Actions ----

    public function markAsRead(): void
    {
        $this->forceFill(['read_at' => now()])->save();
    }
}