@php
    /** @var \App\Models\ServiceBooking $booking */
    $recipient = $recipient ?? 'customer';
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-danger" style="font-size: 13px; padding: 6px 16px;">
            ✗ Booking Dibatalkan
        </span>
    </div>

    <p class="email-greeting">Halo {{ $recipient === 'admin' ? 'Tim KARTEKS' : $booking->customer_name }},</p>
    <p class="email-message">
        {{ $recipient === 'admin' ? 'Customer telah membatalkan booking service berikut:' : 'Booking service Anda telah dibatalkan. Berikut detailnya:' }}
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Booking',
        'rows' => [
            ['No. Booking', '<strong style="font-family: monospace;">' . $booking->booking_number . '</strong>'],
            ['Layanan', $booking->service?->name],
            ['Jadwal Awal', $booking->scheduled_at->format('d F Y, H:i') . ' WIB'],
            ['Lokasi', strtoupper($booking->location_type)],
            ['Customer', $booking->customer_name . ' (' . $booking->customer_phone . ')'],
        ],
    ])

    @if($booking->cancellation_reason)
        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #991b1b;">Alasan Pembatalan</p>
            <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.6; white-space: pre-line;">{{ $booking->cancellation_reason }}</p>
        </div>
    @endif

    @if($recipient === 'admin')
        @include('emails.partials.button', [
            'url' => url('/admin/service-bookings/' . $booking->id),
            'label' => 'Lihat di Admin Panel',
        ])
    @else
        @include('emails.partials.button', [
            'url' => url('/services'),
            'label' => 'Booking Ulang',
            'secondary' => true,
        ])
    @endif
@endsection
