@php
    /** @var \App\Models\Quotation $quotation */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-primary" style="font-size: 13px; padding: 6px 16px;">
            📋 Quotation Baru
        </span>
    </div>

    <p class="email-greeting">Halo {{ $quotation->customer?->name ?? 'Customer' }},</p>
    <p class="email-message">
        Tim KARTEKS telah menyiapkan quotation untuk Anda. Silakan review detail di bawah ini dan konfirmasi sebelum tanggal berlaku.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Quotation',
        'rows' => [
            ['No. Quotation', '<strong style="font-family: monospace;">' . $quotation->quotation_number . '</strong>'],
            ['Judul', $quotation->title],
            ['Tanggal', $quotation->created_at->format('d F Y')],
            ['Berlaku Sampai', $quotation->valid_until?->format('d F Y') ?? '—'],
            ['Total', '<strong style="color: #047857; font-size: 16px;">' . $symbol . ' ' . number_format((float) $quotation->total, 0, ',', '.') . '</strong>'],
        ],
    ])

    @if($quotation->items && $quotation->items->count() > 0)
        <p class="email-message" style="margin-top: 24px;"><strong>Item Quotation:</strong></p>
        @include('emails.partials.order-items-table', [
            'items' => $quotation->items,
            'currency' => $symbol,
        ])
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Quotation ini <strong>berlaku sampai {{ $quotation->valid_until?->format('d F Y') ?? 'tanggal yang akan dikonfirmasi' }}</strong>.
        Silakan review dan konfirmasi melalui dashboard customer.
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/quotation/' . $quotation->id),
        'label' => 'Lihat Quotation Lengkap',
    ])

    @if($quotation->pdf_path)
        @include('emails.partials.button', [
            'url' => url('/dashboard/quotation/' . $quotation->id . '/pdf'),
            'label' => 'Download PDF',
            'secondary' => true,
        ])
    @endif
@endsection
