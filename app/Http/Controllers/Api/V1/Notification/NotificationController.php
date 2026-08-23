<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\Controller;

class NotificationController extends Controller
{
    public function index() { return $this->success([]); }
    public function unreadCount() { return $this->success(['count' => 0]); }
    public function markAsRead($id) { return $this->success(null, 'Marked as read.'); }
    public function markAllAsRead() { return $this->success(null, 'All marked as read.'); }
    public function destroy($id) { return $this->success(null, 'Deleted.'); }
    public function registerFcmToken() { return $this->success(null, 'FCM token registered.'); }
    public function unregisterFcmToken() { return $this->success(null, 'FCM token unregistered.'); }
}
