@php
    /** @var \App\Models\Order $order */
    $currency = config('karteks.locale.currency', 'IDR');
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <p class="email-greeting">Halo {{ $order->customer_name ?? 'Customer' }},</p>
    <p class="email-message">
        Terima kasih telah berbelanja di <strong>{{ config('karteks.company.name') }}</strong>. Pesanan Anda telah kami terima dan sedang menunggu pembayaran.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Pesanan',
        'rows' => [
            ['No. Pesanan', $order->order_number],
            ['Tanggal', $order->created_at->format('d F Y, H:i') . ' WIB'],
            ['Status', '<span class="email-badge email-badge-warning">' . str_replace('_', ' ', ucfirst($order->status)) . '</span>'],
            ['Metode Pembayaran', strtoupper($order->payment_method ?? 'Midtrans')],
            ['Total Pembayaran', '<strong style="color: #10b981; font-size: 16px;">' . $symbol . ' ' . number_format((float) $order->total, 0, ',', '.') . '</strong>'],
        ],
    ])

    @if($order->items && $order->items->count() > 0)
        <p class="email-message" style="margin-top: 24px;"><strong>Item Pesanan:</strong></p>
        @include('emails.partials.order-items-table', [
            'items' => $order->items,
            'currency' => $symbol,
        ])

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #374151;">Subtotal</td>
                <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #374151; width: 110px;">{{ $symbol }} {{ number_format((float) ($order->subtotal ?? $order->total), 0, ',', '.') }}</td>
            </tr>
            @if(($order->discount ?? 0) > 0)
                <tr>
                    <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #ef4444;">Diskon</td>
                    <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #ef4444; width: 110px;">- {{ $symbol }} {{ number_format((float) $order->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if(($order->shipping_cost ?? 0) > 0)
                <tr>
                    <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #374151;">Ongkos Kirim</td>
                    <td style="text-align: right; padding: 8px 12px; font-size: 14px; color: #374151; width: 110px;">{{ $symbol }} {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td style="text-align: right; padding: 12px; font-size: 16px; color: #111827; font-weight: 700; border-top: 2px solid #10b981;">Total</td>
                <td style="text-align: right; padding: 12px; font-size: 16px; color: #047857; font-weight: 700; border-top: 2px solid #10b981; width: 110px; background-color: #ecfdf5;">{{ $symbol }} {{ number_format((float) $order->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    @endif

    @if($order->shippingAddress ?? false)
        @include('emails.partials.info-card', [
            'title' => 'Alamat Pengiriman',
            'rows' => [
                ['Penerima', $order->shippingAddress->recipient_name ?? $order->customer_name],
                ['Alamat', $order->shippingAddress->full_address ?? ''],
                ['Telepon', $order->shippingAddress->phone ?? ''],
            ],
        ])
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Silakan selesaikan pembayaran Anda untuk memproses pesanan. Pesanan akan otomatis kadaluarsa jika tidak dibayar dalam
        <strong>{{ config('karteks.ecommerce.order_expiry_hours', 24) }} jam</strong>.
    </p>

    @include('emails.partials.button', [
        'url' => $paymentUrl ?? url('/dashboard/orders/' . $order->order_number),
        'label' => 'Bayar Sekarang',
    ])

    <p class="email-message" style="margin-top: 24px; font-size: 13px; color: #6b7280;">
        Jika Anda memiliki pertanyaan, silakan hubungi kami di
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('karteks.company.whatsapp')) }}" style="color: #10b981; text-decoration: none;">WhatsApp</a>
        atau balas email ini.
    </p>
@endsection
