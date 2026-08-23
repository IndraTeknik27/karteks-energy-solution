@extends('layouts.app')

@section('title', ($address->exists ? 'Edit' : 'Tambah') . ' Alamat - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-3xl font-bold">{{ $address->exists ? 'Edit' : 'Tambah' }} Alamat</h1>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-2xl">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <form method="POST" action="{{ $address->exists ? route('dashboard.addresses.update', $address) : route('dashboard.addresses.store') }}">
                    @csrf
                    @if($address->exists)@method('PUT')@endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Label (opsional)</label>
                            <input type="text" name="label" value="{{ old('label', $address->label) }}" placeholder="Rumah, Kantor, dll" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Penerima</label>
                            <input type="text" name="recipient" value="{{ old('recipient', $address->recipient) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">WhatsApp</label>
                            <input type="tel" name="phone" value="{{ old('phone', $address->phone) }}" required placeholder="081234567890" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Lengkap</label>
                            <input type="text" name="address_line_1" value="{{ old('address_line_1', $address->address_line_1) }}" required placeholder="Jl. Contoh No. 123" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Detail Tambahan (opsional)</label>
                            <input type="text" name="address_line_2" value="{{ old('address_line_2', $address->address_line_2) }}" placeholder="RT/RW, Patokan, dll" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', $address->province) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kota/Kabupaten</label>
                            <input type="text" name="city" value="{{ old('city', $address->city) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kecamatan</label>
                            <input type="text" name="district" value="{{ old('district', $address->district) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelurahan/Desa (opsional)</label>
                            <input type="text" name="village" value="{{ old('village', $address->village) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Pos</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required pattern="[0-9]{5}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan (opsional)</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('notes', $address->notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $address->is_primary) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>Jadikan sebagai alamat utama</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Simpan</button>
                        <a href="{{ route('dashboard.addresses') }}" class="px-6 py-3 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection