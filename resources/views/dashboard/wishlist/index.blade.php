@extends('layouts.app')

@section('title', 'Wishlist Saya - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Wishlist Saya</h1>
                    <p class="text-brand-100 mt-1">{{ $items->count() }} produk tersimpan</p>
                </div>
                @if($items->isNotEmpty())
                    <form method="POST" action="{{ route('dashboard.wishlist.clear') }}" onsubmit="return confirm('Kosongkan semua wishlist?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 text-white text-sm font-semibold rounded-full hover:bg-white/20 transition">
                            Kosongkan Wishlist
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
            @endif

            @if($items->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">Wishlist kosong</h2>
                    <p class="text-gray-500 mt-2">Belum ada produk yang Anda simpan.</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Lihat Katalog</a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($items as $product)
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden relative group">
                            <form method="POST" action="{{ route('dashboard.wishlist.remove', $product) }}" class="absolute top-2 right-2 z-10">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-red-500 hover:text-red-700 shadow-sm" aria-label="Hapus dari wishlist">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                </button>
                            </form>

                            <a href="{{ route('catalog.show', $product->slug) }}" class="block">
                                <div class="aspect-square bg-gray-50 overflow-hidden">
                                    @if($product->getFirstMediaUrl('images', 'thumb'))
                                        <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4">
                                    @if($product->brand)
                                        <div class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold mb-1">{{ $product->brand->name }}</div>
                                    @endif
                                    <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-brand-700 transition min-h-[2.5rem]">{{ $product->name }}</h3>
                                    <div class="text-base font-bold text-brand-700 mt-2">Rp {{ number_format($product->sale_price > 0 ? $product->sale_price : $product->price, 0, ',', '.') }}</div>
                                </div>
                            </a>

                            <div class="px-4 pb-4">
                                <form method="POST" action="{{ route('dashboard.wishlist.moveToCart', $product) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 bg-brand-600 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm9 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                                        Pindah ke Keranjang
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection