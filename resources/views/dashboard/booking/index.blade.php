@extends('layouts.app')

@section('title', 'Service Booking - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Service Booking</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Service Booking</h1>
                    <p class="text-sm text-gray-500 mt-1">Jadwalkan layanan profesional dari tim KARTEKS.</p>
                </div>
                <a href="{{ route('dashboard.booking.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full shadow-sm transition text-center">
                    + Booking Baru
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($bookings->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-brand-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Belum ada booking</h3>
                    <p class="text-sm text-gray-500 mb-4">Booking service pertama Anda untuk konsultasi EV, instalasi solar, atau layanan lainnya.</p>
                    <a href="{{ route('dashboard.booking.create') }}" class="inline-block px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full transition">
                        Booking Pertama
                    </a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($bookings as $b)
                            <a href="{{ route('dashboard.booking.show', $b->booking_number) }}" class="block p-4 md:p-5 hover:bg-gray-50 transition">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="font-mono font-bold text-gray-900">{{ $b->booking_number }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                @switch($b->status)
                                                    @case('pending') bg-amber-100 text-amber-700 @break
                                                    @case('confirmed') bg-blue-100 text-blue-700 @break
                                                    @case('rescheduled') bg-orange-100 text-orange-700 @break
                                                    @case('in_progress') bg-cyan-100 text-cyan-700 @break
                                                    @case('completed') bg-green-100 text-green-700 @break
                                                    @case('cancelled') bg-red-100 text-red-700 @break
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-700 font-medium line-clamp-1">{{ $b->service?->name ?? 'Layanan' }}</div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-500">
                                            <span>📅 {{ $b->scheduled_at->format('d M Y, H:i') }}</span>
                                            @if($b->duration_minutes)
                                                <span>•</span>
                                                <span>{{ $b->duration_minutes }} min</span>
                                            @endif
                                            <span>•</span>
                                            <span>{{ match($b->location_type) { 'on_site' => 'On-site', 'in_store' => 'In-store', 'remote' => 'Remote' } }}</span>
                                            @if($b->technician)
                                                <span>•</span>
                                                <span>Teknisi: {{ $b->technician->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        @if($b->final_cost)
                                            <div class="text-sm font-bold text-brand-700">Rp {{ number_format($b->final_cost, 0, ',', '.') }}</div>
                                        @elseif($b->estimated_cost)
                                            <div class="text-sm font-bold text-gray-700">Rp {{ number_format($b->estimated_cost, 0, ',', '.') }}</div>
                                            <div class="text-xs text-gray-500">estimasi</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">{{ $bookings->links() }}</div>
            @endif
        </div>
    </section>
@endsection