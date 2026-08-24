@php
    /** @var \App\Models\ContactMessage $message */
@endphp

@extends('emails.layout')

@section('content')
    <p class="email-greeting">Halo {{ $contactMessage->name }},</p>
    <p class="email-message">
        Terima kasih telah menghubungi <strong>{{ config('karteks.company.name') }}</strong>.
        Kami telah menerima pesan Anda dan tim customer service kami akan menanggapi dalam
        <strong>1×24 jam kerja</strong>.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Pesan Anda',
        'rows' => [
            ['Subjek', $contactMessage->subject],
            ['Diterima', $contactMessage->created_at->format('d F Y, H:i') . ' WIB'],
            ['No. Referensi', '#' . str_pad($contactMessage->id, 6, '0', STR_PAD_LEFT)],
        ],
    ])

    <div style="background-color: #f9fafb; border-left: 4px solid #10b981; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-line;">{{ $contactMessage->message }}</p>
    </div>

    <p class="email-message" style="margin-top: 24px;">
        Untuk pertanyaan mendesak, silakan hubungi WhatsApp kami:
    </p>

    @include('emails.partials.button', [
        'url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('karteks.company.whatsapp')),
        'label' => 'Chat WhatsApp',
        'secondary' => true,
    ])
@endsection
