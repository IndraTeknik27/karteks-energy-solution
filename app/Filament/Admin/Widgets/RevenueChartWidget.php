<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Pendapatan 30 Hari';

    protected ?string $description = 'Total revenue dari order paid/completed (IDR)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = (int) ($this->filter ?: 30);
        $days = in_array($days, [7, 14, 30, 90]) ? $days : 30;

        $service = app(DashboardService::class);
        $values = $service->dailyRevenueSeries($days);

        $labels = [];
        $start = Carbon::today()->subDays($days - 1);
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $values,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<scalar, scalar>|null
     */
    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '14' => '14 Hari',
            '30' => '30 Hari',
            '90' => '90 Hari',
        ];
    }
}