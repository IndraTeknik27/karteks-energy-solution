@php
    /** @var \App\Models\NewsletterSubscriber $subscriber */
    $unsubscribeUrl = url('/newsletter/unsubscribe/' . $subscriber->unsubscribe_token);
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-success" style="font-size: 13px; padding: 6px 16px;">
            ✓ Berhasil Berlangganan
        </span>
    </div>

    <p class="email-greeting">Halo {{ $subscriber->name ?: 'Teman KARTEKS' }},</p>
    <p class="email-message">
        Selamat! Email Anda telah terdaftar di <strong>Newsletter KARTEKS ENERGY SOLUTION</strong>.
        Mulai sekarang Anda akan menjadi yang pertama menerima:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 16px 0;">
        <tr>
            <td style="padding: 8px 0;">
                <span style="color: #10b981;">⚡</span>
                <strong style="margin-left: 8px;">Promo & diskon eksklusif</strong>
                <p style="margin: 4px 0 0 24px; font-size: 14px; color: #6b7280;">Penawaran khusus yang hanya untuk subscriber</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0;">
                <span style="color: #10b981;">🔋</span>
                <strong style="margin-left: 8px;">Produk baru & teknologi terkini</strong>
                <p style="margin: 4px 0 0 24px; font-size: 14px; color: #6b7280;">Update EV, solar, dan battery storage</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0;">
                <span style="color: #10b981;">📚</span>
                <strong style="margin-left: 8px;">Tips & edukasi</strong>
                <p style="margin: 4px 0 0 24px; font-size: 14px; color: #6b7280;">Artikel pilihan dari teknisi ahli kami</p>
            </td>
        </tr>
    </table>

    @include('emails.partials.button', [
        'url' => url('/products'),
        'label' => 'Jelajahi Produk Kami',
    ])

    <p class="email-message" style="margin-top: 24px; font-size: 13px; color: #6b7280;">
        Anda bisa berhenti berlangganan kapan saja melalui link di footer email ini.
    </p>
@endsection


@php
    // Override footer untuk show unsubscribe
@endphp
@php
    $showUnsubscribe = true;
    $unsubscribeUrl = url('/newsletter/unsubscribe/' . $subscriber->unsubscribe_token);
@endphp