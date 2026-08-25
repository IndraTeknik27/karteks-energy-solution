<?php
    /**
     * Hero Banner section partial.
     * Variables: $section (HomepageSection), $data (array with banners, autoplay)
     */
    $banners = $data['banners'] ?? collect();
    $autoplay = $data['autoplay'] ?? true;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banners->isNotEmpty()): ?>
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banners->count() === 1): ?>
                
                <?php $banner = $banners->first(); ?>
                <a href="<?php echo e($banner->link_url ?: '#'); ?>" target="<?php echo e($banner->link_target ?? '_self'); ?>"
                    onclick="fetch('<?php echo e(route('public.banner.click', $banner->id)); ?>', {method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}}).catch(()=>{})"
                    class="block relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 hover:shadow-lg transition group">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')): ?>
                        <div class="aspect-[16/5] sm:aspect-[16/4] overflow-hidden">
                            <img src="<?php echo e($banner->desktop_image_url); ?>" alt="<?php echo e($banner->title); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="p-6 sm:p-8 <?php echo e($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http') ? 'absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/70 via-black/30 to-transparent text-white' : ''); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->subtitle): ?>
                            <p class="text-xs uppercase tracking-wider <?php echo e(($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')) ? 'text-brand-100' : 'text-brand-700'); ?> font-semibold mb-2"><?php echo e($banner->subtitle); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->title): ?>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-2"><?php echo e($banner->title); ?></h3>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->description): ?>
                            <p class="text-sm <?php echo e(($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')) ? 'text-white/90' : 'text-gray-700'); ?> max-w-xl"><?php echo e(Str::limit($banner->description, 150)); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->link_text): ?>
                            <span class="inline-flex items-center mt-4 px-5 py-2 bg-white text-brand-700 font-semibold rounded-full text-sm group-hover:bg-brand-50 transition">
                                <?php echo e($banner->link_text); ?>

                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </a>
            <?php else: ?>
                
                <div class="grid grid-cols-1 md:grid-cols-<?php echo e(min($banners->count(), 3)); ?> gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($banner->link_url ?: '#'); ?>" target="<?php echo e($banner->link_target ?? '_self'); ?>"
                            onclick="fetch('<?php echo e(route('public.banner.click', $banner->id)); ?>', {method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}}).catch(()=>{})"
                            class="block relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 hover:shadow-lg transition group">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')): ?>
                                <div class="aspect-[16/9] sm:aspect-[16/10] overflow-hidden">
                                    <img src="<?php echo e($banner->desktop_image_url); ?>" alt="<?php echo e($banner->title); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="p-5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->subtitle): ?>
                                    <p class="text-[10px] uppercase tracking-wider text-brand-700 font-semibold mb-1"><?php echo e($banner->subtitle); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->title): ?>
                                    <h3 class="font-bold text-gray-900 mb-1 text-base"><?php echo e($banner->title); ?></h3>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->description): ?>
                                    <p class="text-sm text-gray-600 line-clamp-2"><?php echo e(Str::limit($banner->description, 80)); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->link_text): ?>
                                    <span class="inline-flex items-center text-sm font-semibold text-brand-700 mt-3">
                                        <?php echo e($banner->link_text); ?>

                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/partials/home-sections/hero-banner.blade.php ENDPATH**/ ?>