@extends('layouts.app')

@section('title', 'Request Custom Battery Baru - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.custom-battery.index') }}" class="hover:text-brand-600">Custom Battery</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Baru</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 md:p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Request Custom Battery Baru</h1>
                <p class="text-sm text-gray-500 mb-6">Isi formulir di bawah ini dengan kebutuhan spesifik Anda. Tim KARTEKS akan meninjau dalam 1x24 jam.</p>

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.custom-battery.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Kimia Baterai <span class="text-red-600">*</span></label>
                            <select name="chemistry" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="">Pilih kimia baterai</option>
                                @foreach($options['chemistry'] as $chem)
                                    <option value="{{ $chem }}" {{ old('chemistry') == $chem ? 'selected' : '' }}>{{ $chem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Voltase <span class="text-red-600">*</span></label>
                            <select name="voltage" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="">Pilih voltase</option>
                                @foreach($options['voltage'] as $volt)
                                    <option value="{{ $volt }}" {{ old('voltage') == $volt ? 'selected' : '' }}>{{ $volt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Kapasitas (Ah)</label>
                            <input type="text" name="capacity" value="{{ old('capacity') }}" placeholder="misal: 100Ah" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Energi (kWh)</label>
                            <input type="number" step="0.01" name="kwh" value="{{ old('kwh') }}" placeholder="misal: 5.0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Jumlah <span class="text-red-600">*</span></label>
                            <input type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Aplikasi <span class="text-red-600">*</span></label>
                            <select name="application" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="">Pilih aplikasi</option>
                                @foreach($options['applications'] as $key => $label)
                                    <option value="{{ $key }}" {{ old('application') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1.5">Load Saat Ini</label>
                            <input type="text" name="current_load" value="{{ old('current_load') }}" placeholder="misal: 50A continuous" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Dimensi (mm) - Opsional</label>
                        <div class="grid grid-cols-3 gap-3">
                            <input type="number" step="0.1" min="0" name="dimensions[length]" value="{{ old('dimensions.length') }}" placeholder="Panjang" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <input type="number" step="0.1" min="0" name="dimensions[width]" value="{{ old('dimensions.width') }}" placeholder="Lebar" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <input type="number" step="0.1" min="0" name="dimensions[height]" value="{{ old('dimensions.height') }}" placeholder="Tinggi" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Deadline</label>
                        <input type="date" name="deadline" value="{{ old('deadline') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Deskripsi Kebutuhan <span class="text-red-600">*</span></label>
                        <textarea name="description" required minlength="20" rows="5" placeholder="Jelaskan kebutuhan battery pack Anda secara detail (minimal 20 karakter)..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Semakin detail, semakin akurat quotation yang akan kami berikan.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Catatan Tambahan</label>
                        <textarea name="customer_notes" rows="2" placeholder="Preferensi material, brand cell tertentu, atau info lain yang perlu kami tahu..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('customer_notes') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full shadow-sm transition">Submit Request</button>
                        <a href="{{ route('dashboard.custom-battery.index') }}" class="px-6 py-2.5 border border-gray-200 hover:border-gray-300 text-gray-700 font-semibold rounded-full transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection