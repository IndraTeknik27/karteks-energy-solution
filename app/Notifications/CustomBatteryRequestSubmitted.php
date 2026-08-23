<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $requestNumber,
        public string $customerName,
        public string $chemistry,
        public string $voltage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Permintaan Custom Battery Baru - {$this->requestNumber}")
            ->greeting("Halo Tim KARTEKS,")
            ->line("Customer {$this->customerName} baru saja mengajukan permintaan custom battery baru.")
            ->line("Nomor Request: {$this->requestNumber}")
            ->line("Kimia Baterai: {$this->chemistry}")
            ->line("Voltase: {$this->voltage}")
            ->action('Tinjau di Admin Panel', url('/admin/custom-battery-requests/'.$this->requestNumber))
            ->line('Mohon ditinjau secepatnya.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_submitted',
            'request_number' => $this->requestNumber,
            'customer_name' => $this->customerName,
            'chemistry' => $this->chemistry,
            'voltage' => $this->voltage,
        ];
    }
}