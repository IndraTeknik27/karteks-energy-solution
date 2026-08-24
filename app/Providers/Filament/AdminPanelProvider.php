<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->brandName('KARTEKS ENERGY')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                // Primary: hijau emerald = renewable energy / sustainability
                'primary' => Color::Emerald,
                // Secondary: biru teal = teknologi
                'secondary' => Color::Teal,
                // Success: green
                'success' => Color::Green,
                // Warning: amber
                'warning' => Color::Amber,
                // Danger: red
                'danger' => Color::Red,
                // Info: sky
                'info' => Color::Sky,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Katalog')->icon('heroicon-o-squares-2x2'),
                NavigationGroup::make('Penjualan')->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Customer')->icon('heroicon-o-users'),
                NavigationGroup::make('Jasa & Custom')->icon('heroicon-o-wrench-screwdriver'),
                NavigationGroup::make('Konten & CMS')->icon('heroicon-o-document-text'),
                NavigationGroup::make('Marketing')->icon('heroicon-o-megaphone'),
                NavigationGroup::make('Pengaturan')->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make('Sistem')->icon('heroicon-o-server-stack'),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                \App\Filament\Admin\Widgets\StatsOverview::class,
                \App\Filament\Admin\Widgets\RevenueChartWidget::class,
                \App\Filament\Admin\Widgets\OrdersChartWidget::class,
                \App\Filament\Admin\Widgets\LowStockTableWidget::class,
                \App\Filament\Admin\Widgets\TopProductsTableWidget::class,
                \App\Filament\Admin\Widgets\LatestOrdersWidget::class,
                \App\Filament\Admin\Widgets\CustomBatteryStatsWidget::class,
                \App\Filament\Admin\Widgets\ServiceBookingWidget::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // RoleMiddleware aktif di FASE 1 setelah Spatie Permission seeder jalan
                // RoleMiddleware::class.':admin-access',
            ])
            ->login()
            ->registration(false)
            ->passwordReset()
            ->emailVerification()
            ->profile()
            // ->databaseNotifications() — disabled: konflik dengan FASE 4.6 custom Notification model
            // (Illuminate\Notifications\DatabaseNotification expects UUID id + notifiable_type/id,
            //  sedangkan tabel notifications app pakai BIGINT id + user_id direct FK)
            // Gunakan NotificationCenter page dari FASE 4.3 untuk admin UI notifikasi
            ->broadcasting()
            ->favicon('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><path fill=%22%2310b981%22 d=%22M13 10V3L4 14h7v7l9-11h-7z%22/></svg>');
    }
}
