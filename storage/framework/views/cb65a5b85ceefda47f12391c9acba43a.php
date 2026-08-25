<?php
    $customer = auth()->user();
    $cartCount = session('cart_count', 0);
?>

<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm" x-data="{ mobileOpen: false, searchOpen: false, searchQuery: '' }">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">

            
            <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2">
                <svg class="w-9 h-9 text-brand-600" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 2L4 18h9l-1.5 12L28 14h-9L21 2z" fill="currentColor"/>
                </svg>
                <div class="leading-tight">
                    <div class="text-base font-bold text-gray-900">KARTEKS</div>
                    <div class="text-[10px] uppercase tracking-wider text-brand-600 font-semibold">Energy Solution</div>
                </div>
            </a>

            
            <nav class="hidden lg:flex items-center gap-7 text-sm">
                <a href="<?php echo e(route('home')); ?>" class="text-gray-700 hover:text-brand-600 font-medium transition">Beranda</a>
                <a href="<?php echo e(route('catalog.index')); ?>" class="text-gray-700 hover:text-brand-600 font-medium transition">Produk</a>
                <a href="<?php echo e(route('services.index')); ?>" class="text-gray-700 hover:text-brand-600 font-medium transition">Layanan</a>
                <a href="<?php echo e(route('blog.index')); ?>" class="text-gray-700 hover:text-brand-600 font-medium transition">Blog</a>
                <a href="<?php echo e(route('pages.show', 'kontak-kami')); ?>" class="text-gray-700 hover:text-brand-600 font-medium transition">Kontak</a>
            </nav>

            
            <div class="flex items-center gap-2">
                
                <div x-show="!searchOpen" x-transition>
                    <button type="button" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full text-gray-600 hover:bg-gray-100 hover:text-brand-600 transition" aria-label="Cari" @click="searchOpen = true; $nextTick(() => document.getElementById('nav-search-input')?.focus())">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                </div>

                
                <div x-show="searchOpen" x-cloak x-transition class="hidden md:flex items-center">
                    <form action="<?php echo e(route('catalog.index')); ?>" method="GET" class="flex items-center" @submit="if (!searchQuery.trim()) { $event.preventDefault(); searchOpen = false; }">
                        <input
                            type="search"
                            name="search"
                            id="nav-search-input"
                            x-model="searchQuery"
                            placeholder="Cari produk..."
                            class="w-40 lg:w-52 h-9 pl-3 pr-2 rounded-full border border-gray-200 bg-white text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                            @keydown.escape="searchOpen = false; searchQuery = ''"
                        />
                        <button type="submit" class="ml-1 flex items-center justify-center w-8 h-8 rounded-full text-brand-600 hover:bg-brand-50 transition" aria-label="Submit search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        <button type="button" class="flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" aria-label="Tutup pencarian" @click="searchOpen = false; searchQuery = ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>

                
                <a href="<?php echo e(route('cart.index')); ?>" class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-600 hover:bg-gray-100 hover:text-brand-600 transition" aria-label="Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm9 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cartCount > 0): ?>
                        <span class="absolute -top-0.5 -right-0.5 bg-brand-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center"><?php echo e($cartCount); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin() || auth()->user()->isCustomer()): ?>
                        <a href="<?php echo e(auth()->user()->isAdmin() ? '/admin' : route('dashboard.index')); ?>" class="hidden md:flex items-center gap-1.5 text-sm text-gray-700 hover:text-brand-600 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span><?php echo e($customer->name); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="hidden md:inline-flex items-center text-sm text-gray-700 hover:text-brand-600 font-medium">Masuk</a>
                    <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-full transition">
                        Daftar
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <button type="button" class="lg:hidden flex items-center justify-center w-9 h-9 rounded-full text-gray-600 hover:bg-gray-100" @click="mobileOpen = !mobileOpen" aria-label="Menu">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        
        <div x-show="mobileOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="lg:hidden border-t border-gray-100 -mx-4 sm:-mx-6 px-4 sm:px-6 pb-4 pt-3">
            <nav class="space-y-1">
                <a href="<?php echo e(route('home')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium <?php if(request()->routeIs('home')): ?> bg-brand-50 text-brand-700 <?php endif; ?>">
                    Beranda
                </a>
                <a href="<?php echo e(route('catalog.index')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium <?php if(request()->routeIs('catalog.*')): ?> bg-brand-50 text-brand-700 <?php endif; ?>">
                    Produk
                </a>
                <a href="<?php echo e(route('services.index')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium <?php if(request()->routeIs('services.*')): ?> bg-brand-50 text-brand-700 <?php endif; ?>">
                    Layanan
                </a>
                <a href="<?php echo e(route('blog.index')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium <?php if(request()->routeIs('blog.*')): ?> bg-brand-50 text-brand-700 <?php endif; ?>">
                    Blog
                </a>
                <a href="<?php echo e(route('pages.show', 'kontak-kami')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium">
                    Kontak
                </a>

                <div class="border-t border-gray-100 pt-3 mt-3">
                    
                    <form action="<?php echo e(route('catalog.index')); ?>" method="GET" class="flex items-center mb-3 px-4">
                        <input
                            type="search"
                            name="search"
                            placeholder="Cari produk..."
                            class="flex-1 h-10 pl-4 pr-3 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        />
                        <button type="submit" class="ml-2 flex items-center justify-center w-10 h-10 rounded-xl bg-brand-600 text-white hover:bg-brand-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                    </form>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(auth()->user()->isAdmin() ? '/admin' : route('dashboard.index')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium">
                            Dashboard Saya
                        </a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full flex items-center px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 font-medium">
                                Keluar
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium">
                            Masuk
                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="flex items-center px-4 py-3 rounded-xl bg-brand-600 text-white hover:bg-brand-700 font-semibold mt-2">
                            Daftar Sekarang
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>
<?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/partials/navbar.blade.php ENDPATH**/ ?>