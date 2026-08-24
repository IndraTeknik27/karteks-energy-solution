@php
    /** @var \App\Models\Order $order */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-success" style="font-size: 13px; padding: 6px 16px;">
            ✓ Pembayaran Berhasil
        </span>
    </div>

    <p class="email-greeting">Halo {{ $order->customer_name ?? 'Customer' }},</p>
    <p class="email-message">
        Pembayaran untuk pesanan Anda telah kami terima. Tim kami akan segera memproses dan menyiapkan pesanan Anda.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Pembayaran',
        'rows' => [
            ['No. Pesanan', $order->order_number],
            ['Tanggal Bayar', $order->paid_at?->format('d F Y, H:i') . ' WIB' ?? now()->format('d F Y, H:i') . ' WIB'],
            ['Metode', str_replace('_', ' ', ucfirst($order->payment_method ?? 'Midtrans'))],
            ['Status', '<span class="email-badge email-badge-success">' . str_replace('_', ' ', ucfirst($order->status)) . '</span>'],
            ['Total Dibayar', '<strong style="color: #10b981; font-size: 16px;">' . $symbol . ' ' . number_format((float) $order->total, 0, ',', '.') . '</strong>'],
        ],
    ])

    <p class="email-message" style="margin-top: 24px;">
        Anda akan menerima email terpisah dengan nomor resi setelah pesanan Anda dikirim. Terima kasih telah berbelanja di KARTEKS!
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/orders/' . $order->order_number),
        'label' => 'Lihat Detail Pesanan',
    ])
@endsection
