@extends('layouts.app')

@section('title', 'Custom Battery Request - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Custom Battery Request</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Custom Battery Request</h1>
                    <p class="text-sm text-gray-500 mt-1">Ajukan permintaan battery pack sesuai kebutuhan spesifik Anda.</p>
                </div>
                <a href="{{ route('dashboard.custom-battery.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full shadow-sm transition text-center">
                    + Request Baru
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($requests->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-brand-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Belum ada permintaan</h3>
                    <p class="text-sm text-gray-500 mb-4">Mulai ajukan permintaan battery custom sesuai kebutuhan Anda.</p>
                    <a href="{{ route('dashboard.custom-battery.create') }}" class="inline-block px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full transition">
                        Buat Request Pertama
                    </a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($requests as $r)
                            <a href="{{ route('dashboard.custom-battery.show', $r->request_number) }}" class="block p-4 md:p-5 hover:bg-gray-50 transition">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="font-mono font-bold text-gray-900">{{ $r->request_number }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                @switch($r->status)
                                                    @case('submitted') bg-blue-100 text-blue-700 @break
                                                    @case('under_review') bg-amber-100 text-amber-700 @break
                                                    @case('revision_requested') bg-orange-100 text-orange-700 @break
                                                    @case('quoted') bg-purple-100 text-purple-700 @break
                                                    @case('approved') bg-brand-100 text-brand-700 @break
                                                    @case('rejected') bg-red-100 text-red-700 @break
                                                    @case('in_production') bg-cyan-100 text-cyan-700 @break
                                                    @case('completed') bg-green-100 text-green-700 @break
                                                    @case('cancelled') bg-gray-100 text-gray-700 @break
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                                            </span>
                                            @if($r->revision_count > 0)
                                                <span class="text-xs text-amber-600 font-semibold">Rev #{{ $r->revision_count }}</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 line-clamp-1">{{ $r->description }}</div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-500">
                                            <span>{{ $r->chemistry ?? '-' }} / {{ $r->voltage ?? '-' }}</span>
                                            <span>•</span>
                                            <span>{{ $r->quantity }} unit</span>
                                            <span>•</span>
                                            <span>{{ config('karteks.battery_options.applications.'.$r->application) ?? $r->application }}</span>
                                            @if($r->files_count > 0)
                                                <span>•</span>
                                                <span>📎 {{ $r->files_count }} file</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs text-gray-500">{{ $r->created_at->format('d M Y') }}</div>
                                        @if($r->estimated_price)
                                            <div class="text-sm font-bold text-brand-700 mt-1">Rp {{ number_format($r->estimated_price, 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">{{ $requests->links() }}</div>
            @endif
        </div>
    </section>
@endsection