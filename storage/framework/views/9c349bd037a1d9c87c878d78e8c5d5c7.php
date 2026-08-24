<?php $__env->startSection('title', ($address->exists ? 'Edit' : 'Tambah') . ' Alamat - KARTEKS ENERGY SOLUTION'); ?>

<?php $__env->startSection('content'); ?>
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-3xl font-bold"><?php echo e($address->exists ? 'Edit' : 'Tambah'); ?> Alamat</h1>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-2xl">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><li><?php echo e($err); ?></li><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <form method="POST" action="<?php echo e($address->exists ? route('dashboard.addresses.update', $address) : route('dashboard.addresses.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address->exists): ?><?php echo method_field('PUT'); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Label (opsional)</label>
                            <input type="text" name="label" value="<?php echo e(old('label', $address->label)); ?>" placeholder="Rumah, Kantor, dll" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Penerima</label>
                            <input type="text" name="recipient" value="<?php echo e(old('recipient', $address->recipient)); ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">WhatsApp</label>
                            <input type="tel" name="phone" value="<?php echo e(old('phone', $address->phone)); ?>" required placeholder="081234567890" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Lengkap</label>
                            <input type="text" name="address_line_1" value="<?php echo e(old('address_line_1', $address->address_line_1)); ?>" required placeholder="Jl. Contoh No. 123" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Detail Tambahan (opsional)</label>
                            <input type="text" name="address_line_2" value="<?php echo e(old('address_line_2', $address->address_line_2)); ?>" placeholder="RT/RW, Patokan, dll" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Provinsi</label>
                            <input type="text" name="province" value="<?php echo e(old('province', $address->province)); ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kota/Kabupaten</label>
                            <input type="text" name="city" value="<?php echo e(old('city', $address->city)); ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kecamatan</label>
                            <input type="text" name="district" value="<?php echo e(old('district', $address->district)); ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelurahan/Desa (opsional)</label>
                            <input type="text" name="village" value="<?php echo e(old('village', $address->village)); ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Pos</label>
                            <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $address->postal_code)); ?>" required pattern="[0-9]{5}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan (opsional)</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"><?php echo e(old('notes', $address->notes)); ?></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_primary" value="1" <?php echo e(old('is_primary', $address->is_primary) ? 'checked' : ''); ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>Jadikan sebagai alamat utama</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Simpan</button>
                        <a href="<?php echo e(route('dashboard.addresses')); ?>" class="px-6 py-3 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\karteks-energy-solution\resources\views/profile/addresses/create.blade.php ENDPATH**/ ?>