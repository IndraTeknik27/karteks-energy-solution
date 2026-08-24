<?php

namespace App\Mail\Newsletter;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\NewsletterSubscriber $subscriber)
    {
        parent::__construct();
        $this->headerTitle = 'Selamat Datang';
        $this->headerSubtitle = 'Newsletter KARTEKS';
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            'Selamat Datang di Newsletter KARTEKS',
            ['newsletter', 'welcome'],
        );
    }

    public function content(): Content
    {
        $token = $this->subscriber->unsubscribe_token ?? \Illuminate\Support\Str::uuid()->toString();
        if (! $this->subscriber->unsubscribe_token) {
            $this->subscriber->update(['unsubscribe_token' => $token]);
        }
        $this->unsubscribeUrl = url('/newsletter/unsubscribe/' . $token);

        return new Content(
            view: 'emails.newsletter.welcome',
            with: [
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'showUnsubscribe' => true,
            ],
        );
    }
}