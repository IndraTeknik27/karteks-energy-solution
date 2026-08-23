@extends('layouts.app')

@section('title', 'Quotation ' . $quotation->quotation_number . ' - KARTEKS')

@php
    $statusColor = match($quotation->status) {
        'draft' => 'bg-gray-100 text-gray-700',
        'sent' => 'bg-blue-100 text-blue-700',
        'viewed' => 'bg-amber-100 text-amber-700',
        'accepted' => 'bg-brand-100 text-brand-700',
        'rejected' => 'bg-red-100 text-red-700',
        'expired' => 'bg-gray-200 text-gray-600',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.quotation.index') }}" class="hover:text-brand-600">Quotation</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">{{ $quotation->quotation_number }}</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            {{-- Header --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Quotation</div>
                        <div class="text-2xl font-bold text-gray-900 font-mono mt-1">{{ $quotation->quotation_number }}</div>
                        <h2 class="text-lg font-semibold text-gray-800 mt-3">{{ $quotation->title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Disusun oleh {{ $quotation->creator?->name ?? 'Tim KARTEKS' }} • {{ $quotation->created_at->format('d F Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-4 py-2 text-sm rounded-full font-bold {{ $statusColor }}">
                            {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
                        </span>
                        @if($quotation->valid_until)
                            <div class="text-xs text-gray-500 mt-2">Berlaku sampai: {{ $quotation->valid_until->format('d F Y') }}</div>
                            @if($quotation->is_expired && !in_array($quotation->status, ['accepted', 'rejected']))
                                <div class="text-xs text-red-600 font-semibold mt-1">SUDAH KADALUARSA</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Description --}}
                    @if($quotation->description)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-2">Deskripsi</h2>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $quotation->description }}</p>
                        </div>
                    @endif

                    {{-- Items --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Item</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                        <th class="text-left py-2">Item</th>
                                        <th class="text-center py-2">Qty</th>
                                        <th class="text-right py-2">Harga Satuan</th>
                                        <th class="text-right py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($quotation->items as $item)
                                        <tr>
                                            <td class="py-3">
                                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                                @if($item->description)
                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</div>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center">{{ $item->qty }}</td>
                                            <td class="py-3 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="py-3 text-right font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Terms --}}
                    @if($quotation->terms_conditions)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-2">Syarat & Ketentuan</h2>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $quotation->terms_conditions }}</p>
                        </div>
                    @endif

                    {{-- Accepted/Rejected banner --}}
                    @if($quotation->status === 'accepted')
                        <div class="bg-brand-50 border-2 border-brand-200 rounded-2xl p-6 text-center">
                            <div class="text-2xl mb-1">✓</div>
                            <div class="font-bold text-brand-700 mb-1">Quotation Diterima</div>
                            <p class="text-sm text-gray-700">Pada {{ $quotation->accepted_at->format('d F Y, H:i') }} WITA. Tim KARTEKS akan menghubungi Anda untuk langkah selanjutnya.</p>
                        </div>
                    @elseif($quotation->status === 'rejected')
                        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 text-center">
                            <div class="text-2xl mb-1">✗</div>
                            <div class="font-bold text-red-700 mb-1">Quotation Ditolak</div>
                            <p class="text-sm text-gray-700">Pada {{ $quotation->rejected_at->format('d F Y, H:i') }} WITA</p>
                            @if($quotation->rejection_reason)
                                <p class="text-sm text-gray-700 mt-2"><strong>Alasan:</strong> {{ $quotation->rejection_reason }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    {{-- Total --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</dd></div>
                            @if($quotation->discount > 0)
                                <div class="flex justify-between text-red-700"><dt>Diskon</dt><dd>− Rp {{ number_format($quotation->discount, 0, ',', '.') }}</dd></div>
                            @endif
                            @if($quotation->tax > 0)
                                <div class="flex justify-between"><dt class="text-gray-600">PPN</dt><dd>Rp {{ number_format($quotation->tax, 0, ',', '.') }}</dd></div>
                            @endif
                        </div>
                        <div class="flex justify-between border-t border-gray-100 pt-3">
                            <dt class="font-bold text-gray-900">Total</dt>
                            <dd class="font-bold text-2xl text-brand-700">Rp {{ number_format($quotation->total, 0, ',', '.') }}</dd>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if(in_array($quotation->status, ['sent', 'viewed'], true) && ! $quotation->is_expired)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                            <h3 class="font-bold text-gray-900">Tanggapan Anda</h3>
                            <form method="POST" action="{{ route('dashboard.quotation.accept', $quotation->quotation_number) }}">
                                @csrf
                                <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-2 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                <button type="submit" class="w-full px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition">✓ Terima Quotation</button>
                            </form>
                            <details class="border border-gray-200 rounded-lg">
                                <summary class="px-3 py-2 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">Tolak Quotation</summary>
                                <form method="POST" action="{{ route('dashboard.quotation.reject', $quotation->quotation_number) }}" class="p-3 space-y-2 border-t border-gray-200">
                                    @csrf
                                    <textarea name="reason" required minlength="10" rows="3" placeholder="Alasan penolakan (min. 10 karakter)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition" onclick="return confirm('Yakin menolak quotation ini?')">Tolak</button>
                                </form>
                            </details>
                        </div>
                    @endif

                    <a href="{{ route('dashboard.quotation.index') }}" class="block w-full text-center px-4 py-2 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">
                        ← Kembali ke Daftar Quotation
                    </a>
                </aside>
            </div>
        </div>
    </section>
@endsection