@extends('layouts.app')

@section('title', 'Pembayaran ' . $order->order_number . ' - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.orders') }}" class="hover:text-brand-600">Pesanan</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="hover:text-brand-600">{{ $order->order_number }}</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Bayar</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Selesaikan Pembayaran</h1>
                <p class="text-sm text-gray-500">Order <span class="font-mono font-semibold">{{ $order->order_number }}</span></p>
            </div>

            @if($error)
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-6 mb-6">
                    <h2 class="font-bold mb-1">Gagal memuat pembayaran</h2>
                    <p class="text-sm">{{ $error }}</p>
                </div>
            @elseif(!$snapToken)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-6 mb-6">
                    <h2 class="font-bold mb-1">Snap token belum tersedia</h2>
                    <p class="text-sm">Coba muat ulang halaman ini dalam beberapa saat.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Ringkasan Order</h2>
                    <div class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <div class="flex gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="w-14 h-14 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                                    @if($item->image)
                                        <img src="{{ $item->image }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-sm font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                            @if($order->coupon_discount > 0)
                                <div class="flex justify-between text-brand-700"><dt>Diskon</dt><dd>− Rp {{ number_format($order->coupon_discount, 0, ',', '.') }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-gray-600">Pengiriman</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-2 mt-2"><dt class="font-bold text-gray-900">Total</dt><dd class="font-bold text-xl text-brand-700">Rp {{ number_format($order->total, 0, ',', '.') }}</dd></div>
                        </div>
                    </div>

                    @if($snapToken)
                        <button id="pay-button" type="button" class="block w-full text-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full shadow-sm transition">
                            Bayar Sekarang
                        </button>
                        <p class="text-xs text-gray-500 text-center">Pembayaran diproses oleh <span class="font-semibold">Midtrans</span> (sandbox).</p>
                    @else
                        <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="block w-full text-center px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-full transition">
                            Kembali ke Order
                        </a>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    @if($snapToken)
        @push('head')
            <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
        @endpush

        @push('scripts')
            <script>
                (function () {
                    const payButton = document.getElementById('pay-button');
                    if (!payButton || !window.snap) return;

                    payButton.addEventListener('click', function () {
                        payButton.disabled = true;
                        payButton.textContent = 'Membuka pembayaran...';

                        window.snap.pay('{{ $snapToken }}', {
                            onSuccess: function (result) {
                                window.location.href = '{{ route('payment.finish', $order->order_number) }}' + '?transaction_id=' + encodeURIComponent(result.transaction_id || '');
                            },
                            onPending: function (result) {
                                window.location.href = '{{ route('payment.unfinish', $order->order_number) }}' + '?transaction_id=' + encodeURIComponent(result.transaction_id || '');
                            },
                            onError: function (result) {
                                window.location.href = '{{ route('payment.error', $order->order_number) }}' + '?transaction_id=' + encodeURIComponent(result.transaction_id || '');
                            },
                            onClose: function () {
                                payButton.disabled = false;
                                payButton.textContent = 'Bayar Sekarang';
                            }
                        });
                    });
                })();
            </script>
        @endpush
    @endif
@endsection
