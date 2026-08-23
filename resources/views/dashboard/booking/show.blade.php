@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_number . ' - KARTEKS')

@php
    $statusColor = match($booking->status) {
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'rescheduled' => 'bg-orange-100 text-orange-700',
        'in_progress' => 'bg-cyan-100 text-cyan-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
    $locationLabel = match($booking->location_type) {
        'on_site' => 'On-site (Tim ke lokasi)',
        'in_store' => 'In-store (ke toko KARTEKS)',
        'remote' => 'Remote (online)',
        default => $booking->location_type,
    };
@endphp

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.booking.index') }}" class="hover:text-brand-600">Service Booking</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">{{ $booking->booking_number }}</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
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

            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Service Booking</div>
                        <div class="text-2xl font-bold text-gray-900 font-mono mt-1">{{ $booking->booking_number }}</div>
                        <h2 class="text-lg font-semibold text-gray-800 mt-3">{{ $booking->service?->name ?? 'Layanan' }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Dibuat {{ $booking->created_at->format('d F Y, H:i') }} WITA</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-4 py-2 text-sm rounded-full font-bold {{ $statusColor }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Detail Booking</h2>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-gray-500">Tanggal & Jam</dt><dd class="font-semibold">{{ $booking->scheduled_at->format('d F Y, H:i') }} WITA</dd></div>
                            <div><dt class="text-gray-500">Durasi</dt><dd class="font-semibold">{{ $booking->duration_minutes ?? 60 }} menit</dd></div>
                            <div><dt class="text-gray-500">Tipe Lokasi</dt><dd class="font-semibold">{{ $locationLabel }}</dd></div>
                            <div>
                                <dt class="text-gray-500">Teknisi</dt>
                                <dd class="font-semibold">
                                    @if($booking->technician)
                                        {{ $booking->technician->name }}
                                    @else
                                        <span class="text-gray-400">Akan di-assign setelah konfirmasi</span>
                                    @endif
                                </dd>
                            </div>
                            @if($booking->location_address)
                                <div class="col-span-2">
                                    <dt class="text-gray-500">Alamat</dt>
                                    <dd class="font-semibold">{{ $booking->location_address }}</dd>
                                </div>
                            @endif
                            @if($booking->customer_notes)
                                <div class="col-span-2">
                                    <dt class="text-gray-500">Catatan Customer</dt>
                                    <dd class="text-sm text-gray-700">{{ $booking->customer_notes }}</dd>
                                </div>
                            @endif
                            @if($booking->admin_notes)
                                <div class="col-span-2">
                                    <dt class="text-gray-500">Catatan Tim KARTEKS</dt>
                                    <dd class="text-sm text-gray-700">{{ $booking->admin_notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    @if($booking->statusHistories->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Riwayat Status</h2>
                            <div class="space-y-3">
                                @foreach($booking->statusHistories as $h)
                                    <div class="flex gap-3">
                                        <div class="w-2 h-2 bg-brand-500 rounded-full mt-2 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $h->from_status ? ucfirst(str_replace('_', ' ', $h->from_status)) . ' → ' : '' }}
                                                <span class="text-brand-700">{{ ucfirst(str_replace('_', ' ', $h->to_status)) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $h->created_at->format('d M Y H:i') }}</div>
                                            @if($h->note)
                                                <div class="text-sm text-gray-600 mt-1">{{ $h->note }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    @if($booking->estimated_cost || $booking->final_cost)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-3">Biaya</h3>
                            @if($booking->final_cost)
                                <div class="text-xs text-gray-500 uppercase tracking-wider">Final</div>
                                <div class="text-2xl font-bold text-brand-700">Rp {{ number_format($booking->final_cost, 0, ',', '.') }}</div>
                            @elseif($booking->estimated_cost)
                                <div class="text-xs text-gray-500 uppercase tracking-wider">Estimasi</div>
                                <div class="text-2xl font-bold text-gray-700">Rp {{ number_format($booking->estimated_cost, 0, ',', '.') }}</div>
                                <p class="text-xs text-gray-500 mt-2">* Biaya final akan dikonfirmasi setelah layanan selesai.</p>
                            @endif
                        </div>
                    @endif

                    @if($booking->is_cancellable)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                            <h3 class="font-bold text-gray-900">Aksi</h3>
                            <details class="border border-gray-200 rounded-lg">
                                <summary class="px-3 py-2 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">Reschedule</summary>
                                <form method="POST" action="{{ route('dashboard.booking.reschedule', $booking->booking_number) }}" class="p-3 space-y-2 border-t border-gray-200">
                                    @csrf
                                    <input type="datetime-local" name="scheduled_at" required min="{{ now()->addDay()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                    <textarea name="notes" rows="2" placeholder="Alasan reschedule (opsional)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                    <button type="submit" class="w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition">Reschedule</button>
                                </form>
                            </details>
                            <details class="border border-red-200 rounded-lg">
                                <summary class="px-3 py-2 cursor-pointer text-sm font-medium text-red-700 hover:bg-red-50">Batalkan Booking</summary>
                                <form method="POST" action="{{ route('dashboard.booking.cancel', $booking->booking_number) }}" class="p-3 space-y-2 border-t border-red-200">
                                    @csrf
                                    <textarea name="reason" required minlength="5" rows="2" placeholder="Alasan pembatalan" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                    <button type="submit" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition" onclick="return confirm('Yakin membatalkan booking ini?')">Batalkan</button>
                                </form>
                            </details>
                        </div>
                    @endif

                    <a href="{{ route('dashboard.booking.index') }}" class="block w-full text-center px-4 py-2 border border-gray-200 text-gray-700 font-semibold rounded-full hover:bg-gray-50 transition">
                        ← Kembali ke Daftar Booking
                    </a>
                </aside>
            </div>
        </div>
    </section>
@endsection