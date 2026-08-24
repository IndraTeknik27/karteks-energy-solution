@php
    /**
     * Brand Logos section partial.
     * Variables: $section (HomepageSection), $data (array with brands Collection)
     */
    $brands = $data['brands'] ?? collect();
@endphp

@if($brands->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if($section->title || $section->subtitle)
                <div class="text-center mb-8">
                    @if($section->subtitle)
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                    @endif
                    @if($section->title)
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mt-1">{{ $section->title }}</h2>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
                @foreach($brands as $brand)
                    @if($brand->website)
                        <a href="{{ $brand->website }}" target="_blank" rel="nofollow noopener" class="flex items-center justify-center p-4 bg-white rounded-2xl border border-gray-100 hover:shadow-md hover:border-brand-200 transition aspect-[3/2]">
                            @if($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="max-w-full max-h-full object-contain" loading="lazy">
                            @else
                                <span class="text-sm font-semibold text-gray-700 text-center">{{ $brand->name }}</span>
                            @endif
                        </a>
                    @else
                        <div class="flex items-center justify-center p-4 bg-white rounded-2xl border border-gray-100 aspect-[3/2]">
                            @if($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="max-w-full max-h-full object-contain" loading="lazy">
                            @else
                                <span class="text-sm font-semibold text-gray-700 text-center">{{ $brand->name }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif