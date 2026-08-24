<?php
    $productUrl = route('catalog.show', $product->slug);
    $hasDiscount = $product->sale_price && $product->sale_price > 0 && $product->sale_price < $product->price;
    $displayPrice = $product->sale_price > 0 ? $product->sale_price : $product->price;
    $priceFormatted = 'Rp ' . number_format($displayPrice, 0, ',', '.');
    $oldPriceFormatted = $hasDiscount ? 'Rp ' . number_format($product->price, 0, ',', '.') : null;
?>

<a href="<?php echo e($productUrl); ?>" class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg hover:border-brand-200 transition">
    <div class="aspect-square bg-gray-50 overflow-hidden relative">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->getFirstMediaUrl('images', 'thumb')): ?>
            <img src="<?php echo e($product->getFirstMediaUrl('images', 'thumb')); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasDiscount): ?>
            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                -<?php echo e(round((1 - $product->sale_price / $product->price) * 100)); ?>%
            </span>
        <?php elseif($product->is_bestseller): ?>
            <span class="absolute top-2 left-2 bg-accent-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                Bestseller
            </span>
        <?php elseif($product->is_new_arrival): ?>
            <span class="absolute top-2 left-2 bg-brand-600 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                Baru
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="p-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?>
            <div class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold mb-1"><?php echo e($product->brand->name); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-brand-700 transition min-h-[2.5rem]"><?php echo e($product->name); ?></h3>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-base font-bold text-brand-700"><?php echo e($priceFormatted); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($oldPriceFormatted): ?>
                <span class="text-xs text-gray-400 line-through"><?php echo e($oldPriceFormatted); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->manage_stock): ?>
            <div class="text-[10px] text-gray-500 mt-1">
                Stok: <?php echo e(max(0, (int) $product->stock_qty - (int) ($product->reserved_qty ?? 0))); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</a><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/partials/product-card.blade.php ENDPATH**/ ?>