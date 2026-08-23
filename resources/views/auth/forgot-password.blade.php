@extends('layouts.app')

@section('title', 'Lupa Password - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Lupa Password?</h1>
                <p class="text-sm text-gray-600 mb-6">Masukkan email Anda. Kami akan kirimkan link untuk reset password.</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.forgot.attempt') }}">
                    @csrf
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 mb-4">

                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Kirim Link Reset</button>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-medium">← Kembali ke Login</a>
                </p>
            </div>
        </div>
    </section>
@endsection