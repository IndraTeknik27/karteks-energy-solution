<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Kirim notifikasi via WhatsApp link (wa.me).
     *
     * Pattern ini tidak mengirim pesan otomatis via WhatsApp Business API
     * (yang butuh official sender). Sebaliknya, generate wa.me URL dengan
     * pre-filled message yang di-include di in-app notification 'data'.
     *
     * Customer tinggal klik link di app/web → WhatsApp terbuka dengan template pesan siap kirim.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        try {
            $message = $this->buildMessage($notification);
            $phone = $this->resolvePhone($notifiable);

            if (! $phone) {
                Log::debug('WhatsAppChannel: no phone for notifiable', [
                    'notifiable_id' => $notifiable->id ?? null,
                ]);
                return;
            }

            $url = $this->buildWhatsappUrl($phone, $message);

            // Push WhatsApp URL ke in-app notification (via database channel)
            if (method_exists($notification, 'toDatabase')) {
                $database = $notification->toDatabase($notifiable);
                $database['whatsapp_url'] = $url;
                $database['whatsapp_phone'] = $phone;
                $this->saveDatabaseEntry($notifiable, $notification, $database);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppChannel failed: '.$e->getMessage(), [
                'notifiable_id' => $notifiable->id ?? null,
            ]);
        }
    }

    protected function buildMessage(Notification $notification): string
    {
        $title = method_exists($notification, 'toArray')
            ? ($notification->toArray($notifiable ?? new \stdClass())['title'] ?? 'Notifikasi')
            : 'Notifikasi';
        $message = property_exists($notification, 'title') ? ($notification->title ?? '') : '';

        $base = "Halo, ini notifikasi dari KARTEKS:\n\n*{$title}*\n{$message}";

        // Append action_url jika ada
        if (method_exists($notification, 'toArray')) {
            $arr = $notification->toArray($notifiable ?? new \stdClass());
            if (! empty($arr['action_url'])) {
                $base .= "\n\nLihat detail: {$arr['action_url']}";
            }
        }

        $base .= "\n\n-- KARTEKS ENERGY SOLUTION";

        return $base;
    }

    protected function resolvePhone(object $notifiable): ?string
    {
        // Try various phone fields
        $phone = $notifiable->phone
            ?? $notifiable->phone_number
            ?? $notifiable->whatsapp
            ?? null;

        if (! $phone) {
            return null;
        }

        // Normalize ke format 62xxx (Indonesia)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8') && strlen($phone) >= 9) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    protected function buildWhatsappUrl(string $phone, string $message): string
    {
        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    protected function saveDatabaseEntry(object $notifiable, Notification $notification, array $data): void
    {
        if (! isset($notifiable->id)) {
            return;
        }
        \App\Models\Notification::create([
            'user_id' => $notifiable->id,
            'channel' => \App\Models\Notification::CHANNEL_WHATSAPP,
            'type' => $data['type'] ?? 'whatsapp',
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['whatsapp_url'] ?? null,
            'data' => $data,
            'sent_at' => now(),
        ]);
    }
}