@extends('layouts.app')

@section('title', 'Daftar - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <svg class="w-10 h-10 text-brand-600" viewBox="0 0 32 32" fill="none"><path d="M16 2L4 18h9l-1.5 12L28 14h-9L21 2z" fill="currentColor"/></svg>
                    <span class="text-xl font-bold text-gray-900">KARTEKS</span>
                </a>
                <h1 class="mt-6 text-3xl font-bold text-gray-900">Buat Akun Baru</h1>
                <p class="mt-2 text-gray-600">Bergabung dan dapatkan penawaran eksklusif</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.attempt') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">WhatsApp <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="081234567890"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter, kombinasi huruf besar-kecil dan angka</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                        Daftar
                    </button>

                    <p class="text-xs text-gray-500 mt-3 text-center">
                        Dengan mendaftar, Anda menyetujui <a href="{{ route('pages.show', 'syarat-ketentuan') }}" class="text-brand-600 hover:underline">Syarat &amp; Ketentuan</a> dan <a href="{{ route('pages.show', 'kebijakan-privasi') }}" class="text-brand-600 hover:underline">Kebijakan Privasi</a>.
                    </p>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-semibold">Masuk</a>
                </p>
            </div>
        </div>
    </section>
@endsection