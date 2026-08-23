@extends('layouts.app')

@section('title', 'Alamat Saya - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Alamat Saya</h1>
                    <p class="text-brand-100 mt-1">{{ $addresses->count() }} alamat tersimpan</p>
                </div>
                <a href="{{ route('dashboard.addresses.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-brand-700 text-sm font-semibold rounded-full hover:bg-brand-50 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                    Tambah Alamat
                </a>
            </div>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($addresses->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h2 class="mt-4 text-lg font-bold text-gray-900">Belum ada alamat</h2>
                    <p class="text-gray-500 mt-1">Tambah alamat pertama Anda untuk mempercepat checkout.</p>
                    <a href="{{ route('dashboard.addresses.create') }}" class="inline-block mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Tambah Alamat</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($addresses as $address)
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ $address->recipient }}</span>
                                    @if($address->is_primary)
                                        <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-[10px] uppercase tracking-wider font-bold rounded">Utama</span>
                                    @endif
                                    @if($address->label)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] uppercase tracking-wider font-bold rounded">{{ $address->label }}</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">{{ $address->phone }}</div>
                                <div class="text-sm text-gray-700 mt-2">{{ $address->full_address }}</div>
                                @if($address->notes)
                                    <div class="text-xs text-gray-500 mt-2">Catatan: {{ $address->notes }}</div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-1 text-xs shrink-0">
                                @if(! $address->is_primary)
                                    <form method="POST" action="{{ route('dashboard.addresses.primary', $address) }}">
                                        @csrf
                                        <button type="submit" class="text-brand-600 hover:text-brand-700 font-medium">Jadikan Utama</button>
                                    </form>
                                @endif
                                <a href="{{ route('dashboard.addresses.edit', $address) }}" class="text-gray-600 hover:text-gray-900 font-medium">Edit</a>
                                <form method="POST" action="{{ route('dashboard.addresses.destroy', $address) }}" onsubmit="return confirm('Hapus alamat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection