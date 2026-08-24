{{--
    Reusable CTA button partial.
    Variables: $url, $label, $secondary (bool)
--}}
@php
    $secondaryClass = ($secondary ?? false) ? 'email-button email-button-secondary' : 'email-button';
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td align="center" style="padding: 24px 0;">
            <a href="{{ $url }}" class="{{ $secondaryClass }}" style="display: inline-block; background: {{ ($secondary ?? false) ? 'transparent' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)' }}; color: {{ ($secondary ?? false) ? '#10b981 !important' : '#ffffff !important' }}; padding: 14px 32px; border-radius: 9999px; font-size: 15px; font-weight: 600; text-decoration: none; {{ ($secondary ?? false) ? 'border: 2px solid #10b981;' : 'box-shadow: 0 4px 12px rgba(16,185,129,0.3);' }}">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>