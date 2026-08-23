@extends('layouts.app')

@section('title', 'Keranjang Belanja - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold">Keranjang Belanja</h1>
            <p class="text-brand-100 mt-1">{{ $cart->items->count() }} item di keranjang Anda</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if($cart->items->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <svg class="w-20 h-20 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm9 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">Keranjang Anda kosong</h2>
                    <p class="text-gray-500 mt-2">Belum ada produk di keranjang. Yuk mulai belanja!</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">
                        Lihat Katalog Produk
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Cart items --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                            @foreach($cart->items as $item)
                                @php
                                    $itemable = $item->itemable;
                                    $displayPrice = (float) $item->price_snapshot;
                                    $subtotal = $displayPrice * $item->qty;
                                    $imageUrl = $itemable && method_exists($itemable, 'getFirstMediaUrl')
                                        ? ($itemable->getFirstMediaUrl('images', 'thumb') ?: $itemable->getFirstMediaUrl('image', 'thumb'))
                                        : null;
                                @endphp

                                <div class="flex gap-4 p-4 border-b border-gray-100 last:border-0">
                                    <a href="{{ $itemable ? route('catalog.show', $itemable->slug) : '#' }}" class="shrink-0 w-24 h-24 bg-gray-50 rounded-xl overflow-hidden">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $itemable->name ?? 'Produk' }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="flex-1 min-w-0">
                                        <a href="{{ $itemable ? route('catalog.show', $itemable->slug) : '#' }}" class="font-semibold text-gray-900 hover:text-brand-700 line-clamp-2">{{ $itemable->name ?? 'Produk tidak tersedia' }}</a>
                                        <div class="text-sm text-gray-500 mt-1">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>

                                        <div class="flex items-center justify-between mt-3 gap-2">
                                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center border border-gray-200 rounded-full overflow-hidden">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" name="qty" value="{{ max(1, $item->qty - 1) }}" class="px-3 py-1 text-gray-500 hover:bg-gray-100">−</button>
                                                <span class="px-3 py-1 text-sm font-medium border-x border-gray-200 min-w-[3rem] text-center">{{ $item->qty }}</span>
                                                <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="px-3 py-1 text-gray-500 hover:bg-gray-100">+</button>
                                            </form>

                                            <div class="flex items-center gap-3">
                                                <span class="font-bold text-brand-700">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                <form method="POST" action="{{ route('cart.remove', $item->id) }}" onsubmit="return confirm('Hapus item ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" aria-label="Hapus">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('catalog.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">← Lanjut Belanja</a>
                            <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Kosongkan semua item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Kosongkan Keranjang</button>
                            </form>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4">Ringkasan Belanja</h3>

                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Subtotal ({{ $cart->items->sum('qty') }} item)</dt>
                                    <dd class="font-semibold">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</dd>
                                </div>

                                @if($cart->discount > 0)
                                    <div class="flex justify-between text-brand-700">
                                        <dt>Diskon @if($cart->coupon_code)({{ $cart->coupon_code }})@endif</dt>
                                        <dd class="font-semibold">− Rp {{ number_format($cart->discount, 0, ',', '.') }}</dd>
                                    </div>
                                @endif

                                <div class="flex justify-between border-t border-gray-100 pt-3 mt-3">
                                    <dt class="font-bold text-gray-900">Total</dt>
                                    <dd class="font-bold text-xl text-brand-700">Rp {{ number_format($cart->total, 0, ',', '.') }}</dd>
                                </div>
                            </dl>

                            <a href="{{ auth()->check() ? route('checkout.index') : route('login') }}" class="block w-full text-center mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                                @auth
                                    Lanjut ke Checkout
                                @else
                                    Masuk untuk Checkout
                                @endauth
                            </a>

                            {{-- Coupon --}}
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                @if($cart->coupon_code)
                                    <div class="flex items-center justify-between p-3 bg-brand-50 rounded-lg">
                                        <div>
                                            <div class="text-sm font-semibold text-brand-700">{{ $cart->coupon_code }}</div>
                                            <div class="text-xs text-brand-600">Coupon aktif</div>
                                        </div>
                                        <form method="POST" action="{{ route('cart.removeCoupon') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                        </form>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('cart.applyCoupon') }}">
                                        @csrf
                                        <label class="block text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">Punya Kode Coupon?</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="code" required placeholder="Masukkan kode" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm uppercase tracking-wider focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition">Pakai</button>
                                        </div>
                                        @error('coupon')
                                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                        @enderror
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Trust signals --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 text-sm space-y-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Garansi produk original</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Pengiriman seluruh Indonesia</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>Pembayaran aman Midtrans</span>
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection