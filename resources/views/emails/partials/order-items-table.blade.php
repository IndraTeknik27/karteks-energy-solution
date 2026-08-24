{{--
    Reusable order items table partial.
    Variables: $items (Collection of OrderItem with name, qty, price, subtotal)
               $currency (default 'Rp')
--}}
@if(! empty($items) && $items->count() > 0)
<table role="presentation" class="email-table" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; border-collapse: collapse; margin: 16px 0;">
    <thead>
        <tr>
            <th style="background-color: #f9fafb; padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb;">Item</th>
            <th style="background-color: #f9fafb; padding: 10px 12px; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; width: 60px;">Qty</th>
            <th style="background-color: #f9fafb; padding: 10px 12px; text-align: right; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; width: 100px;">Harga</th>
            <th style="background-color: #f9fafb; padding: 10px 12px; text-align: right; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; width: 110px;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td style="padding: 12px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6;">
                    <strong style="color: #111827;">{{ $item->name ?? $item->itemable?->name ?? 'Item' }}</strong>
                    @if($item->sku ?? false)
                        <br><span style="font-size: 11px; color: #9ca3af; font-family: monospace;">SKU: {{ $item->sku }}</span>
                    @endif
                </td>
                <td style="padding: 12px; font-size: 14px; color: #374151; text-align: center; border-bottom: 1px solid #f3f4f6;">{{ $item->qty }}</td>
                <td style="padding: 12px; font-size: 14px; color: #374151; text-align: right; border-bottom: 1px solid #f3f4f6;">{{ ($currency ?? 'Rp') . ' ' . number_format((float) ($item->price ?? 0), 0, ',', '.') }}</td>
                <td style="padding: 12px; font-size: 14px; color: #111827; text-align: right; font-weight: 600; border-bottom: 1px solid #f3f4f6;">{{ ($currency ?? 'Rp') . ' ' . number_format((float) ($item->subtotal ?? ($item->price ?? 0) * $item->qty), 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif