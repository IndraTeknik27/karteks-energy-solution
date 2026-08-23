<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = $this->buildWebResetUrl($notifiable->getEmailForPasswordReset(), $this->token);

        return (new MailMessage)
            ->subject('Reset Password - KARTEKS ENERGY SOLUTION')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->line('Jika Anda yang meminta, silakan tekan tombol di bawah ini untuk membuat password baru.')
            ->action('Reset Password', $resetUrl)
            ->line('Tautan reset ini berlaku selama 60 menit.')
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini. Password Anda tetap aman.')
            ->salutation('Salam hangat, Tim KARTEKS ENERGY SOLUTION');
    }

    protected function buildWebResetUrl(string $email, string $token): string
    {
        $base = rtrim(config('app.url'), '/');

        return $base.'/reset-password?token='.urlencode($token).'&email='.urlencode($email);
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'password_reset',
            'message' => 'Permintaan reset password telah dikirim.',
        ];
    }
}