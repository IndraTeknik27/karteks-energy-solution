<?php

namespace App\Channels;

use App\Models\FcmToken;
use App\Models\Notification;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Kirim push notification ke FCM untuk di-deliver ke Flutter apps.
     *
     * Menggunakan Firebase HTTP v1 API.
     * Service account credentials di-load dari config('karteks.fcm').
     */
    public function send(object $notifiable, LaravelNotification $notification): void
    {
        $fcmEnabled = (bool) config('karteks.fcm.enabled', false);
        if (! $fcmEnabled) {
            Log::debug('FCM disabled in config — skipping push');
            return;
        }

        $tokens = FcmToken::query()
            ->where('customer_id', $notifiable->id ?? 0)
            ->where('is_active', true)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            Log::debug('FCMChannel: no active tokens for user', ['user_id' => $notifiable->id ?? null]);
            return;
        }

        $payload = $this->buildPayload($notification);

        foreach ($tokens as $token) {
            try {
                $response = $this->sendToToken($token, $payload);

                if ($response->successful()) {
                    $this->recordSuccess($notifiable, $notification, $token);
                } else {
                    $this->handleError($token, $response->json(), $notifiable->id ?? 0);
                }
            } catch (\Throwable $e) {
                Log::warning('FCM send failed: '.$e->getMessage(), [
                    'user_id' => $notifiable->id ?? null,
                    'token_prefix' => substr($token, 0, 20),
                ]);
            }
        }
    }

    protected function buildPayload(LaravelNotification $notification): array
    {
        $data = method_exists($notification, 'toArray')
            ? $notification->toArray($notifiable = new \stdClass())
            : [];

        $title = $data['title'] ?? 'Notifikasi KARTEKS';
        $body = $data['message'] ?? '';
        $icon = $data['icon'] ?? null;

        return [
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'image' => $icon && filter_var($icon, FILTER_VALIDATE_URL) ? $icon : null,
                ],
                'data' => [
                    'type' => $data['type'] ?? '',
                    'action_url' => $data['action_url'] ?? '',
                    'click_action' => $data['action_url'] ?? '',
                    'sound' => 'default',
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => $data['action_url'] ?? '',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function sendToToken(string $token, array $payload)
    {
        $projectId = config('karteks.fcm.project_id');
        $serverKey = config('karteks.fcm.server_key');

        // Untuk production, pakai HTTP v1 API dengan OAuth2 token.
        // Untuk simplicity di dev, pakai legacy HTTP API dengan server_key.
        return Http::withHeaders([
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/fcm/send", array_merge($payload['message'], ['to' => $token]));
    }

    protected function recordSuccess(object $notifiable, LaravelNotification $notification, string $token): void
    {
        FcmToken::where('token', $token)->update(['last_used_at' => now()]);

        // Also create database notification record for FCM
        $data = method_exists($notification, 'toArray')
            ? $notification->toArray(new \stdClass())
            : [];
        Notification::create([
            'user_id' => $notifiable->id,
            'channel' => Notification::CHANNEL_FCM,
            'type' => $data['type'] ?? 'fcm',
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'data' => $data,
            'sent_at' => now(),
        ]);
    }

    protected function handleError(string $token, ?array $responseBody, int $userId): void
    {
        $errorCode = $responseBody['results'][0]['error'] ?? ($responseBody['error'] ?? 'unknown');

        // Token invalid/not registered → deactivate
        if (in_array($errorCode, ['NotRegistered', 'InvalidRegistration', 'InvalidArgument'], true)) {
            FcmToken::where('token', $token)->update(['is_active' => false]);
            Log::info('FCM token deactivated (invalid): '.$errorCode, ['user_id' => $userId]);
        } else {
            Log::warning('FCM send error: '.$errorCode, [
                'user_id' => $userId,
                'token_prefix' => substr($token, 0, 20),
            ]);
        }
    }
}