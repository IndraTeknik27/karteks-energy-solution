<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Api\Controller;
use App\Mail\Contact\AdminNotificationMail;
use App\Mail\Contact\AutoReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $message = ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
        ]);

        // Send auto-reply ke customer
        try {
            Mail::to($message->email, $message->name)
                ->queue(new AutoReplyMail($message));
        } catch (\Throwable $e) {
            Log::warning('Contact auto-reply gagal: '.$e->getMessage(), ['message_id' => $message->id]);
        }

        // Send notification ke admin
        try {
            $adminEmail = config('karteks.company.email');
            $adminName = config('karteks.company.name');
            Mail::to($adminEmail, $adminName)
                ->queue(new AdminNotificationMail($message));
        } catch (\Throwable $e) {
            Log::warning('Contact admin notification gagal: '.$e->getMessage(), ['message_id' => $message->id]);
        }

        return $this->success([
            'id' => $message->id,
            'reference_number' => '#'.str_pad($message->id, 6, '0', STR_PAD_LEFT),
            'status' => 'received',
        ], 'Pesan Anda telah diterima. Kami akan membalas dalam 1x24 jam kerja.');
    }
}