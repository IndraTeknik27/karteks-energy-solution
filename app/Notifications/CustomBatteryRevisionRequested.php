<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryRevisionRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $requestNumber,
        public int $revisionNumber,
        public string $adminNote,
        public ?array $fieldChanges = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Revisi Diperlukan - {$this->requestNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Tim KARTEKS meminta revisi pada permintaan custom battery Anda.")
            ->line("Nomor Request: {$this->requestNumber}")
            ->line("Revisi ke: #{$this->revisionNumber}")
            ->line("Catatan admin: {$this->adminNote}");

        if (! empty($this->fieldChanges)) {
            $mail->line('Field yang perlu diubah:');
            foreach ($this->fieldChanges as $field => $expected) {
                $label = ucfirst(str_replace('_', ' ', $field));
                $mail->line("- {$label}: {$expected}");
            }
        }

        return $mail
            ->action('Lihat & Revisi', url('/dashboard/custom-battery/'.$this->requestNumber))
            ->line('Mohon segera ditanggapi agar permintaan dapat diproses.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_revision_requested',
            'request_number' => $this->requestNumber,
            'revision_number' => $this->revisionNumber,
            'admin_note' => $this->adminNote,
            'field_changes' => $this->fieldChanges,
        ];
    }
}