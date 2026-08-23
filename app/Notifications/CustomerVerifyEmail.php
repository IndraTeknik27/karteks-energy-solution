<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomerVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'api.auth.email.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage)
            ->subject('Verifikasi Email - KARTEKS ENERGY SOLUTION')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih telah mendaftar di KARTEKS ENERGY SOLUTION.')
            ->line('Untuk menyelesaikan pendaftaran dan mengaktifkan akun Anda, silakan verifikasi alamat email dengan menekan tombol di bawah ini.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Tautan verifikasi ini berlaku selama 60 menit.')
            ->line('Jika Anda tidak merasa mendaftar di KARTEKS, abaikan email ini.')
            ->salutation('Salam hangat, Tim KARTEKS ENERGY SOLUTION');
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'email_verification',
            'message' => 'Verifikasi email Anda untuk mengaktifkan akun.',
        ];
    }
}