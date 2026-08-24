<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>{{ $title ?? config('karteks.company.name') }}</title>

    <style type="text/css">
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        a { color: inherit; text-decoration: none; }

        /* KARTEKS brand tokens */
        :root {
            --brand-primary: #10b981;
            --brand-primary-dark: #059669;
            --brand-primary-light: #d1fae5;
            --brand-secondary: #0ea5e9;
            --accent: #f59e0b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        /* Email body */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            -webkit-font-smoothing: antialiased;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 32px 16px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 32px 32px;
            text-align: center;
            color: #ffffff;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .email-header .subtitle {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.95;
        }

        .email-content {
            padding: 32px;
        }

        .email-greeting {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 16px 0;
        }

        .email-message {
            font-size: 15px;
            line-height: 1.6;
            color: #374151;
            margin: 0 0 24px 0;
        }

        .email-message p {
            margin: 0 0 12px 0;
        }

        .email-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin: 16px 0;
        }

        .email-card-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #10b981;
;
            margin: 0 0 8px 0;
        }

        .email-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 14px;
            color: #374151;
        }

        .email-card-row .label {
            color: #6b7280;
        }

        .email-card-row .value {
            font-weight: 600;
            color: #111827;
            text-align: right;
        }

        .email-button-wrapper {
            text-align: center;
            margin: 28px 0;
        }

        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .email-button:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .email-button-secondary {
            background: transparent;
            color: #10b981 !important;
            border: 2px solid #10b981;
            box-shadow: none;
        }

        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }

        .email-table th {
            background-color: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }

        .email-table td {
            padding: 12px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .email-table tfoot td {
            font-weight: 700;
            color: #111827;
            border-top: 2px solid #10b981;
;
            border-bottom: none;
            background-color: #ecfdf5;
        }

        .email-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .email-badge-success { background-color: #d1fae5; color: #065f46; }
        .email-badge-warning { background-color: #fef3c7; color: #92400e; }
        .email-badge-danger  { background-color: #fee2e2; color: #991b1b; }
        .email-badge-info    { background-color: #dbeafe; color: #1e40af; }
        .email-badge-primary { background-color: #d1fae5; color: #047857; }

        .email-divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        .email-footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }

        .email-footer p {
            margin: 4px 0;
        }

        .email-footer a {
            color: #10b981;
;
            text-decoration: none;
        }

        .email-footer-brand {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 12px;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content { padding: 24px 20px !important; }
            .email-header { padding: 24px 20px !important; }
            .email-card-row { flex-direction: column; align-items: flex-start; gap: 4px; }
            .email-table th, .email-table td { padding: 8px 6px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center">
                <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                    {{-- Header --}}
                    @include('emails.partials.header', [
                        'title' => $headerTitle ?? null,
                        'subtitle' => $headerSubtitle ?? null,
                    ])

                    {{-- Main content --}}
                    <tr>
                        <td class="email-content" style="padding: 32px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    @include('emails.partials.footer')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>