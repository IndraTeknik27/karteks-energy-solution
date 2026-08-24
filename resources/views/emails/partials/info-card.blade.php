{{--
    Reusable info card partial (key-value rows).
    Variables: $title (string), $rows (array of [label, value])
--}}
@if(! empty($rows))
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; margin: 16px 0;">
    @if($title ?? false)
        <tr>
            <td style="padding: 14px 20px 0 20px;">
                <p style="margin: 0; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #10b981;">
                    {{ $title }}
                </p>
            </td>
        </tr>
    @endif
    <tr>
        <td style="padding: 8px 20px 16px 20px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                @foreach($rows as $row)
                    <tr>
                        <td style="padding: 6px 0; font-size: 14px; color: #6b7280; width: 40%; vertical-align: top;">
                            {{ $row[0] }}
                        </td>
                        <td style="padding: 6px 0; font-size: 14px; color: #111827; font-weight: 600; text-align: right; vertical-align: top;">
                            {{ $row[1] }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
@endif