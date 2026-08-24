<?php

namespace App\Services\V1;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Default channels per user role (jika preference belum di-set).
     */
    public const DEFAULT_CHANNELS = [
        'customer' => ['database', 'broadcast'],
        'admin' => ['database', 'broadcast', 'email'],
        'staff' => ['database', 'broadcast', 'email'],
    ];

    /**
     * Resolve channels to use untuk user + notification type.
     * Priority: UserPreference > RoleDefault.
     */
    public function resolveChannels(User $user, string $notificationType): array
    {
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('notification_type', $notificationType)
            ->where('is_enabled', true)
            ->first();

        if ($preference && ! empty($preference->channels)) {
            return $preference->channels;
        }

        $role = $this->resolveRole($user);
        return self::DEFAULT_CHANNELS[$role] ?? self::DEFAULT_CHANNELS['customer'];
    }

    /**
     * Determine role untuk default channels.
     */
    protected function resolveRole(User $user): string
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return 'admin';
        }
        if ($user->hasAnyRole(['manager', 'staff', 'sales', 'technician', 'finance'])) {
            return 'staff';
        }
        return 'customer';
    }

    /**
     * Send notification multi-channel to user.
     *
     * @param  string  $type  e.g. Notification::TYPE_ORDER_PLACED
     * @param  string  $title
     * @param  string  $message
     * @param  array  $data  Extra context (icon, action_url, dll)
     * @param  array|null  $channelsOverride  Override channels (skip preference check)
     * @return array  Channel yang successfully dikirim
     */
    public function sendToUser(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?array $channelsOverride = null
    ): array {
        $channels = $channelsOverride ?? $this->resolveChannels($user, $type);

        $data = array_merge([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['action_url'] ?? null,
        ], $data);

        $sent = [];

        foreach ($channels as $channel) {
            try {
                $this->dispatchToChannel($channel, $user, $type, $title, $message, $data);
                $sent[] = $channel;
            } catch (\Throwable $e) {
                Log::warning("Notification channel [{$channel}] failed: ".$e->getMessage(), [
                    'user_id' => $user->id,
                    'type' => $type,
                ]);
            }
        }

        return $sent;
    }

    /**
     * Dispatch to single channel.
     */
    protected function dispatchToChannel(
        string $channel,
        User $user,
        string $type,
        string $title,
        string $message,
        array $data
    ): void {
        match ($channel) {
            Notification::CHANNEL_DATABASE => $this->sendDatabase($user, $type, $title, $message, $data),
            Notification::CHANNEL_BROADCAST => $this->sendBroadcast($user, $type, $title, $message, $data),
            Notification::CHANNEL_EMAIL => $this->sendEmail($user, $data),
            Notification::CHANNEL_WHATSAPP => $this->sendWhatsApp($user, $data),
            Notification::CHANNEL_FCM => $this->sendFcm($user, $data),
            default => Log::warning("Unknown channel: {$channel}"),
        };
    }

    protected function sendDatabase(User $user, string $type, string $title, string $message, array $data): void
    {
        Notification::create([
            'user_id' => $user->id,
            'channel' => Notification::CHANNEL_DATABASE,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'data' => $data,
            'sent_at' => now(),
        ]);
    }

    protected function sendBroadcast(User $user, string $type, string $title, string $message, array $data): void
    {
        // Save to DB first (broadcast typically paired with DB)
        $this->sendDatabase($user, $type, $title, $message, $data);

        // Then broadcast via Laravel broadcasting (real-time via WebSocket)
        try {
            event(new \Illuminate\Notifications\Events\BroadcastNotificationCreated(
                $user,
                new \Illuminate\Notifications\Notification($data)
            ));
        } catch (\Throwable $e) {
            // If broadcasting not configured, skip
            Log::debug('Broadcast failed (probably no broadcasting driver): '.$e->getMessage());
        }
    }

    protected function sendEmail(User $user, array $data): void
    {
        if (! $user->email) {
            return;
        }

        // If there's a mailable class in data, use it
        $mailableClass = $data['mailable'] ?? null;
        if ($mailableClass && class_exists($mailableClass)) {
            $mailable = new $mailableClass($data['mailable_data'] ?? null);
            \Illuminate\Support\Facades\Mail::to($user->email, $user->name)->queue($mailable);
            return;
        }

        // Otherwise, generic notification email
        \Illuminate\Support\Facades\Mail::to($user->email, $user->name)->queue(
            new \App\Mail\Notification\GenericNotificationMail($user, $data)
        );
    }

    protected function sendWhatsApp(User $user, array $data): void
    {
        $phone = $user->phone ?? $user->phone_number ?? null;
        if (! $phone) {
            return;
        }

        // Normalize phone ke format 62xxx
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $message = ($data['title'] ?? 'Notifikasi KARTEKS')."\n\n".($data['message'] ?? '');
        if (! empty($data['action_url'])) {
            $message .= "\n\n".$data['action_url'];
        }
        $url = 'https://wa.me/'.$phone.'?text='.rawurlencode($message);

        // Save notification with WhatsApp URL
        Notification::create([
            'user_id' => $user->id,
            'channel' => Notification::CHANNEL_WHATSAPP,
            'type' => $data['type'] ?? 'whatsapp',
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? null,
            'action_url' => $url,
            'data' => array_merge($data, ['whatsapp_url' => $url, 'whatsapp_phone' => $phone]),
            'sent_at' => now(),
        ]);
    }

    protected function sendFcm(User $user, array $data): void
    {
        $fcmChannel = app(\App\Channels\FcmChannel::class);
        $notification = new \Illuminate\Notifications\Notification($data);
        $fcmChannel->send($user, $notification);
    }

    /**
     * Send to multiple users (broadcast pattern).
     */
    public function sendToMany(
        iterable $users,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?array $channelsOverride = null
    ): int {
        $count = 0;
        foreach ($users as $user) {
            $this->sendToUser($user, $type, $title, $message, $data, $channelsOverride);
            $count++;
        }
        return $count;
    }

    /**
     * Send to all admins/staff.
     */
    public function sendToAdmins(
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?array $channelsOverride = null
    ): int {
        $admins = User::query()->whereHas('roles', function ($q) {
            $q->whereIn('name', ['super-admin', 'admin']);
        })->get();

        return $this->sendToMany($admins, $type, $title, $message, $data, $channelsOverride);
    }

    /**
     * Send to all staff (admin + manager + technician, etc).
     */
    public function sendToStaff(
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?array $channelsOverride = null
    ): int {
        $staff = User::query()->whereHas('roles', function ($q) {
            $q->whereIn('name', ['super-admin', 'admin', 'manager', 'staff', 'sales', 'technician', 'finance']);
        })->get();

        return $this->sendToMany($staff, $type, $title, $message, $data, $channelsOverride);
    }
}