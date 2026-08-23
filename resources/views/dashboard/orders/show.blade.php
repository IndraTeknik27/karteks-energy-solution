@extends('layouts.app')

@section('title', 'Order ' . $order->order_number . ' - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.orders') }}" class="hover:text-brand-600">Pesanan</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">{{ $order->order_number }}</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Order Number</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $order->created_at?->format('d F Y, H:i') }} WIB</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-4 py-2 text-sm rounded-full font-bold
                            {{ $order->is_cancelled ? 'bg-red-100 text-red-700' :
                               ($order->is_completed ? 'bg-brand-100 text-brand-700' :
                               ($order->is_paid ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700')) }}">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                        @if($order->expires_at && $order->is_pending_payment && $order->expires_at->isFuture())
                            <div class="text-xs text-gray-500 mt-2">Bayar sebelum: {{ $order->expires_at->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Items --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Item Pesanan</h2>
                        <div class="divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <div class="flex gap-4 py-3 first:pt-0 last:pb-0">
                                    <div class="w-16 h-16 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                                        @if($item->image)
                                            <img src="{{ $item->image }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 line-clamp-2">{{ $item->name }}</div>
                                        @if($item->sku)
                                            <div class="text-xs text-gray-500 mt-0.5">SKU: {{ $item->sku }}</div>
                                        @endif
                                        <div class="text-sm text-gray-600 mt-1">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="font-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                            @if($order->coupon_discount > 0)
                                <div class="flex justify-between text-brand-700"><dt>Diskon ({{ $order->coupon_code }})</dt><dd>− Rp {{ number_format($order->coupon_discount, 0, ',', '.') }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-gray-600">Pengiriman</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-2 mt-2"><dt class="font-bold text-gray-900">Total</dt><dd class="font-bold text-xl text-brand-700">Rp {{ number_format($order->total, 0, ',', '.') }}</dd></div>
                        </div>
                    </div>

                    {{-- Status history --}}
                    @if($order->statusHistories->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Riwayat Status</h2>
                            <div class="space-y-3">
                                @foreach($order->statusHistories->sortBy('created_at') as $h)
                                    <div class="flex gap-3">
                                        <div class="w-2 h-2 bg-brand-500 rounded-full mt-2 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $h->to_status)) }}</div>
                                            <div class="text-xs text-gray-500">{{ $h->created_at?->format('d M Y H:i') }} • {{ ucfirst($h->changed_by_role ?? 'system') }}</div>
                                            @if($h->note)
                                                <div class="text-sm text-gray-600 mt-1">{{ $h->note }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    @php
                        $canCancel = in_array($order->status, ['pending_payment', 'payment_pending', 'paid', 'processing']);
                        $canReview = in_array($order->status, ['delivered', 'completed']);
                    @endphp
                    @if($canCancel)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-3">Batalkan Pesanan</h2>
                            <form method="POST" action="{{ route('dashboard.orders.cancel', $order->order_number) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.')">
                                @csrf
                                <textarea name="reason" required minlength="5" placeholder="Alasan pembatalan (min. 5 karakter)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 mb-3"></textarea>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">Batalkan Pesanan</button>
                            </form>
                        </div>
                    @endif

                    @if($canReview)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-3">Beri Ulasan Produk</h2>
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    <a href="{{ route('dashboard.review.create', $item->itemable) }}" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-brand-500 transition">
                                        <span class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->name }}</span>
                                        <span class="text-xs text-brand-600 font-semibold whitespace-nowrap">Tulis Ulasan →</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    @if(in_array($order->status, ['pending_payment', 'payment_pending']) && ! $order->paid_at)
                        <a href="{{ route('payment.show', $order->order_number) }}" class="block w-full text-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full shadow-sm transition">
                            Bayar Sekarang
                        </a>
                    @endif

                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-3">Alamat Pengiriman</h3>
                        @if($order->shipping_address)
                            <div class="text-sm text-gray-700 space-y-1">
                                <div class="font-semibold">{{ $order->shipping_address['recipient'] ?? '-' }}</div>
                                <div>{{ $order->shipping_address['phone'] ?? '-' }}</div>
                                <div class="text-gray-600">{{ $order->shipping_address['address_line_1'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['province'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-3">Pengiriman</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="text-gray-600">Kurir</dt><dd class="font-semibold">{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</dd></div>
                            @if($order->shipping_tracking_number)
                                <div class="flex justify-between"><dt class="text-gray-600">No. Resi</dt><dd class="font-mono text-xs">{{ $order->shipping_tracking_number }}</dd></div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-3">Pembayaran</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="text-gray-600">Metode</dt><dd class="font-semibold uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-600">Status</dt><dd>
                                @if($order->paid_at)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 font-semibold">Lunas</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">Belum Bayar</span>
                                @endif
                            </dd></div>
                        </div>
                    </div>

                    <a href="{{ route('dashboard.orders.invoice', $order->order_number) }}" class="block w-full text-center px-4 py-2 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">
                        Lihat Invoice
                    </a>
                </aside>
            </div>
        </div>
    </section>
@endsection