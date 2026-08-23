<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $requestNumber,
        public string $previousStatus,
        public string $newStatus,
        public ?string $adminNote = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->newStatus));

        $mail = (new MailMessage)
            ->subject("Update Permintaan Custom Battery {$this->requestNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Status permintaan custom battery Anda telah diperbarui.")
            ->line("Nomor Request: {$this->requestNumber}")
            ->line("Status: ".ucfirst(str_replace('_', ' ', $this->previousStatus))." → {$statusLabel}");

        if ($this->adminNote) {
            $mail->line("Catatan tim KARTEKS: {$this->adminNote}");
        }

        return $mail
            ->action('Lihat Detail', url('/dashboard/custom-battery/'.$this->requestNumber))
            ->line('Terima kasih telah memilih KARTEKS ENERGY SOLUTION.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_status_changed',
            'request_number' => $this->requestNumber,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'admin_note' => $this->adminNote,
        ];
    }
}