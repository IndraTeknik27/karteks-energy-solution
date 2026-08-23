@extends('layouts.app')

@section('title', 'Booking Service Baru - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.booking.index') }}" class="hover:text-brand-600">Service Booking</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Baru</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 md:p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Booking Service Baru</h1>
                <p class="text-sm text-gray-500 mb-6">Pilih layanan dan jadwal yang sesuai. Tim KARTEKS akan konfirmasi dalam 1x24 jam.</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.booking.store') }}" class="space-y-5" x-data="{ serviceId: '{{ old('service_id', $preselectedService?->id ?? '') }}', locationType: '{{ old('location_type', 'on_site') }}' }">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Layanan <span class="text-red-600">*</span></label>
                        <select name="service_id" x-model="serviceId" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="">Pilih layanan</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}" {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                                    {{ $svc->name }} @if($svc->duration_minutes)({{ $svc->duration_minutes }} min)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Nama <span class="text-red-600">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">No. WhatsApp <span class="text-red-600">*</span></label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone) }}" required placeholder="08xxx" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Tanggal & Jam <span class="text-red-600">*</span></label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required min="{{ now()->addDay()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 mt-1">Jam operasional: 08:00 - 17:00 WITA</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Tipe Lokasi <span class="text-red-600">*</span></label>
                        <select name="location_type" x-model="locationType" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="on_site">On-site (tim ke lokasi Anda)</option>
                            <option value="in_store">In-store (ke toko KARTEKS)</option>
                            <option value="remote">Remote (konsultasi online)</option>
                        </select>
                    </div>

                    <div x-show="locationType === 'on_site'" x-cloak>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Alamat Lengkap <span class="text-red-600">*</span></label>
                        <textarea name="location_address" rows="2" placeholder="Jalan, kota, patokan" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('location_address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Catatan Tambahan</label>
                        <textarea name="customer_notes" rows="2" placeholder="Detail tambahan yang perlu kami tahu..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('customer_notes') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full shadow-sm transition">Submit Booking</button>
                        <a href="{{ route('dashboard.booking.index') }}" class="px-6 py-2.5 border border-gray-200 hover:border-gray-300 text-gray-700 font-semibold rounded-full transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection