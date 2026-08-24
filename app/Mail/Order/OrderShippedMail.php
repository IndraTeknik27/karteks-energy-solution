<?php

namespace App\Mail\Order;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderShippedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Order $order)
    {
        parent::__construct();
        $this->headerTitle = 'Pesanan Dikirim';
        $this->headerSubtitle = $order->order_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Pesanan Anda Sedang Dikirim - #{$this->order->order_number}",
            ['order', 'shipped', $this->order->order_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.shipped',
            with: [
                'order' => $this->order->load([
                    'shipment.trackingHistories' => fn ($q) => $q->orderByDesc('occurred_at'),
                ]),
            ],
        );
    }
}