<?php

namespace App\Mail\Order;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPlacedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Order $order)
    {
        parent::__construct();
        $this->headerTitle = 'Pesanan Diterima';
        $this->headerSubtitle = $order->order_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Pesanan Baru #{$this->order->order_number} - KARTEKS ENERGY SOLUTION",
            ['order', 'placed', $this->order->order_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
            with: [
                'order' => $this->order,
                'paymentUrl' => $this->order->payment?->snap_token
                    ? url('/payment/orders/' . $this->order->order_number)
                    : url('/dashboard/orders/' . $this->order->order_number),
            ],
        );
    }
}