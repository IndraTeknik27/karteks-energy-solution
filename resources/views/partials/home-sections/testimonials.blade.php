@php
    /**
     * Testimonials section partial.
     * Variables: $section (HomepageSection), $data (array with testimonials Collection)
     */
    $testimonials = $data['testimonials'] ?? collect();
@endphp

@if($testimonials->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                @if($section->subtitle)
                    <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                @endif
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? 'Apa Kata Pelanggan Kami' }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($testimonials as $testimonial)
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <div class="flex items-center gap-1 text-accent-500 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $testimonial->rating)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                @endif
                            @endfor
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">"{{ $testimonial->content }}"</p>
                        <div class="flex items-center gap-3">
                            @if($testimonial->customer_avatar_url ?? false)
                                <img src="{{ $testimonial->customer_avatar_url }}" alt="{{ $testimonial->customer_name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr($testimonial->customer_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-sm text-gray-900">{{ $testimonial->customer_name }}</div>
                                @if($testimonial->position || $testimonial->company)
                                    <div class="text-xs text-gray-500">{{ trim(($testimonial->position ?? '') . ($testimonial->company ? ' • ' . $testimonial->company : '')) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif