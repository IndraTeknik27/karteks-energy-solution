<?php $__env->startSection('title', $page->meta_title ?? $page->title); ?>
<?php $__env->startSection('description', $page->meta_description ?? ''); ?>

<?php $__env->startSection('content'); ?>
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold"><?php echo e($page->title); ?></h1>
        </div>
    </section>

    <article class="py-10 bg-white">
        <div class="container mx-auto px-4 sm:px-6 max-w-4xl">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page->featured_image_url): ?>
                <img src="<?php echo e($page->featured_image_url); ?>" alt="<?php echo e($page->title); ?>" class="w-full rounded-2xl mb-8">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="prose prose-lg max-w-none text-gray-700">
                <?php echo $page->content; ?>

            </div>
        </div>
    </article>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/pages/page.blade.php ENDPATH**/ ?>