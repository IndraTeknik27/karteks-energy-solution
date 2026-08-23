@extends('layouts.app')

@section('title', 'Pembayaran Tertunda - KARTEKS')

@section('content')
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-xl text-center">
            <div class="bg-white rounded-2xl border border-gray-100 p-10">
                <div class="w-16 h-16 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Tertunda</h1>
                <p class="text-gray-600 mb-1">Order <span class="font-mono font-semibold">{{ $order->order_number }}</span></p>
                <p class="text-sm text-gray-500 mb-6">Pembayaran Anda belum selesai. Selesaikan pembayaran untuk melanjutkan proses order.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('payment.show', $order->order_number) }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full transition">Lanjutkan Pembayaran</a>
                    <a href="{{ route('dashboard.orders.show', $order->order_number) }}" class="px-5 py-2.5 border border-gray-200 hover:border-gray-300 text-gray-700 font-semibold rounded-full transition">Lihat Order</a>
                </div>
            </div>
        </div>
    </section>
@endsection
