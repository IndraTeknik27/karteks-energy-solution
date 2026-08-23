@extends('layouts.app')

@section('title', 'Reset Password - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h1>
                <p class="text-sm text-gray-600 mb-6">Masukkan password baru Anda.</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.reset.attempt') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password Baru</label>
                        <input type="password" name="password" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Reset Password</button>
                </form>
            </div>
        </div>
    </section>
@endsection