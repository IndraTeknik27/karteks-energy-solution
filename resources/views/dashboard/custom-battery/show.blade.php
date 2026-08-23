@extends('layouts.app')

@section('title', 'Detail ' . $request->request_number . ' - KARTEKS')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('dashboard.custom-battery.index') }}" class="hover:text-brand-600">Custom Battery</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">{{ $request->request_number }}</span>
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

            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Request Number</div>
                        <div class="text-2xl font-bold text-gray-900 font-mono">{{ $request->request_number }}</div>
                        <div class="text-sm text-gray-500 mt-1">Disubmit: {{ $request->created_at->format('d F Y, H:i') }} WITA</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-4 py-2 text-sm rounded-full font-bold
                            @switch($request->status)
                                @case('submitted') bg-blue-100 text-blue-700 @break
                                @case('under_review') bg-amber-100 text-amber-700 @break
                                @case('revision_requested') bg-orange-100 text-orange-700 @break
                                @case('quoted') bg-purple-100 text-purple-700 @break
                                @case('approved') bg-brand-100 text-brand-700 @break
                                @case('rejected') bg-red-100 text-red-700 @break
                                @case('in_production') bg-cyan-100 text-cyan-700 @break
                                @case('completed') bg-green-100 text-green-700 @break
                                @case('cancelled') bg-gray-100 text-gray-700 @break
                            @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                        @if($request->deadline)
                            <div class="text-xs text-gray-500 mt-2">Deadline: {{ $request->deadline->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Spesifikasi</h2>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-gray-500">Kimia</dt><dd class="font-semibold">{{ $request->chemistry }}</dd></div>
                            <div><dt class="text-gray-500">Voltase</dt><dd class="font-semibold">{{ $request->voltage }}</dd></div>
                            <div><dt class="text-gray-500">Kapasitas</dt><dd class="font-semibold">{{ $request->capacity ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500">Energi</dt><dd class="font-semibold">{{ $request->kwh ? $request->kwh.' kWh' : '—' }}</dd></div>
                            <div><dt class="text-gray-500">Aplikasi</dt><dd class="font-semibold">{{ config('karteks.battery_options.applications.'.$request->application) ?? $request->application }}</dd></div>
                            <div><dt class="text-gray-500">Jumlah</dt><dd class="font-semibold">{{ $request->quantity }} unit</dd></div>
                            <div><dt class="text-gray-500">Load</dt><dd class="font-semibold">{{ $request->current_load ?? '—' }}</dd></div>
                            <div>
                                <dt class="text-gray-500">Dimensi</dt>
                                <dd class="font-semibold">
                                    @if($request->dimensions)
                                        {{ $request->dimensions['length'] ?? '?' }} × {{ $request->dimensions['width'] ?? '?' }} × {{ $request->dimensions['height'] ?? '?' }} mm
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-3">Deskripsi Kebutuhan</h2>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $request->description }}</p>
                        @if($request->customer_notes)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h3 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Catatan Customer</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $request->customer_notes }}</p>
                            </div>
                        @endif
                        @if($request->admin_notes)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h3 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Catatan Tim KARTEKS</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $request->admin_notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($request->revisions->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Riwayat Revisi</h2>
                            <div class="space-y-3">
                                @foreach($request->revisions->sortBy('revision_number') as $rev)
                                    <div class="border border-gray-100 rounded-lg p-4
                                        @if($rev->status === 'pending') bg-amber-50 border-amber-200
                                        @elseif($rev->status === 'responded') bg-blue-50 border-blue-200
                                        @else bg-gray-50 @endif">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-semibold text-gray-900">Revisi #{{ $rev->revision_number }}</span>
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                                @if($rev->status === 'pending') bg-amber-200 text-amber-800
                                                @elseif($rev->status === 'responded') bg-blue-200 text-blue-800
                                                @else bg-green-200 text-green-800 @endif">
                                                {{ ucfirst($rev->status) }}
                                            </span>
                                        </div>
                                        @if($rev->admin_note)
                                            <div class="text-sm text-gray-700 mb-2"><strong>Catatan admin:</strong> {{ $rev->admin_note }}</div>
                                        @endif
                                        @if($rev->customer_response)
                                            <div class="text-sm text-gray-700 mb-2"><strong>Tanggapan Anda:</strong> {{ $rev->customer_response }}</div>
                                        @elseif($rev->status === 'pending')
                                            <form method="POST" action="{{ route('dashboard.custom-battery.revision.respond', [$request->request_number, $rev->id]) }}" class="mt-3 space-y-2">
                                                @csrf
                                                <textarea name="customer_response" required minlength="5" rows="3" placeholder="Tanggapi revisi ini..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                                                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition">Kirim Tanggapan</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($request->files->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-bold text-gray-900 mb-4">Lampiran ({{ $request->files->count() }})</h2>
                            <div class="space-y-2">
                                @foreach($request->files as $file)
                                    <div class="flex items-center justify-between gap-3 p-3 border border-gray-100 rounded-lg">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 bg-gray-50 rounded flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $file->original_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $file->size_human }} • {{ $file->uploaded_at?->format('d M Y H:i') }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ $file->url }}" target="_blank" class="text-xs text-brand-600 hover:text-brand-700 font-semibold whitespace-nowrap">Download</a>
                                    </div>
                                @endforeach
                            </div>

                            @if(in_array($request->status, ['submitted', 'revision_requested'], true))
                                <form method="POST" action="{{ route('dashboard.custom-battery.file.upload', $request->request_number) }}" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-gray-100">
                                    @csrf
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Upload Lampiran Baru</label>
                                    <div class="flex gap-2">
                                        <input type="file" name="file" required class="flex-1 text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-l-lg file:border-0 file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-gray-200 rounded-lg">
                                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition whitespace-nowrap">Upload</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    @if($request->estimated_price || $request->final_price)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-3">Quotation</h3>
                            @if($request->estimated_price)
                                <div class="flex justify-between text-sm mb-2"><dt class="text-gray-600">Estimasi</dt><dd class="font-bold text-brand-700">Rp {{ number_format($request->estimated_price, 0, ',', '.') }}</dd></div>
                            @endif
                            @if($request->final_price)
                                <div class="flex justify-between text-sm"><dt class="text-gray-600">Final</dt><dd class="font-bold text-brand-700">Rp {{ number_format($request->final_price, 0, ',', '.') }}</dd></div>
                            @endif
                        </div>
                    @endif

                    @if(in_array($request->status, ['submitted', 'under_review', 'revision_requested', 'quoted'], true))
                        <div class="bg-white rounded-2xl border border-red-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-2">Batalkan Request</h3>
                            <form method="POST" action="{{ route('dashboard.custom-battery.cancel', $request->request_number) }}" onsubmit="return confirm('Yakin ingin membatalkan request ini?')">
                                @csrf
                                <textarea name="reason" required minlength="5" placeholder="Alasan pembatalan..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 mb-2"></textarea>
                                <button type="submit" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">Batalkan Request</button>
                            </form>
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-3">Timeline</h3>
                        <div class="space-y-2 text-xs">
                            <div class="flex gap-2"><span class="w-2 h-2 bg-brand-500 rounded-full mt-1.5 shrink-0"></span><span class="text-gray-700">Submitted: {{ $request->created_at->format('d M Y H:i') }}</span></div>
                            @if($request->assigned_at)
                                <div class="flex gap-2"><span class="w-2 h-2 bg-amber-500 rounded-full mt-1.5 shrink-0"></span><span class="text-gray-700">In Review: {{ $request->assigned_at->format('d M Y H:i') }}</span></div>
                            @endif
                            @if($request->quoted_at)
                                <div class="flex gap-2"><span class="w-2 h-2 bg-purple-500 rounded-full mt-1.5 shrink-0"></span><span class="text-gray-700">Quoted: {{ $request->quoted_at->format('d M Y H:i') }}</span></div>
                            @endif
                            @if($request->approved_at)
                                <div class="flex gap-2"><span class="w-2 h-2 bg-brand-600 rounded-full mt-1.5 shrink-0"></span><span class="text-gray-700">Approved: {{ $request->approved_at->format('d M Y H:i') }}</span></div>
                            @endif
                            @if($request->completed_at)
                                <div class="flex gap-2"><span class="w-2 h-2 bg-green-500 rounded-full mt-1.5 shrink-0"></span><span class="text-gray-700">Completed: {{ $request->completed_at->format('d M Y H:i') }}</span></div>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection