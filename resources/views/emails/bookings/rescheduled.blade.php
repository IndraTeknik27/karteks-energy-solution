@php
    /** @var \App\Models\ServiceBooking $booking */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-warning" style="font-size: 13px; padding: 6px 16px;">
            ⏰ Jadwal Berubah
        </span>
    </div>

    <p class="email-greeting">Halo {{ $booking->customer_name }},</p>
    <p class="email-message">
        Jadwal service booking Anda telah diubah. Mohon cek detail baru di bawah ini.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Perubahan',
        'rows' => [
            ['No. Booking', '<strong style="font-family: monospace;">' . $booking->booking_number . '</strong>'],
            ['Layanan', $booking->service?->name],
            ['Jadwal Baru', '<strong style="color: #047857;">' . $booking->scheduled_at->format('d F Y, H:i') . ' WIB</strong>'],
            ['Lokasi', strtoupper($booking->location_type)],
        ],
    ])

    @if($booking->notes)
        <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #92400e;">Alasan Perubahan</p>
            <p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.6; white-space: pre-line;">{{ $booking->notes }}</p>
        </div>
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Jika jadwal baru tidak sesuai, silakan hubungi tim KARTEKS untuk reschedule alternatif.
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/booking/' . $booking->id),
        'label' => 'Konfirmasi Jadwal Baru',
    ])
@endsection
