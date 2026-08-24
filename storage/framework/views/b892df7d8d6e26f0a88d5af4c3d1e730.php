<?php $__env->startSection('title', 'KARTEKS ENERGY SOLUTION - Solusi Energi Terbarukan & Kendaraan Listrik'); ?>
<?php $__env->startSection('description', 'KARTEKS ENERGY SOLUTION - Solusi energi terbarukan, kendaraan listrik, custom battery, dan konsultasi profesional di Sulawesi Selatan.'); ?>

<?php $__env->startSection('content'); ?>
    
    <section class="relative bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 text-white overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.4),transparent_50%)]"></div>

        <div class="container mx-auto px-4 sm:px-6 py-16 md:py-24 lg:py-32 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-3 py-1 text-xs font-medium uppercase tracking-wider mb-6">
                        <span class="w-1.5 h-1.5 bg-accent-400 rounded-full animate-pulse"></span>
                        Energi Terbarukan
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Solusi Energi
                        <span class="block text-accent-400">untuk Masa Depan</span>
                    </h1>
                    <p class="text-lg text-brand-50 leading-relaxed mb-8">
                        Dari kendaraan listrik hingga custom battery dan solar panel — KARTEKS menyediakan solusi energi terbarukan yang handal, efisien, dan berkelanjutan untuk bisnis dan rumah tangga Anda.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="<?php echo e(route('catalog.index')); ?>" class="inline-flex items-center px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition shadow-lg">
                            Lihat Produk
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                        </a>
                        <a href="<?php echo e(route('services.index')); ?>" class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 text-white font-semibold rounded-full hover:bg-white/20 transition">
                            Konsultasi Gratis
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-white/20 max-w-md">
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">500+</div>
                            <div class="text-xs text-brand-100 mt-1">Pelanggan</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">50+</div>
                            <div class="text-xs text-brand-100 mt-1">Produk</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">5★</div>
                            <div class="text-xs text-brand-100 mt-1">Rating</div>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($heroFeaturedProducts) && $heroFeaturedProducts && $heroFeaturedProducts->isNotEmpty()): ?>
                    <div class="hidden lg:block">
                        <div class="grid grid-cols-2 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $heroFeaturedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(route('catalog.show', $product->slug)); ?>" class="group bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-4 hover:bg-white/20 transition">
                                    <div class="aspect-square rounded-xl bg-white/20 mb-3 overflow-hidden flex items-center justify-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->getFirstMediaUrl('featured')): ?>
                                            <img src="<?php echo e($product->getFirstMediaUrl('featured')); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="text-sm font-semibold line-clamp-2"><?php echo e($product->name); ?></div>
                                    <div class="text-xs text-brand-100 mt-1"><?php echo e('Rp '.number_format((float) ($product->sale_price ?? $product->price), 0, ',', '.')); ?></div>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($sections) > 0): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $section = $sectionData['section'];
                $view = $sectionData['view'];
                $sectionDataArr = $sectionData['data'];
            ?>
            <?php echo $__env->renderWhen(View::exists($view), $view, ['section' => $section, 'data' => $sectionDataArr], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <?php else: ?>
        
        <?php echo $__env->make('pages.home._fallback', ['faqs' => $faqs], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($sections) > 0 && $faqs->isNotEmpty()): ?>
        <section class="py-12 md:py-16 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="max-w-3xl mx-auto">
                    <div class="text-center mb-10">
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">FAQ</span>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Pertanyaan Umum</h2>
                    </div>
                    <div class="space-y-3" x-data="{ open: null }">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                                <button type="button" @click="open = (open === <?php echo e($i); ?> ? null : <?php echo e($i); ?>)" class="w-full flex items-center justify-between p-5 text-left hover:bg-gray-50 transition">
                                    <span class="font-semibold text-gray-900 pr-4"><?php echo e($faq->question); ?></span>
                                    <svg class="w-5 h-5 text-gray-400 shrink-0 transition" :class="{ 'rotate-180': open === <?php echo e($i); ?> }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open === <?php echo e($i); ?>" x-collapse x-transition class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                                    <?php echo e($faq->answer); ?>

                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 text-center max-w-3xl">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Siap Beralih ke Energi Bersih?</h2>
            <p class="text-lg text-brand-50 mb-8">Konsultasikan kebutuhan energi Anda dengan tim ahli KARTEKS. Gratis, tanpa komitmen.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="<?php echo e(route('services.index')); ?>" class="inline-flex items-center px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition">
                    Konsultasi Gratis
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('karteks.company.whatsapp')): ?>
                    <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', config('karteks.company.whatsapp'))); ?>?text=Halo%20KARTEKS%2C%20saya%20tertarik%20dengan%20produk%20Anda" target="_blank" class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 font-semibold rounded-full hover:bg-white/20 transition">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.591 1.746 5.522l-1.453 5.31 5.196-1.531zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        Chat WhatsApp
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popupBanners->isNotEmpty()): ?>
        <?php $popup = $popupBanners->first(); ?>
        <div x-data="{ open: false }" x-init="const seen = sessionStorage.getItem('popup_<?php echo e($popup->id); ?>_seen'); if (!seen) { setTimeout(() => open = true, 1500); sessionStorage.setItem('popup_<?php echo e($popup->id); ?>_seen', '1'); }" x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
            <div @click.outside="open = false" class="relative bg-white rounded-3xl overflow-hidden max-w-lg w-full shadow-2xl">
                <button type="button" @click="open = false" class="absolute top-3 right-3 z-10 w-8 h-8 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-gray-600 hover:text-gray-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup->desktop_image_url): ?>
                    <img src="<?php echo e($popup->desktop_image_url); ?>" alt="<?php echo e($popup->title); ?>" class="w-full aspect-[3/4] object-cover">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="p-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup->title): ?>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo e($popup->title); ?></h3>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup->description): ?>
                        <p class="text-sm text-gray-600 mb-4"><?php echo e($popup->description); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popup->link_url): ?>
                        <a href="<?php echo e($popup->link_url); ?>" target="<?php echo e($popup->link_target ?? '_self'); ?>" class="inline-flex items-center px-5 py-2.5 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">
                            <?php echo e($popup->link_text ?: 'Lihat'); ?>

                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Banner click tracking — fire-and-forget POST
        function trackBannerClick(bannerId) {
            const url = '<?php echo e(url("banners")); ?>/' + bannerId + '/click';
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, new Blob([], { type: 'application/json' }));
            } else {
                fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } }).catch(() => {});
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/pages/home.blade.php ENDPATH**/ ?>