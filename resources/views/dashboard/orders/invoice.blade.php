@extends('layouts.app')

@section('title', 'Invoice ' . $order->order_number . ' - KARTEKS')

@push('styles')
<style>
@media print {
    body { background: white !important; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .container { max-width: 800px !important; }
}
</style>
@endpush

@section('content')
    <section class="bg-white border-b border-gray-100 no-print">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.orders') }}" class="hover:text-brand-600">Pesanan</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="hover:text-brand-600">{{ $order->order_number }}</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Invoice</span>
            </nav>
            <button onclick="window.print()" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-full transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
                Cetak Invoice
            </button>
        </div>
    </section>

    <section class="py-8 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">

            {{-- Invoice Header --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-8 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-8 h-8 text-brand-600" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 2L4 18h9l-1.5 12L28 14h-9L21 2z" fill="currentColor"/>
                            </svg>
                            <span class="text-lg font-bold text-gray-900">KARTEKS</span>
                        </div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Energy Solution</div>
                        <div class="text-xs text-gray-500 mt-2 leading-relaxed">
                            Jln. Bonto Marannu, Perumahan Tanjung Kencana Residence No. 8,<br>
                            Kabupaten Gowa, Sulawesi Selatan, Indonesia<br>
                            +62 815 4532 6426 · karteksenergy27@gmail.com
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Invoice</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $order->created_at?->format('d F Y') }}</div>
                        <span class="inline-block mt-2 px-3 py-1 text-xs rounded-full font-bold
                            {{ $order->is_cancelled ? 'bg-red-100 text-red-700' :
                               ($order->is_completed ? 'bg-brand-100 text-brand-700' :
                               ($order->is_paid ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700')) }}">
                            {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Bill To --}}
            @if($order->customer)
                <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-3">Bill To</div>
                    <div class="text-sm text-gray-900 font-semibold">{{ $order->customer->name }}</div>
                    <div class="text-sm text-gray-600">{{ $order->customer->email }}</div>
                    @if($order->customer->phone)
                        <div class="text-sm text-gray-600">{{ $order->customer->phone }}</div>
                    @endif
                </div>
            @endif

            {{-- Items Table --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wide">Produk</th>
                            <th class="text-center px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Qty</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wide">Harga</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wide">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                            @php
                                $showImageUrl = $item->image
                                    ?: (method_exists($item->itemable, 'getFeaturedImageUrl') ? $item->itemable->featuredImageUrl : null)
                                    ?: (method_exists($item->itemable, 'getFirstMediaUrl')
                                        ? ($item->itemable->getFirstMediaUrl('gallery') ?: $item->itemable->getFirstMediaUrl('featured'))
                                        : null);
                            @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($showImageUrl)
                                            <img src="{{ $showImageUrl }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover bg-gray-50">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                            @if($item->sku)
                                                <div class="text-xs text-gray-400">SKU: {{ $item->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center text-gray-600 hidden sm:table-cell">{{ $item->qty }}</td>
                                <td class="px-6 py-4 text-right text-gray-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Summary --}}
                <div class="border-t border-gray-100 px-6 py-5 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">Subtotal</dt>
                        <dd class="text-gray-900 font-medium">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</dd>
                    </div>
                    @if($order->coupon_discount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-brand-600">Diskon ({{ $order->coupon_code }})</dt>
                            <dd class="text-brand-600 font-medium">− Rp {{ number_format((float) $order->coupon_discount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">
                            Pengiriman
                            @if($order->shipping_courier)
                                <span class="text-gray-400">({{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }})</span>
                            @endif
                        </dt>
                        <dd class="text-gray-900 font-medium">
                            @if($order->shipping_cost > 0)
                                Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}
                            @else
                                <span class="text-brand-600">Gratis</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2.5 mt-2">
                        <dt class="font-bold text-gray-900 text-base">Total</dt>
                        <dd class="font-bold text-brand-700 text-xl">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd>
                    </div>
                </div>
            </div>

            {{-- Shipping & Payment --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                @if($order->shipping_address)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-3">Alamat Pengiriman</div>
                        <div class="text-sm text-gray-700 space-y-1">
                            <div class="font-semibold text-gray-900">{{ $order->shipping_address['recipient'] ?? '-' }}</div>
                            <div>{{ $order->shipping_address['phone'] ?? '-' }}</div>
                            <div class="text-gray-500 leading-relaxed">
                                {{ $order->shipping_address['address_line_1'] ?? '' }}
                                @if(!empty($order->shipping_address['address_line_2']))
                                    , {{ $order->shipping_address['address_line_2'] }}
                                @endif
                            </div>
                            <div class="text-gray-500">
                                {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['province'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-3">Informasi Pembayaran</div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Metode</dt>
                            <dd class="font-semibold text-gray-900">{{ strtoupper(str_replace('_', ' ', $order->payment_method ?? '-')) }}</dd>
                        </div>
                        @if($order->shipping_tracking_number)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">No. Resi</dt>
                                <dd class="font-mono text-xs text-gray-700">{{ $order->shipping_tracking_number }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Status</dt>
                            <dd>
                                @if($order->paid_at)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 font-bold">LUNAS</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold">BELUM BAYAR</span>
                                @endif
                            </dd>
                        </div>
                        @if($order->paid_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Tgl. Bayar</dt>
                                <dd class="text-gray-700">{{ $order->paid_at?->format('d M Y, H:i') }} WIB</dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Footer note --}}
            <div class="text-center text-xs text-gray-400 py-4 no-print">
                Invoice ini dibuat secara otomatis oleh sistem KARTEKS Energy Solution.<br>
                Untuk pertanyaan, hubungi kami di +62 815 4532 6426 atau karteksenergy27@gmail.com
            </div>

            <div class="text-center mt-4 no-print">
                <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-brand-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                    Kembali ke Detail Pesanan
                </a>
            </div>
        </div>
    </section>
@endsection
