<x-filament-panels::page>
    <x-filament::section>
        <x-slot:heading>
            <div class="flex items-center gap-2">
                <x-heroicon-o-bell class="w-5 h-5" />
                Notifikasi Anda
            </div>
        </x-slot:heading>

        <x-slot:description>
            Daftar notifikasi yang ditujukan untuk akun Anda. Maksimal 50 notifikasi terbaru.
        </x-slot:description>

        @php
            $notifications = $this->getNotifications();
        @endphp

        @if($notifications->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <x-heroicon-o-bell-slash class="mx-auto w-12 h-12 mb-3 text-gray-300" />
                <p>Tidak ada notifikasi saat ini.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($notifications as $notification)
                    <div class="flex items-start gap-3 p-4 rounded-lg border transition {{ $notification->read_at ? 'bg-gray-50 border-gray-200' : 'bg-white border-brand-200 shadow-sm' }}"
                        wire:key="notification-{{ $notification->id }}">
                        <div class="shrink-0 mt-0.5">
                            @if($notification->icon)
                                <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                                    <span class="text-xl">{{ $notification->icon }}</span>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full {{ $notification->read_at ? 'bg-gray-200' : 'bg-brand-100' }} flex items-center justify-center {{ $notification->read_at ? 'text-gray-500' : 'text-brand-600' }}">
                                    <x-heroicon-o-bell class="w-5 h-5" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm text-gray-900 {{ $notification->read_at ? 'font-normal' : '' }}">
                                        {{ $notification->title }}
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>

                                    @if($notification->type)
                                        <span class="inline-block mt-2 text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $notification->type }}</span>
                                    @endif

                                    <div class="text-xs text-gray-400 mt-2">
                                        {{ $notification->created_at?->diffForHumans() }}
                                        @if($notification->read_at)
                                            • Dibaca {{ $notification->read_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>

                                @if(! $notification->read_at)
                                    <span class="shrink-0 inline-block w-2 h-2 bg-brand-500 rounded-full mt-2" title="Belum dibaca"></span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 mt-3">
                                @if($notification->action_url)
                                    <a href="{{ $notification->action_url }}" target="_blank"
                                        class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                        Lihat Detail →
                                    </a>
                                @endif

                                @if(! $notification->read_at)
                                    <button type="button" wire:click="markAsRead({{ $notification->id }})"
                                        class="text-xs font-medium text-gray-600 hover:text-gray-900">
                                        Tandai Dibaca
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>