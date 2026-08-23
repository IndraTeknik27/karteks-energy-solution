@extends('layouts.app')

@section('title', 'Layanan - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold">Layanan Profesional</h1>
            <p class="text-brand-100 mt-1">{{ $services->count() }} layanan tersedia</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Categories sidebar --}}
                @if($categories->isNotEmpty())
                    <aside class="lg:sticky lg:top-20 lg:self-start">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 mb-3">Kategori</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach($categories as $cat)
                                    <li class="flex items-center justify-between text-gray-700 hover:text-brand-600">
                                        <span>{{ $cat->name }}</span>
                                        <span class="text-xs text-gray-400">({{ $cat->services_count }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                @endif

                <div class="{{ $categories->isNotEmpty() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 {{ $categories->isNotEmpty() ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }} gap-4">
                        @foreach($services as $service)
                            <a href="{{ route('services.show', $service->slug) }}" class="group bg-white rounded-2xl p-6 hover:shadow-lg transition border border-gray-100">
                                <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-4 group-hover:bg-brand-600 group-hover:text-white transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.444L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.444l1.745-1.444"/></svg>
                                </div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-brand-700 transition">{{ $service->name }}</h3>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-3">{{ $service->short_description }}</p>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-brand-700">{{ $service->price_label }}</span>
                                    <span class="text-brand-600 group-hover:translate-x-1 transition">→</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection