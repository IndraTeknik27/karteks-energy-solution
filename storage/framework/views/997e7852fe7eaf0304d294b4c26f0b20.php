<?php $__env->startSection('title', ($currentCategory?->name ?? 'Semua Produk') . ' - KARTEKS ENERGY SOLUTION'); ?>

<?php $__env->startSection('content'); ?>
    
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10 md:py-14">
            <nav class="text-sm text-brand-100 mb-2">
                <a href="<?php echo e(route('home')); ?>" class="hover:text-white">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="<?php echo e(route('catalog.index')); ?>" class="hover:text-white">Produk</a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCategory): ?>
                    <span class="mx-1.5">/</span>
                    <span class="text-white"><?php echo e($currentCategory->name); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCategory): ?>
                    <?php echo e($currentCategory->name); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCategory->description): ?>
                        <p class="text-brand-100 font-normal text-base mt-2 max-w-2xl"><?php echo e($currentCategory->description); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php elseif($currentBrand): ?>
                    Brand: <?php echo e($currentBrand->name); ?>

                <?php else: ?>
                    Semua Produk
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h1>
            <p class="text-brand-100 mt-2"><?php echo e($products->total()); ?> produk ditemukan</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                
                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    <form method="GET" action="<?php echo e(route('catalog.index')); ?>" class="bg-white rounded-2xl p-5 border border-gray-100">
                        <h3 class="font-bold text-gray-900 mb-3">Cari</h3>
                        <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari produk..." class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Kategori</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="<?php echo e(route('catalog.index', request()->except('category_slug', 'category_id', 'page'))); ?>" class="flex items-center justify-between py-1 <?php echo e(!request('category_slug') && !request('category_id') ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600'); ?>">
                                    <span>Semua Kategori</span>
                                </a>
                            </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <li>
                                    <a href="<?php echo e(route('catalog.index', array_merge(request()->except('category_slug', 'category_id', 'page'), ['category_slug' => $cat->slug]))); ?>" class="flex items-center justify-between py-1 <?php echo e(request('category_slug') === $cat->slug ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600'); ?>">
                                        <span><?php echo e($cat->name); ?></span>
                                    </a>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Brand</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="<?php echo e(route('catalog.index', request()->except('brand_slug', 'brand_id', 'page'))); ?>" class="flex items-center py-1 <?php echo e(!request('brand_slug') && !request('brand_id') ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600'); ?>">
                                    Semua Brand
                                </a>
                            </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <li>
                                    <a href="<?php echo e(route('catalog.index', array_merge(request()->except('brand_slug', 'brand_id', 'page'), ['brand_slug' => $brand->slug]))); ?>" class="flex items-center py-1 <?php echo e(request('brand_slug') === $brand->slug ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600'); ?>">
                                        <?php echo e($brand->name); ?>

                                    </a>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Harga</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="<?php echo e(request('min_price')); ?>" placeholder="Min" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <input type="number" name="max_price" value="<?php echo e(request('max_price')); ?>" placeholder="Max" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>

                        <button type="submit" class="w-full mt-5 px-4 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                            Terapkan Filter
                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->anyFilled(['search', 'category_slug', 'category_id', 'brand_slug', 'brand_id', 'min_price', 'max_price'])): ?>
                            <a href="<?php echo e(route('catalog.index')); ?>" class="block text-center mt-2 text-xs text-gray-500 hover:text-brand-600">Reset semua filter</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </form>
                </aside>

                
                <div class="lg:col-span-3">
                    
                    <div class="flex items-center justify-between mb-4 bg-white rounded-2xl border border-gray-100 px-4 py-3">
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900"><?php echo e($products->count()); ?></span> produk ditampilkan
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Urutkan:</label>
                            <select name="sort" onchange="window.location.href = this.value" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="<?php echo e(route('catalog.index', array_merge(request()->all(), ['sort' => 'latest']))); ?>" <?php echo e(request('sort', 'latest') === 'latest' ? 'selected' : ''); ?>>Terbaru</option>
                                <option value="<?php echo e(route('catalog.index', array_merge(request()->all(), ['sort' => 'popular']))); ?>" <?php echo e(request('sort') === 'popular' ? 'selected' : ''); ?>>Paling Dilihat</option>
                                <option value="<?php echo e(route('catalog.index', array_merge(request()->all(), ['sort' => 'price_asc']))); ?>" <?php echo e(request('sort') === 'price_asc' ? 'selected' : ''); ?>>Harga Terendah</option>
                                <option value="<?php echo e(route('catalog.index', array_merge(request()->all(), ['sort' => 'price_desc']))); ?>" <?php echo e(request('sort') === 'price_desc' ? 'selected' : ''); ?>>Harga Tertinggi</option>
                                <option value="<?php echo e(route('catalog.index', array_merge(request()->all(), ['sort' => 'name_asc']))); ?>" <?php echo e(request('sort') === 'name_asc' ? 'selected' : ''); ?>>Nama A-Z</option>
                            </select>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->isEmpty()): ?>
                        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900">Produk tidak ditemukan</h3>
                            <p class="text-sm text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                            <a href="<?php echo e(route('catalog.index')); ?>" class="inline-block mt-4 text-sm text-brand-600 hover:text-brand-700 font-semibold">Reset filter →</a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php echo $__env->make('partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>

                        
                        <div class="mt-8">
                            <?php echo e($products->links()); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/pages/catalog/index.blade.php ENDPATH**/ ?>