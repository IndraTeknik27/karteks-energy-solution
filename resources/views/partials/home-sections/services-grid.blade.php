@php
    /**
     * Services Grid section partial.
     * Variables: $section (HomepageSection), $data (array with services Collection)
     */
    $services = $data['services'] ?? collect();
@endphp

@if($services->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    @if($section->subtitle)
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                    @endif
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? 'Layanan Profesional' }}</h2>
                </div>
                <a href="{{ route('services.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($services as $service)
                    <a href="{{ route('services.show', $service->slug) }}" class="group bg-white rounded-2xl p-6 hover:shadow-lg transition border border-gray-100">
                        <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-4 group-hover:bg-brand-600 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.444L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.444l1.745-1.444"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $service->name }}</h3>
                        @if($service->short_description)
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $service->short_description }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-brand-600">{{ $service->price_label ?? 'Hubungi Kami' }}</span>
                            <span class="text-brand-600 group-hover:translate-x-1 transition">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif