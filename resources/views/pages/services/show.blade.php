@extends('layouts.app')

@section('title', $service->name . ' - KARTEKS ENERGY SOLUTION')
@section('description', $service->short_description ?? '')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <nav class="text-sm text-brand-100 mb-2">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('services.index') }}" class="hover:text-white">Layanan</a>
                <span class="mx-1.5">/</span>
                <span class="text-white">{{ $service->name }}</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold">{{ $service->name }}</h1>
            <p class="text-brand-100 mt-2 text-lg">{{ $service->price_label }}</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-white">
        <div class="container mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2">
                @if($service->short_description)
                    <p class="text-lg text-gray-700 leading-relaxed mb-6">{{ $service->short_description }}</p>
                @endif

                @if($service->description)
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! $service->description !!}
                    </div>
                @endif

                @if($service->features && is_array($service->features) && count($service->features))
                    <h3 class="font-bold text-gray-900 mt-8 mb-3">Yang Anda Dapatkan:</h3>
                    <ul class="space-y-2">
                        @foreach($service->features as $feature)
                            <li class="flex items-start gap-3 text-gray-700">
                                <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m5 12 5 5L20 7"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($service->requirements && is_array($service->requirements) && count($service->requirements))
                    <h3 class="font-bold text-gray-900 mt-8 mb-3">Yang Perlu Disiapkan:</h3>
                    <ul class="space-y-2">
                        @foreach($service->requirements as $req)
                            <li class="flex items-start gap-3 text-gray-700">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                <span>{{ $req }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <aside class="lg:sticky lg:top-20 lg:self-start">
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-3">{{ $service->name }}</h3>

                    <dl class="space-y-3 text-sm mb-6">
                        @if($service->duration_minutes)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Durasi: {{ $service->duration_minutes }} menit</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-bold text-brand-700">{{ $service->price_label }}</span>
                        </div>
                        @if($service->pricing_type)
                            <div class="text-xs text-gray-500 uppercase tracking-wider">
                                Jenis: {{ ucfirst(str_replace('_', ' ', $service->pricing_type)) }}
                            </div>
                        @endif
                    </dl>

                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('karteks.company.whatsapp')) }}?text=Halo%20KARTEKS%2C%20saya%20tertarik%20dengan%20layanan%20{{ urlencode($service->name) }}" target="_blank" class="block w-full text-center px-4 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">
                        Konsultasi via WhatsApp
                    </a>

                    @auth
                        <a href="{{ route('dashboard.index') }}" class="block w-full text-center mt-2 px-4 py-3 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">
                            Booking via Dashboard
                        </a>
                    @endauth
                </div>
            </aside>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Layanan Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('services.show', $rel->slug) }}" class="group bg-white rounded-2xl p-5 hover:shadow-lg transition border border-gray-100">
                            <h3 class="font-bold text-sm text-gray-900 group-hover:text-brand-700 transition mb-2">{{ $rel->name }}</h3>
                            <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $rel->short_description }}</p>
                            <span class="text-sm font-semibold text-brand-700">{{ $rel->price_label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection