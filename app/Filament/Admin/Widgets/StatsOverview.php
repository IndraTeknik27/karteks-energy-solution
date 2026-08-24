<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $service = app(DashboardService::class);
        $stats = $service->statsOverview(30);

        $result = [];
        foreach ($stats as $stat) {
            $value = $stat['value'];
            $valueFormatted = match ($stat['format']) {
                'currency' => 'Rp '.number_format($value, 0, ',', '.'),
                default => number_format($value, 0, ',', '.'),
            };

            $change = $stat['change'];
            $changeIcon = $change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
            $changeColor = $change >= 0 ? 'success' : 'danger';
            $changeText = ($change >= 0 ? '+' : '').$change.'%';

            $statObj = Stat::make($stat['label'], $valueFormatted)
                ->description($stat['description'].' • '.$changeText.' vs kemarin')
                ->descriptionIcon($changeIcon)
                ->color($changeColor)
                ->icon($stat['icon']);

            // Add sparkline if available
            if (! empty($stat['sparkline']) && count($stat['sparkline']) > 1) {
                $statObj->chart($stat['sparkline']);
            }

            $result[] = $statObj;
        }

        return $result;
    }
}