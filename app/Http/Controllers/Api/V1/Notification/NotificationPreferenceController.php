<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\Controller;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Services\V1\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationPreferenceController extends Controller
{
    /**
     * GET /api/v1/notification-preferences
     * List user's notification preferences (all types).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $preferences = NotificationPreference::where('user_id', $user->id)
            ->get()
            ->keyBy('notification_type');

        // Build response dengan default channels jika preference belum di-set
        $response = [];
        foreach (Notification::where('type', '!=', null)
            ->distinct()->pluck('type')->filter() as $type) {
            $response[] = [
                'notification_type' => $type,
                'channels' => $preferences[$type]->channels ?? NotificationService::DEFAULT_CHANNELS[$this->resolveRole($user)] ?? ['database', 'broadcast'],
                'is_enabled' => $preferences[$type]->is_enabled ?? true,
                'is_custom' => isset($preferences[$type]),
            ];
        }

        // Include types yang belum pernah ada notif-nya (pre-defined)
        $definedTypes = [
            Notification::TYPE_ORDER_PLACED,
            Notification::TYPE_ORDER_PAID,
            Notification::TYPE_ORDER_SHIPPED,
            Notification::TYPE_ORDER_DELIVERED,
            Notification::TYPE_CUSTOM_BATTERY_SUBMITTED,
            Notification::TYPE_CUSTOM_BATTERY_STATUS,
            Notification::TYPE_QUOTATION_SENT,
            Notification::TYPE_BOOKING_CREATED,
            Notification::TYPE_BOOKING_CONFIRMED,
        ];
        foreach ($definedTypes as $type) {
            if (! collect($response)->contains('notification_type', $type)) {
                $response[] = [
                    'notification_type' => $type,
                    'channels' => NotificationService::DEFAULT_CHANNELS[$this->resolveRole($user)] ?? ['database', 'broadcast'],
                    'is_enabled' => true,
                    'is_custom' => false,
                ];
            }
        }

        return $this->success([
            'preferences' => $response,
            'available_channels' => Notification::CHANNELS,
            'default_channels' => NotificationService::DEFAULT_CHANNELS[$this->resolveRole($user)] ?? ['database', 'broadcast'],
        ], 'Notification preferences.');
    }

    /**
     * PUT /api/v1/notification-preferences/{type}
     * Update single notification preference.
     */
    public function update(Request $request, string $type): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'channels' => ['required', 'array', 'min:0'],
            'channels.*' => ['string', 'in:'.implode(',', array_keys(Notification::CHANNELS))],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $preference = NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'notification_type' => $type],
            [
                'channels' => $request->input('channels', []),
                'is_enabled' => $request->boolean('is_enabled', true),
            ]
        );

        return $this->success([
            'notification_type' => $type,
            'channels' => $preference->channels,
            'is_enabled' => $preference->is_enabled,
        ], 'Preference diperbarui.');
    }

    /**
     * DELETE /api/v1/notification-preferences/{type}
     * Reset preference ke default (delete record).
     */
    public function destroy(Request $request, string $type): JsonResponse
    {
        $user = $request->user();

        NotificationPreference::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->delete();

        return $this->success(null, 'Preference direset ke default.');
    }

    /**
     * POST /api/v1/notification-preferences/reset-all
     * Reset ALL preferences ke default.
     */
    public function resetAll(Request $request): JsonResponse
    {
        $count = NotificationPreference::where('user_id', $request->user()->id)->delete();

        return $this->success([
            'deleted_count' => $count,
        ], "{$count} preferences direset.");
    }

    protected function resolveRole($user): string
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return 'admin';
        }
        if ($user->hasAnyRole(['manager', 'staff', 'sales', 'technician', 'finance'])) {
            return 'staff';
        }
        return 'customer';
    }
}