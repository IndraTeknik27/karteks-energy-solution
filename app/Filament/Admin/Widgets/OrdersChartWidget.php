<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Filament\Widgets\ChartWidget;

class OrdersChartWidget extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Order 14 Hari (by Status)';

    protected ?string $description = 'Distribusi order berdasarkan status';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public ?string $filter = '14';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = (int) ($this->filter ?: 14);
        $days = in_array($days, [7, 14, 30]) ? $days : 14;

        $service = app(DashboardService::class);
        $data = $service->ordersByStatus($days);

        $colors = [
            'Pending' => '#f59e0b',
            'Paid' => '#3b82f6',
            'Processing' => '#0ea5e9',
            'Shipped' => '#10b981',
            'Delivered' => '#22c55e',
            'Completed' => '#16a34a',
            'Cancelled' => '#ef4444',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Order',
                    'data' => array_values($data),
                    'backgroundColor' => array_map(fn ($label) => $colors[$label] ?? '#94a3b8', array_keys($data)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($data),
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
        ];
    }
}