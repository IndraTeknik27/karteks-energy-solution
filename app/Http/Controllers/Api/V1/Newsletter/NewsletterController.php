<?php

namespace App\Http\Controllers\Api\V1\Newsletter;

use App\Http\Controllers\Api\Controller;
use App\Mail\Newsletter\WelcomeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:191'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'] ?? null,
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribe_token' => \Illuminate\Support\Str::uuid()->toString(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]
        );

        if (! $subscriber->wasRecentlyCreated && ! $subscriber->is_active) {
            $subscriber->update([
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
        }

        // Send welcome email
        try {
            Mail::to($subscriber->email, $subscriber->name)
                ->queue(new WelcomeMail($subscriber));
        } catch (\Throwable $e) {
            Log::warning('Newsletter welcome email gagal: '.$e->getMessage(), ['email' => $subscriber->email]);
        }

        return $this->success([
            'email' => $subscriber->email,
            'is_active' => $subscriber->is_active,
        ], 'Terima kasih telah berlangganan newsletter KARTEKS.');
    }

    public function unsubscribe(Request $request, string $token): JsonResponse
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return $this->error('Token unsubscribe tidak valid.', 404);
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return $this->success(null, 'Anda telah berhenti berlangganan newsletter.');
    }
}