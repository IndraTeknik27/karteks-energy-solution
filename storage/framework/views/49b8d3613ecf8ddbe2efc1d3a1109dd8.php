<?php
    $companyEmail = config('karteks.company.email');
    $companyPhone = config('karteks.company.phone');
    $companyWhatsApp = config('karteks.company.whatsapp');
    $companyAddress = config('karteks.company.address');

    try {
        $footerPages = \App\Models\Page::where('is_published', true)
            ->where('show_in_footer', true)
            ->orderBy('sort')
            ->get();
    } catch (\Throwable $e) {
        $footerPages = collect();
    }
?>

<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="container mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-9 h-9 text-brand-500" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 2L4 18h9l-1.5 12L28 14h-9L21 2z" fill="currentColor"/>
                    </svg>
                    <div class="leading-tight">
                        <div class="text-base font-bold text-white">KARTEKS</div>
                        <div class="text-[10px] uppercase tracking-wider text-brand-400 font-semibold">Energy Solution</div>
                    </div>
                </div>
                <p class="text-sm leading-relaxed text-gray-400 mb-4"><?php echo e(config('karteks.company.tagline')); ?></p>
                <div class="flex gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('karteks.company.whatsapp')): ?>
                        <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $companyWhatsApp)); ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-800 hover:bg-brand-600 transition" aria-label="WhatsApp">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.591 1.746 5.522l-1.453 5.31 5.196-1.531zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('karteks.company.email')): ?>
                        <a href="mailto:<?php echo e($companyEmail); ?>" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-800 hover:bg-brand-600 transition" aria-label="Email">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('karteks.company.website')): ?>
                        <a href="<?php echo e(config('karteks.company.website')); ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-800 hover:bg-brand-600 transition" aria-label="Website">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Tautan Cepat</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(route('catalog.index')); ?>" class="hover:text-brand-400 transition">Semua Produk</a></li>
                    <li><a href="<?php echo e(route('services.index')); ?>" class="hover:text-brand-400 transition">Layanan</a></li>
                    <li><a href="<?php echo e(route('blog.index')); ?>" class="hover:text-brand-400 transition">Blog &amp; Tips</a></li>
                    <li><a href="<?php echo e(route('cart.index')); ?>" class="hover:text-brand-400 transition">Keranjang</a></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <li><a href="<?php echo e(route('dashboard.index')); ?>" class="hover:text-brand-400 transition">Akun Saya</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo e(route('login')); ?>" class="hover:text-brand-400 transition">Masuk</a></li>
                        <li><a href="<?php echo e(route('register')); ?>" class="hover:text-brand-400 transition">Daftar</a></li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Informasi</h3>
                <ul class="space-y-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $footerPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><a href="<?php echo e(route('pages.show', $page->slug)); ?>" class="hover:text-brand-400 transition"><?php echo e($page->title); ?></a></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <li class="text-gray-500">Belum ada halaman.</li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Hubungi Kami</h3>
                <ul class="space-y-3 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyPhone): ?>
                        <li class="flex gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                            <a href="tel:<?php echo e($companyPhone); ?>" class="hover:text-brand-400 transition"><?php echo e($companyPhone); ?></a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyEmail): ?>
                        <li class="flex gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <a href="mailto:<?php echo e($companyEmail); ?>" class="hover:text-brand-400 transition break-all"><?php echo e($companyEmail); ?></a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyAddress): ?>
                        <li class="flex gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><?php echo e($companyAddress); ?></span>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-10 pt-6 text-xs text-gray-500 flex flex-col sm:flex-row gap-2 items-center justify-between">
            <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('karteks.company.name')); ?>. All rights reserved.</p>
            <p>Powered by KARTEKS Energy Solution</p>
        </div>
    </div>
</footer><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/partials/footer.blade.php ENDPATH**/ ?>