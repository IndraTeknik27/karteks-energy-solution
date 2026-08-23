@extends('layouts.app')

@section('title', 'Masuk - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <svg class="w-10 h-10 text-brand-600" viewBox="0 0 32 32" fill="none"><path d="M16 2L4 18h9l-1.5 12L28 14h-9L21 2z" fill="currentColor"/></svg>
                    <span class="text-xl font-bold text-gray-900">KARTEKS</span>
                </a>
                <h1 class="mt-6 text-3xl font-bold text-gray-900">Selamat Datang Kembali</h1>
                <p class="mt-2 text-gray-600">Masuk untuk melanjutkan belanja Anda</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 @error('email') border-red-400 @enderror">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="flex items-center justify-between mb-6 text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="ml-2 text-gray-600">Ingat saya</span>
                        </label>
                        <a href="{{ route('password.forgot') }}" class="text-brand-600 hover:text-brand-700 font-medium">Lupa password?</a>
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                        Masuk
                    </button>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-700 font-semibold">Daftar sekarang</a>
                </p>
            </div>
        </div>
    </section>
@endsection