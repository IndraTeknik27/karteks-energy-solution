<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 30px 40px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #059669;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-left { width: 60%; }
        .header-right { width: 40%; text-align: right; }
        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #059669;
            margin: 0 0 4px 0;
        }
        .company-tagline { font-size: 9pt; color: #6b7280; margin: 0 0 8px 0; }
        .company-info { font-size: 9pt; color: #6b7280; line-height: 1.4; }
        .quotation-title {
            font-size: 24pt;
            font-weight: bold;
            color: #059669;
            margin: 0 0 8px 0;
        }
        .quotation-number { font-size: 11pt; color: #6b7280; margin: 0; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-viewed { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-expired { background: #f3f4f6; color: #4b5563; }

        .info-block {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .info-left, .info-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .info-label {
            font-size: 9pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .info-content { font-size: 11pt; line-height: 1.5; }
        .info-content strong { display: block; }

        .description {
            background: #f9fafb;
            padding: 12px;
            border-left: 3px solid #059669;
            margin-bottom: 25px;
            font-size: 10pt;
            line-height: 1.5;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th {
            background: #059669;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }
        table.items th.text-right { text-align: right; }
        table.items th.text-center { text-align: center; }
        table.items td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }
        table.items td.text-right { text-align: right; }
        table.items td.text-center { text-align: center; }
        table.items tr:nth-child(even) td { background: #f9fafb; }

        .totals {
            width: 350px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .totals table { width: 100%; }
        .totals td { padding: 6px 0; font-size: 11pt; }
        .totals td.label { color: #6b7280; }
        .totals td.value { text-align: right; font-weight: 600; }
        .totals tr.grand-total td {
            border-top: 2px solid #059669;
            padding-top: 10px;
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
        }

        .terms {
            background: #f9fafb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .terms-title {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }
        .terms-content { font-size: 9.5pt; line-height: 1.6; white-space: pre-line; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
        }

        .validity-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    @php
        $company = config('karteks.company');
    @endphp

    <div class="header">
        <div class="header-left">
            <h1 class="company-name">{{ $company['name'] }}</h1>
            <p class="company-tagline">{{ $company['tagline'] }}</p>
            <div class="company-info">
                {{ $company['address'] }}<br>
                Telp: {{ $company['phone'] }} • Email: {{ $company['email'] }}<br>
                {{ $company['website'] }}
            </div>
        </div>
        <div class="header-right">
            <h1 class="quotation-title">QUOTATION</h1>
            <p class="quotation-number">No: <strong>{{ $quotation->quotation_number }}</strong></p>
            <p class="quotation-number">Tanggal: {{ $quotation->created_at?->format('d F Y') }}</p>
            <span class="status-badge status-{{ $quotation->status }}">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
        </div>
    </div>

    <div class="info-block">
        <div class="info-left">
            <div class="info-label">Kepada Yth.</div>
            <div class="info-content">
                <strong>{{ $quotation->customer?->name ?? '-' }}</strong>
                {{ $quotation->customer?->email ?? '' }}<br>
                {{ $quotation->customer?->phone ?? '' }}
            </div>
        </div>
        <div class="info-right">
            <div class="info-label">Detail</div>
            <div class="info-content">
                @if($quotation->quotable_type === 'App\\Models\\CustomBatteryRequest')
                    Referensi: <strong>{{ $quotation->quotable?->request_number ?? '-' }}</strong><br>
                    (Custom Battery Request)
                @endif
                @if($quotation->creator)
                    Disusun oleh: {{ $quotation->creator->name }}
                @endif
            </div>
        </div>
    </div>

    <h2 style="font-size: 14pt; color: #1f2937; margin: 0 0 10px 0; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;">{{ $quotation->title }}</h2>

    @if($quotation->description)
        <div class="description">{{ $quotation->description }}</div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 50%;">Item</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 17%;" class="text-right">Harga Satuan</th>
                <th style="width: 18%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if($item->description)
                            <br><span style="color: #6b7280; font-size: 9pt;">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($quotation->discount > 0)
                <tr>
                    <td class="label">Diskon</td>
                    <td class="value">− Rp {{ number_format($quotation->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($quotation->tax > 0)
                <tr>
                    <td class="label">PPN</td>
                    <td class="value">Rp {{ number_format($quotation->tax, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td class="label">TOTAL</td>
                <td class="value">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($quotation->valid_until)
        <div class="validity-box">
            <strong>Berlaku sampai: {{ $quotation->valid_until->format('d F Y') }}</strong>
            @if($quotation->is_expired)
                <br><span style="color: #991b1b;">(QUOTATION SUDAH KADALUARSA)</span>
            @endif
        </div>
    @endif

    @if($quotation->terms_conditions)
        <div class="terms">
            <h3 class="terms-title">Syarat & Ketentuan</h3>
            <div class="terms-content">{{ $quotation->terms_conditions }}</div>
        </div>
    @endif

    @if($quotation->notes)
        <div style="margin-bottom: 20px; font-size: 10pt;">
            <strong>Catatan:</strong> {{ $quotation->notes }}
        </div>
    @endif

    @if(in_array($quotation->status, ['accepted', 'rejected']))
        <div style="margin-top: 30px; padding: 15px; border: 2px solid {{ $quotation->status === 'accepted' ? '#059669' : '#dc2626' }}; border-radius: 4px; text-align: center;">
            <div style="font-size: 14pt; font-weight: bold; color: {{ $quotation->status === 'accepted' ? '#059669' : '#dc2626' }};">
                QUOTATION {{ strtoupper($quotation->status) }}
            </div>
            <div style="font-size: 9pt; color: #6b7280; margin-top: 4px;">
                pada {{ $quotation->accepted_at?->format('d F Y H:i') ?? $quotation->rejected_at?->format('d F Y H:i') }}
                @if($quotation->rejection_reason)
                    <br>Alasan: {{ $quotation->rejection_reason }}
                @endif
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Quotation ini dihasilkan oleh sistem dan sah tanpa tanda tangan.</p>
        <p>{{ $company['name'] }} • {{ $company['website'] }} • {{ $company['phone'] }}</p>
    </div>
</body>
</html>