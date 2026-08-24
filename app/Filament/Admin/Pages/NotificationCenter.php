<?php

namespace App\Filament\Admin\Pages;

use App\Models\Notification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class NotificationCenter extends Page
{
    protected static string $routePath = '/notifications';

    protected string $view = 'filament.admin.pages.notification-center';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $navigationLabel = 'Notifikasi';

    protected static ?string $title = 'Pusat Notifikasi';

    protected static ?int $navigationSort = 0;

    public static function getNavigationBadge(): ?string
    {
        $count = Notification::query()
            ->where('user_id', Auth::id())
            ->unread()
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sistem';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Tandai Semua Dibaca')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => Notification::where('user_id', Auth::id())->unread()->exists())
                ->requiresConfirmation()
                ->modalHeading('Tandai semua notifikasi dibaca?')
                ->action(function () {
                    Notification::where('user_id', Auth::id())
                        ->unread()
                        ->update(['read_at' => now()]);
                    FilamentNotification::make()
                        ->title('Semua notifikasi ditandai dibaca')
                        ->success()
                        ->send();
                }),

            Action::make('clear_all')
                ->label('Hapus Semua')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus semua notifikasi?')
                ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                ->action(function () {
                    Notification::where('user_id', Auth::id())->delete();
                    FilamentNotification::make()
                        ->title('Semua notifikasi dihapus')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getNotifications()
    {
        return Notification::query()
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->limit(50)
            ->get();
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $notificationId)
            ->first();

        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
            FilamentNotification::make()
                ->title('Notifikasi ditandai dibaca')
                ->success()
                ->send();
        }
    }
}