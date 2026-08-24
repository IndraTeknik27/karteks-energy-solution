@php
    /** @var \App\Models\ServiceBooking $booking */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-success" style="font-size: 13px; padding: 6px 16px;">
            ✓ Booking Dikonfirmasi
        </span>
    </div>

    <p class="email-greeting">Halo {{ $booking->customer_name }},</p>
    <p class="email-message">
        Booking service Anda telah dikonfirmasi oleh tim KARTEKS. Berikut detail jadwal Anda:
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Booking',
        'rows' => [
            ['No. Booking', '<strong style="font-family: monospace;">' . $booking->booking_number . '</strong>'],
            ['Layanan', $booking->service?->name],
            ['Jadwal', '<strong style="color: #047857;">' . $booking->scheduled_at->format('d F Y, H:i') . ' WIB</strong>'],
            ['Durasi', $booking->service?->duration_minutes . ' menit'],
            ['Teknisi', $booking->technician?->name ?? 'Akan dikonfirmasi H-1'],
            ['Lokasi', strtoupper($booking->location_type)],
        ],
    ])

    @if($booking->location_type === 'onsite' && $booking->address)
        <div style="background-color: #f9fafb; padding: 12px 16px; margin: 16px 0; border-radius: 8px;">
            <p style="margin: 0; font-size: 14px; color: #374151;">
                📍 {{ $booking->address }}
            </p>
        </div>
    @endif

    @if($booking->notes)
        <div style="background-color: #f9fafb; border-left: 4px solid #10b981; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #047857;">Catatan Tim</p>
            <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-line;">{{ $booking->notes }}</p>
        </div>
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Tim teknisi akan siap sedia pada jadwal yang ditentukan. Jika ada perubahan, silakan hubungi kami minimal 24 jam sebelumnya.
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/booking/' . $booking->id),
        'label' => 'Lihat Booking',
    ])
@endsection
