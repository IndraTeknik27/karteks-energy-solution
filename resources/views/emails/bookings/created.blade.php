@php
    /** @var \App\Models\ServiceBooking $booking */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-info" style="font-size: 13px; padding: 6px 16px;">
            📅 Booking Baru
        </span>
    </div>

    <p class="email-greeting">Halo Tim KARTEKS,</p>
    <p class="email-message">
        Ada service booking baru masuk yang menunggu konfirmasi.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Booking',
        'rows' => [
            ['No. Booking', '<strong style="font-family: monospace;">' . $booking->booking_number . '</strong>'],
            ['Customer', $booking->customer_name . '<br><span style="color: #9ca3af; font-size: 12px;">' . $booking->customer_phone . '</span>'],
            ['Layanan', $booking->service?->name],
            ['Jadwal', '<strong>' . $booking->scheduled_at->format('d F Y, H:i') . ' WIB</strong>'],
            ['Durasi', $booking->service?->duration_minutes . ' menit'],
            ['Lokasi', strtoupper($booking->location_type)],
        ],
    ])

    @if($booking->location_type === 'onsite' && $booking->address)
        <div style="background-color: #f9fafb; padding: 12px 16px; margin: 16px 0; border-radius: 8px;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #6b7280;">Alamat Onsite</p>
            <p style="margin: 0; font-size: 14px; color: #374151;">{{ $booking->address }}</p>
        </div>
    @endif

    @include('emails.partials.button', [
        'url' => url('/admin/service-bookings/' . $booking->id),
        'label' => 'Konfirmasi di Admin Panel',
    ])
@endsection
