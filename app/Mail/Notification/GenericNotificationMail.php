<?php

namespace App\Mail\Notification;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class GenericNotificationMail extends AbstractTransactionalMail
{
    public function __construct(
        public \App\Models\User $user,
        public array $data = [],
    ) {
        parent::__construct();
        $this->headerTitle = 'Notifikasi KARTEKS';
        $this->headerSubtitle = $data['type'] ?? 'Update';
    }

    public function envelope(): Envelope
    {
        $subject = $this->data['title'] ?? 'Notifikasi Baru dari KARTEKS';
        return $this->buildEnvelope($subject, ['notification', 'generic']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification.generic',
            with: [
                'user' => $this->user,
                'data' => $this->data,
            ],
        );
    }
}