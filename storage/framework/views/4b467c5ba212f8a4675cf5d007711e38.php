<?php
    /**
     * Custom Battery Promo section partial.
     * Variables: $section (HomepageSection), $data (array with cta_url, cta_label)
     */
    $ctaUrl = $data['cta_url'] ?? '/dashboard/custom-battery/create';
    $ctaLabel = $data['cta_label'] ?? 'Konsultasi Sekarang';
?>

<section class="py-12 md:py-16 bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.1),transparent_60%)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.3),transparent_50%)]"></div>

    <div class="container mx-auto px-4 sm:px-6 relative">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-3 py-1 text-xs font-medium uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 bg-accent-400 rounded-full animate-pulse"></span>
                    Custom Battery
                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section->title): ?>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight mb-4"><?php echo e($section->title); ?></h2>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section->subtitle): ?>
                    <p class="text-lg text-brand-50 leading-relaxed mb-6"><?php echo e($section->subtitle); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex flex-wrap gap-3">
                    <a href="<?php echo e($ctaUrl); ?>" class="inline-flex items-center px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition shadow-lg">
                        <?php echo e($ctaLabel); ?>

                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                    </a>
                    <a href="<?php echo e(route('services.show', 'custom-battery-service')); ?>" class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 text-white font-semibold rounded-full hover:bg-white/20 transition">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>

            <div class="hidden lg:flex justify-center">
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-3xl p-8 max-w-sm">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-4">
                            <div class="w-10 h-10 bg-accent-400 rounded-xl flex items-center justify-center text-brand-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="text-sm">Spesifikasi sesuai kebutuhan Anda</div>
                        </div>
                        <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-4">
                            <div class="w-10 h-10 bg-accent-400 rounded-xl flex items-center justify-center text-brand-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="text-sm">Konsultasi gratis dengan teknisi ahli</div>
                        </div>
                        <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-4">
                            <div class="w-10 h-10 bg-accent-400 rounded-xl flex items-center justify-center text-brand-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="text-sm">Garansi & after-sales profesional</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/partials/home-sections/custom-battery-promo.blade.php ENDPATH**/ ?>