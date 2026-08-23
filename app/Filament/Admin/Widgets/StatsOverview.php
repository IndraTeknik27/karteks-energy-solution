<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->description('Published: '.Product::where('status', 'published')->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Categories', Category::count())
                ->description('Active: '.Category::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info')
                ->icon('heroicon-o-squares-2x2'),

            Stat::make('Total Services', Service::count())
                ->description('Active: '.Service::where('is_active', true)->count())
                ->color('warning')
                ->icon('heroicon-o-wrench-screwdriver'),

            Stat::make('Total Orders', Order::count())
                ->description('Pending: '.Order::whereIn('status', ['pending_payment', 'payment_pending'])->count())
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Customers', User::role('customer')->count())
                ->description('Active: '.User::role('customer')->where('is_active', true)->count())
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Contact Messages', ContactMessage::count())
                ->description('Unread: '.ContactMessage::whereNull('read_at')->count())
                ->color('danger')
                ->icon('heroicon-o-envelope'),
        ];
    }
}