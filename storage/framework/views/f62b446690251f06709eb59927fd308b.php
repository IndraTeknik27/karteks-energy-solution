<?php $__env->startSection('title', 'Dashboard - KARTEKS ENERGY SOLUTION'); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
        <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
            <div class="container mx-auto px-4 sm:px-6 py-10">
                <h1 class="text-3xl md:text-4xl font-bold">Halo, <?php echo e(auth()->user()->name); ?>!</h1>
                <p class="text-brand-100 mt-1">Selamat datang di dashboard pelanggan Anda</p>
            </div>
        </section>

        <section class="py-8 md:py-12 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-4 gap-6">

                
                <aside>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="p-5 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-lg font-bold">
                                    <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 truncate"><?php echo e(auth()->user()->name); ?></div>
                                    <div class="text-xs text-gray-500 truncate"><?php echo e(auth()->user()->email); ?></div>
                                </div>
                            </div>
                        </div>
                        <nav class="text-sm">
                            <a href="<?php echo e(route('dashboard.index')); ?>" class="flex items-center gap-3 px-5 py-3 text-brand-700 bg-brand-50 font-semibold border-r-2 border-brand-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Overview
                            </a>
                            <a href="<?php echo e(route('dashboard.orders')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13"/></svg>
                                Pesanan Saya
                            </a>
                            <a href="<?php echo e(route('dashboard.wishlist')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                Wishlist
                            </a>
                            <a href="<?php echo e(route('dashboard.review.my')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                                Ulasan Saya
                            </a>
                            <a href="<?php echo e(route('dashboard.custom-battery.index')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Custom Battery
                            </a>
                            <a href="<?php echo e(route('dashboard.quotation.index')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Quotation
                            </a>
                            <a href="<?php echo e(route('dashboard.booking.index')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Service Booking
                            </a>
                            <a href="<?php echo e(route('dashboard.addresses')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Alamat
                            </a>
                            <a href="<?php echo e(route('dashboard.profile.edit')); ?>" class="flex items-center gap-3 px-5 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil
                            </a>
                            <form method="POST" action="<?php echo e(route('logout')); ?>" class="border-t border-gray-100">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="flex items-center gap-3 px-5 py-3 w-full text-left text-red-600 hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </nav>
                    </div>
                </aside>

                
                <div class="lg:col-span-3 space-y-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                        <div class="p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm"><?php echo e(session('success')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php
                        $user = auth()->user();
                        $orderCount = $user->orders()->count();
                        $addressCount = $user->addresses()->count();
                        $recentOrders = $user->orders()->latest('created_at')->limit(3)->get();
                    ?>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13"/></svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?php echo e($orderCount); ?></div>
                            <div class="text-xs text-gray-500">Total Pesanan</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?php echo e($addressCount); ?></div>
                            <div class="text-xs text-gray-500">Alamat Tersimpan</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?php echo e($user->hasVerifiedEmail() ? '✓' : '!'); ?></div>
                            <div class="text-xs text-gray-500">Email <?php echo e($user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum Verifikasi'); ?></div>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentOrders->isNotEmpty()): ?>
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                                <h2 class="font-bold text-gray-900">Pesanan Terbaru</h2>
                                <a href="<?php echo e(route('dashboard.orders')); ?>" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Lihat semua →</a>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e(route('dashboard.orders.show', $order->order_number)); ?>" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                                        <div>
                                            <div class="font-semibold text-gray-900"><?php echo e($order->order_number); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo e($order->created_at?->format('d M Y H:i')); ?> • <?php echo e($order->items->count()); ?> item</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-gray-900">Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?></div>
                                            <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($order->is_cancelled ? 'bg-red-100 text-red-700' : ($order->is_completed ? 'bg-brand-100 text-brand-700' : 'bg-amber-100 text-amber-700')); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?>

                                            </span>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
                            <p class="text-gray-500">Belum ada pesanan. <a href="<?php echo e(route('catalog.index')); ?>" class="text-brand-600 font-medium">Mulai belanja</a></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/dashboard/index.blade.php ENDPATH**/ ?>