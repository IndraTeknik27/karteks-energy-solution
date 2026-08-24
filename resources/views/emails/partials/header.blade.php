{{--
    Email Header partial - used by all transactional emails.
    Variables: $title, $subtitle
--}}
<tr>
    <td class="email-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 32px; text-align: center; color: #ffffff;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
            {{ config('karteks.company.name', 'KARTEKS ENERGY SOLUTION') }}
        </h1>
        @if($title ?? false)
            <p class="subtitle" style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.95;">
                {{ $title }}
            </p>
        @endif
        @if($subtitle ?? false)
            <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.85;">
                {{ $subtitle }}
            </p>
        @endif
    </td>
</tr>