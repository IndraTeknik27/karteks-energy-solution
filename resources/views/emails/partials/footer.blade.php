{{--
    Email Footer partial - used by all transactional emails.
    Variables: $showUnsubscribe (bool), $unsubscribeUrl (string)
--}}
<tr>
    <td class="email-footer" style="background-color: #f9fafb; padding: 24px 32px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb;">
        <p style="margin: 0 0 8px 0;">
            <strong style="color: #10b981;">{{ config('karteks.company.name', 'KARTEKS ENERGY SOLUTION') }}</strong>
        </p>
        <p style="margin: 4px 0;">
            {{ config('karteks.company.address', 'Gowa, Sulawesi Selatan') }}
        </p>
        <p style="margin: 4px 0;">
            <a href="tel:{{ config('karteks.company.phone') }}" style="color: #10b981; text-decoration: none;">{{ config('karteks.company.phone') }}</a>
            &nbsp;·&nbsp;
            <a href="mailto:{{ config('karteks.company.email') }}" style="color: #10b981; text-decoration: none;">{{ config('karteks.company.email') }}</a>
        </p>

        @if($showUnsubscribe ?? false)
            <p style="margin: 12px 0 0 0;">
                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #6b7280; text-decoration: underline; font-size: 12px;">
                    Unsubscribe dari email ini
                </a>
            </p>
        @endif

        <p class="email-footer-brand" style="font-size: 12px; color: #9ca3af; margin-top: 12px;">
            Dikirim oleh {{ config('karteks.company.name') }} &middot;
            <a href="{{ url('/') }}" style="color: #10b981;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
        </p>
    </td>
</tr>