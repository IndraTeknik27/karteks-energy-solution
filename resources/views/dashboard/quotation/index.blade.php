@extends('layouts.app')

@section('title', 'Quotation - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Quotation</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Quotation</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar quotation yang disiapkan oleh tim KARTEKS untuk Anda.</p>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($quotations->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-brand-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Belum ada quotation</h3>
                    <p class="text-sm text-gray-500">Quotation dari tim KARTEKS akan muncul di sini setelah disiapkan.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($quotations as $q)
                            <a href="{{ route('dashboard.quotation.show', $q->quotation_number) }}" class="block p-4 md:p-5 hover:bg-gray-50 transition">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="font-mono font-bold text-gray-900">{{ $q->quotation_number }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                @switch($q->status)
                                                    @case('draft') bg-gray-100 text-gray-700 @break
                                                    @case('sent') bg-blue-100 text-blue-700 @break
                                                    @case('viewed') bg-amber-100 text-amber-700 @break
                                                    @case('accepted') bg-brand-100 text-brand-700 @break
                                                    @case('rejected') bg-red-100 text-red-700 @break
                                                    @case('expired') bg-gray-200 text-gray-600 @break
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $q->status)) }}
                                            </span>
                                            @if($q->is_expired && !in_array($q->status, ['accepted', 'rejected']))
                                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">Kadaluarsa</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-700 line-clamp-1">{{ $q->title }}</div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-500">
                                            <span>{{ $q->items_count }} item</span>
                                            @if($q->valid_until)
                                                <span>•</span>
                                                <span>Berlaku sampai {{ $q->valid_until->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-base font-bold text-brand-700">Rp {{ number_format($q->total, 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $q->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">{{ $quotations->links() }}</div>
            @endif
        </div>
    </section>
@endsection