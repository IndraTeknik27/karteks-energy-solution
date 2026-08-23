@extends('layouts.app')

@section('title', 'Pesanan Saya - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold">Pesanan Saya</h1>
            <p class="text-brand-100 mt-1">Riwayat pesanan Anda</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($orders->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13"/></svg>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">Belum ada pesanan</h2>
                    <p class="text-gray-500 mt-2">Mulai belanja dan pesan produk pertama Anda.</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Lihat Katalog</a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($orders as $order)
                            <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-4 md:p-5 hover:bg-gray-50 transition">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $order->created_at?->format('d M Y H:i') }} • {{ $order->items->count() }} item</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Kurir: {{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</div>
                                </div>
                                <div class="text-left md:text-right">
                                    <div class="font-bold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full font-semibold
                                        {{ $order->is_cancelled ? 'bg-red-100 text-red-700' :
                                           ($order->is_completed ? 'bg-brand-100 text-brand-700' :
                                           ($order->is_paid ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            @endif
        </div>
    </section>
@endsection