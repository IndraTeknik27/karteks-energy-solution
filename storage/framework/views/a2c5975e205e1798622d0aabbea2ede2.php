<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($seoMeta)): ?>
        <?php if (isset($component)) { $__componentOriginal1cac458866481a0e563721e566d01754 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1cac458866481a0e563721e566d01754 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.meta','data' => ['meta' => $seoMeta]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('meta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['meta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoMeta)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1cac458866481a0e563721e566d01754)): ?>
<?php $attributes = $__attributesOriginal1cac458866481a0e563721e566d01754; ?>
<?php unset($__attributesOriginal1cac458866481a0e563721e566d01754); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1cac458866481a0e563721e566d01754)): ?>
<?php $component = $__componentOriginal1cac458866481a0e563721e566d01754; ?>
<?php unset($__componentOriginal1cac458866481a0e563721e566d01754); ?>
<?php endif; ?>
    <?php else: ?>
        <title><?php echo $__env->yieldContent('title', config('karteks.company.name', 'KARTEKS ENERGY SOLUTION')); ?></title>
        <meta name="description" content="<?php echo $__env->yieldContent('description', config('karteks.seo.default_description')); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2310b981' stroke-width='2'><path d='M13 2L3 14h9l-1 8 10-12h-9l1-8z'/></svg>">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($seoSchemas)): ?>
        <?php if (isset($component)) { $__componentOriginal6f2bba119883b8508b4378648d9d7fcc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f2bba119883b8508b4378648d9d7fcc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.jsonld','data' => ['schemas' => $seoSchemas]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('jsonld'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['schemas' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoSchemas)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f2bba119883b8508b4378648d9d7fcc)): ?>
<?php $attributes = $__attributesOriginal6f2bba119883b8508b4378648d9d7fcc; ?>
<?php unset($__attributesOriginal6f2bba119883b8508b4378648d9d7fcc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f2bba119883b8508b4378648d9d7fcc)): ?>
<?php $component = $__componentOriginal6f2bba119883b8508b4378648d9d7fcc; ?>
<?php unset($__componentOriginal6f2bba119883b8508b4378648d9d7fcc); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="min-h-screen bg-white text-gray-900 font-sans antialiased flex flex-col">

    
    <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-20 right-4 z-50 max-w-sm bg-brand-600 text-white px-5 py-3 rounded-lg shadow-lg">
            <p class="text-sm font-medium"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-20 right-4 z-50 max-w-sm bg-red-600 text-white px-5 py-3 rounded-lg shadow-lg">
            <p class="text-sm font-medium"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <main class="flex-1">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/layouts/app.blade.php ENDPATH**/ ?>