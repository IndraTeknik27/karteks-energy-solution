<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\Controller;
use App\Models\FcmToken;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     * List user's notifications dengan filter channel/type/read.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        $query = Notification::query()
            ->where('user_id', $user->id)
            ->latest('sent_at');

        // Filter by channel
        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }

        // Filter by read/unread
        if ($request->has('unread_only')) {
            $query->when($request->boolean('unread_only'), fn ($q) => $q->unread());
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate($perPage);

        return $this->success([
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 'Daftar notifikasi.');
    }

    /**
     * GET /api/v1/notifications/unread-count
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return $this->success([
            'count' => $count,
        ], 'Unread count.');
    }

    /**
     * POST /api/v1/notifications/{id}/read
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return $this->error('Notifikasi tidak ditemukan.', 404);
        }

        $notification->markAsRead();

        return $this->success([
            'id' => $notification->id,
            'read_at' => $notification->read_at->toIso8601String(),
        ], 'Notifikasi ditandai dibaca.');
    }

    /**
     * POST /api/v1/notifications/read-all
     * Mark all user notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return $this->success([
            'updated_count' => $count,
        ], "{$count} notifikasi ditandai dibaca.");
    }

    /**
     * DELETE /api/v1/notifications/{id}
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return $this->error('Notifikasi tidak ditemukan.', 404);
        }

        $notification->delete();

        return $this->success(null, 'Notifikasi dihapus.');
    }

    /**
     * POST /api/v1/notifications/fcm-token
     * Register FCM token untuk push notification ke Flutter.
     */
    public function registerFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'max:500'],
            'device_id' => ['nullable', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:191'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $token = FcmToken::updateOrCreate(
            [
                'customer_id' => $request->user()->id,
                'token' => $request->input('token'),
            ],
            [
                'device_id' => $request->input('device_id'),
                'device_name' => $request->input('device_name'),
                'platform' => $request->input('platform', 'android'),
                'app_version' => $request->input('app_version'),
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return $this->success([
            'token_id' => $token->id,
            'platform' => $token->platform,
            'is_active' => $token->is_active,
        ], 'FCM token terdaftar.');
    }

    /**
     * DELETE /api/v1/notifications/fcm-token
     * Unregister FCM token (logout dari device).
     */
    public function unregisterFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $count = FcmToken::where('customer_id', $request->user()->id)
            ->where('token', $request->input('token'))
            ->update(['is_active' => false]);

        return $this->success([
            'deactivated_count' => $count,
        ], 'FCM token dinonaktifkan.');
    }
}