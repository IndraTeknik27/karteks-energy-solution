@extends('layouts.app')

@section('title', 'Checkout - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <nav class="text-sm text-brand-100 mb-2">
                <a href="{{ route('cart.index') }}" class="hover:text-white">Keranjang</a>
                <span class="mx-1.5">/</span>
                <span class="text-white">Checkout</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold">Checkout</h1>
        </div>
    </section>

    @if($errors->any())
        <div class="container mx-auto px-4 sm:px-6 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
                {{ $errors->first() }}
            </div>
        </div>
    @endif

@php
    $shippingOptionsJson = json_encode($shippingOptions);
    $couponCodeJson = json_encode($cart->coupon_code ?? '');
@endphp

    <form method="POST" action="{{ route('checkout.place') }}" x-data="{
        selectedAddressId: {{ $defaultAddress?->id ?? 'null' }},
        selectedCourier: 'jne',
        shippingOptions: {{ $shippingOptionsJson }},
        shippingCost: 0,
        selectedService: 'REG',
        couponCode: {{ $couponCodeJson }},
        subtotal: {{ (float) $cart->subtotal }},
        discount: {{ (float) $cart->discount }},
        get total() {
            return Math.max(0, this.subtotal - this.discount + parseFloat(this.shippingCost || 0));
        },
        formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        init() {
            this.recalcShipping();
        },
        recalcShipping() {
            if (this.selectedAddressId) {
                fetch('{{ route("checkout.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        shipping_address_id: this.selectedAddressId,
                        shipping_courier: this.selectedCourier,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.shippingOptions = data.data.shipping_options;
                        this.recalcShipping();
                    }
                });
            }
        },
        pickService(service, cost) {
            this.selectedService = service;
            this.shippingCost = cost;
        },
    }">
        @csrf
        <input type="hidden" name="shipping_cost" :value="shippingCost">
        <input type="hidden" name="shipping_service" :value="selectedService">
        <input type="hidden" name="billing_same_as_shipping" value="1">

        <section class="py-8 md:py-12 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left: form --}}
                    <div class="lg:col-span-2 space-y-5">

                        {{-- Address --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="font-bold text-gray-900">Alamat Pengiriman</h2>
                                <a href="{{ route('dashboard.addresses.create') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">+ Tambah Alamat</a>
                            </div>

                            @if($addresses->isEmpty())
                                <p class="text-sm text-gray-500">Anda belum punya alamat. <a href="{{ route('dashboard.addresses.create') }}" class="text-brand-600 font-medium">Tambah alamat pertama</a></p>
                            @else
                                <div class="space-y-3">
                                    @foreach($addresses as $address)
                                        <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition"
                                            :class="selectedAddressId === {{ $address->id }} ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-brand-300'">
                                            <input type="radio" name="shipping_address_id" value="{{ $address->id }}" x-model.number="selectedAddressId" @change="recalcShipping()" class="mt-1 shrink-0" {{ $address->is_primary ? 'checked' : '' }}>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-semibold text-gray-900">{{ $address->recipient }}</span>
                                                    @if($address->is_primary)
                                                        <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-[10px] uppercase tracking-wider font-bold rounded">Utama</span>
                                                    @endif
                                                    @if($address->label)
                                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] uppercase tracking-wider font-bold rounded">{{ $address->label }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-600 mt-1">{{ $address->phone }}</div>
                                                <div class="text-sm text-gray-700 mt-2">{{ $address->address_line_1 }}, {{ $address->address_line_2 ? $address->address_line_2 . ', ' : '' }}{{ $address->village ? $address->village . ', ' : '' }}{{ $address->district }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</div>
                                                @if($address->notes)
                                                    <div class="text-xs text-gray-500 mt-2">Catatan: {{ $address->notes }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Shipping --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Metode Pengiriman</h2>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kurir</label>
                                <select name="shipping_courier" x-model="selectedCourier" @change="recalcShipping()" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                    <option value="jne">JNE</option>
                                    <option value="pos">POS Indonesia</option>
                                    <option value="tiki">TIKI</option>
                                    <option value="sicepat">SiCepat</option>
                                    <option value="jnt">J&amp;T Express</option>
                                </select>
                            </div>

                            <div x-show="shippingOptions.length > 0" class="space-y-2">
                                <template x-for="opt in shippingOptions" :key="opt.service">
                                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition"
                                        :class="selectedService === opt.service ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-brand-300'"
                                        @click="pickService(opt.service, opt.cost)">
                                        <input type="radio" name="shipping_service_select" :value="opt.service" :checked="selectedService === opt.service" class="shrink-0">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900" x-text="opt.courier + ' ' + opt.service + ' - ' + opt.name"></div>
                                            <div class="text-xs text-gray-500" x-text="'Estimasi ' + opt.eta_days + ' hari'"></div>
                                        </div>
                                        <div class="font-bold text-brand-700" x-text="opt.is_free ? 'GRATIS' : formatRupiah(opt.cost)"></div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Customer info --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Data Pemesan</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                                    <input type="text" name="customer_name" value="{{ auth()->user()->name }}" required class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                                    <input type="email" name="customer_email" value="{{ auth()->user()->email }}" required class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp</label>
                                    <input type="tel" name="customer_phone" value="{{ auth()->user()->phone }}" required class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Order (opsional)</label>
                                    <textarea name="customer_notes" rows="2" placeholder="Misalnya: Tolong kirim sore hari" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Payment --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Metode Pembayaran</h2>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 border-2 border-brand-500 bg-brand-50 rounded-xl cursor-pointer">
                                    <input type="radio" name="payment_method" value="midtrans" checked class="shrink-0">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900">Midtrans Payment Gateway</div>
                                        <div class="text-xs text-gray-500">Transfer bank, e-wallet, kartu kredit</div>
                                    </div>
                                    <svg class="w-10 h-6 text-brand-600" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Right: summary --}}
                    <aside class="lg:sticky lg:top-20 lg:self-start">
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4">Ringkasan Order</h3>

                            <div class="space-y-3 mb-4 pb-4 border-b border-gray-100 max-h-72 overflow-y-auto">
                                @foreach($cart->items as $item)
                                    @php $itemable = $item->itemable; @endphp
                                    <div class="flex gap-3 text-sm">
                                        <div class="w-14 h-14 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                                            @if($itemable && method_exists($itemable, 'getFirstMediaUrl') && $itemable->getFirstMediaUrl('images', 'thumb'))
                                                <img src="{{ $itemable->getFirstMediaUrl('images', 'thumb') }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 line-clamp-2 text-xs">{{ $itemable->name ?? 'Produk' }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $item->qty }} × Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="font-semibold text-gray-900 text-xs whitespace-nowrap">Rp {{ number_format($item->qty * $item->price_snapshot, 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Subtotal</dt>
                                    <dd class="font-semibold" x-text="formatRupiah(subtotal)"></dd>
                                </div>
                                <template x-if="discount > 0">
                                    <div class="flex justify-between text-brand-700">
                                        <dt>Diskon</dt>
                                        <dd class="font-semibold" x-text="'− ' + formatRupiah(discount)"></dd>
                                    </div>
                                </template>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Pengiriman</dt>
                                    <dd class="font-semibold" x-text="formatRupiah(shippingCost || 0)"></dd>
                                </div>
                                <div class="flex justify-between border-t border-gray-100 pt-3 mt-3">
                                    <dt class="font-bold text-gray-900">Total</dt>
                                    <dd class="font-bold text-xl text-brand-700" x-text="formatRupiah(total)"></dd>
                                </div>
                            </dl>

                            @if($cart->coupon_code)
                                <div class="mt-4 p-3 bg-brand-50 rounded-lg flex items-center justify-between">
                                    <span class="text-xs font-semibold text-brand-700">Coupon: {{ $cart->coupon_code }}</span>
                                    <form method="POST" action="{{ route('cart.removeCoupon') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500">Hapus</button>
                                    </form>
                                </div>
                            @endif

                            <button type="submit" class="w-full mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                                Buat Order
                            </button>

                            <p class="text-xs text-gray-500 mt-3 text-center">
                                Dengan klik "Buat Order", Anda menyetujui Syarat &amp; Ketentuan KARTEKS.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </form>
@endsection