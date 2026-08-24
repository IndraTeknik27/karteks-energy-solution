<?php

namespace App\Services\V1;

use App\Models\CustomBatteryRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\ServiceBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct() {}

    // ---- Stats Overview ----

    /**
     * Stats overview cards: today's revenue, today's orders, new customers, AOV.
     * Returns array of [label, value, change_pct, sparkline_data, ...]
     */
    public function statsOverview(int $days = 30): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $daysAgo = $today->copy()->subDays($days);

        $todayRevenue = $this->revenueForDate($today);
        $yesterdayRevenue = $this->revenueForDate($yesterday);

        $todayOrders = Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereDate('created_at', $today)
            ->count();
        $yesterdayOrders = Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereDate('created_at', $yesterday)
            ->count();

        $todayCustomers = User::query()
            ->where('created_at', '>=', $today)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'staff']);
            })
            ->count();
        $yesterdayCustomers = User::query()
            ->whereBetween('created_at', [$yesterday, $today])
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'staff']);
            })
            ->count();

        // AOV (Average Order Value) for paid orders in period
        $aov = $this->aovForPeriod($daysAgo, $today);
        $aovPrev = $this->aovForPeriod($daysAgo->copy()->subDays($days), $daysAgo);

        // Daily revenue sparkline
        $sparklineRevenue = $this->dailyRevenueSeries($days);

        return [
            [
                'label' => 'Pendapatan Hari Ini',
                'value' => $todayRevenue,
                'format' => 'currency',
                'change' => $this->percentChange($todayRevenue, $yesterdayRevenue),
                'sparkline' => $sparklineRevenue,
                'icon' => 'heroicon-o-banknotes',
                'color' => 'success',
                'description' => 'Order paid/completed',
            ],
            [
                'label' => 'Order Paid Hari Ini',
                'value' => $todayOrders,
                'format' => 'integer',
                'change' => $this->percentChange($todayOrders, $yesterdayOrders),
                'sparkline' => $this->dailyOrderCountSeries($days),
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'info',
                'description' => 'Total '.$todayOrders.' order paid',
            ],
            [
                'label' => 'Customer Baru',
                'value' => $todayCustomers,
                'format' => 'integer',
                'change' => $this->percentChange($todayCustomers, $yesterdayCustomers),
                'sparkline' => $this->dailyCustomerSeries($days),
                'icon' => 'heroicon-o-user-plus',
                'color' => 'warning',
                'description' => 'Hari ini',
            ],
            [
                'label' => 'Average Order Value',
                'value' => $aov,
                'format' => 'currency',
                'change' => $this->percentChange($aov, $aovPrev),
                'sparkline' => $this->dailyAovSeries($days),
                'icon' => 'heroicon-o-presentation-chart-line',
                'color' => 'primary',
                'description' => "Rata-rata {$days} hari",
            ],
        ];
    }

    // ---- Revenue Chart ----

    /**
     * Revenue series per day for N days (paid/completed orders).
     */
    public function dailyRevenueSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today()->endOfDay();

        $orders = Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = (float) ($orders[$date] ?? 0);
        }
        return $series;
    }

    public function dailyOrderCountSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today()->endOfDay();

        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = (int) ($orders[$date] ?? 0);
        }
        return $series;
    }

    public function dailyCustomerSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today()->endOfDay();

        $users = User::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'staff']);
            })
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = (int) ($users[$date] ?? 0);
        }
        return $series;
    }

    public function dailyAovSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today()->endOfDay();

        $orders = Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as date, AVG(total) as aov')
            ->groupBy('date')
            ->pluck('aov', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = round((float) ($orders[$date] ?? 0), 2);
        }
        return $series;
    }

    /**
     * Order status distribution untuk N days.
     */
    public function ordersByStatus(int $days = 14): array
    {
        $start = Carbon::today()->subDays($days - 1)->startOfDay();

        $orders = Order::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statuses = [
            'pending_payment' => 'Pending',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $result = [];
        foreach ($statuses as $key => $label) {
            $result[$label] = (int) ($orders[$key] ?? 0);
        }
        return $result;
    }

    // ---- Low Stock ----

    public function lowStockProducts(int $limit = 10): Collection
    {
        return Product::query()
            ->where('status', 'published')
            ->where('manage_stock', true)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('low_stock_threshold')
                        ->whereColumn('stock_qty', '<=', 'low_stock_threshold');
                })->orWhere('stock_qty', '<=', 0);
            })
            ->orderBy('stock_qty', 'asc')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'sku', 'stock_qty', 'low_stock_threshold', 'price', 'sale_price']);
    }

    // ---- Top Products ----

    public function topProducts(int $days = 30, int $limit = 10): Collection
    {
        $start = Carbon::today()->subDays($days - 1)->startOfDay();

        // Use sales_count sebagai fallback (lifetime), tapi prefer order_item dalam N hari
        $topByRecentOrders = \App\Models\OrderItem::query()
            ->where('itemable_type', \App\Models\Product::class)
            ->whereHas('order', function ($q) use ($start) {
                $q->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
                  ->where('created_at', '>=', $start);
            })
            ->selectRaw('itemable_id, SUM(qty) as total_qty, SUM(qty * price) as total_revenue')
            ->groupBy('itemable_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        if ($topByRecentOrders->isEmpty()) {
            // Fallback ke lifetime sales_count
            return Product::query()
                ->where('status', 'published')
                ->orderByDesc('sales_count')
                ->limit($limit)
                ->get(['id', 'name', 'slug', 'price', 'sale_price', 'sales_count', 'rating_avg']);
        }

        $productIds = $topByRecentOrders->pluck('itemable_id')->all();
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'name', 'slug', 'price', 'sale_price', 'sales_count', 'rating_avg'])
            ->keyBy('id');

        return $topByRecentOrders->map(function ($row) use ($products) {
            $row->product = $products[$row->itemable_id] ?? null;
            return $row;
        })->filter(fn ($r) => $r->product !== null);
    }

    // ---- Latest Orders ----

    public function latestOrders(int $limit = 10): Collection
    {
        return Order::query()
            ->with('customer:id,name,email')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    // ---- Custom Battery Stats ----

    public function customBatteryStats(): array
    {
        $base = CustomBatteryRequest::query();

        return [
            'total_open' => (clone $base)->whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count(),
            'submitted' => (clone $base)->where('status', 'submitted')->count(),
            'under_review' => (clone $base)->where('status', 'under_review')->count(),
            'revision_requested' => (clone $base)->where('status', 'revision_requested')->count(),
            'quoted' => (clone $base)->where('status', 'quoted')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'in_production' => (clone $base)->where('status', 'in_production')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'pending_revenue' => (clone $base)->whereIn('status', ['quoted', 'approved'])->sum('estimated_price'),
            'completed_revenue' => (clone $base)->where('status', 'completed')->sum('final_price'),
        ];
    }

    public function serviceBookingStats(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $base = ServiceBooking::query();

        return [
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'today' => (clone $base)->whereDate('scheduled_at', $today)->count(),
            'this_week' => (clone $base)->where('scheduled_at', '>=', $weekStart)->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
        ];
    }

    public function quotationStats(): array
    {
        $base = Quotation::query();
        return [
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'sent' => (clone $base)->where('status', 'sent')->count(),
            'viewed' => (clone $base)->where('status', 'viewed')->count(),
            'accepted' => (clone $base)->where('status', 'accepted')->count(),
            'pending_value' => (clone $base)->whereIn('status', ['sent', 'viewed'])->sum('total'),
            'accepted_value' => (clone $base)->where('status', 'accepted')->sum('total'),
        ];
    }

    // ---- Helpers ----

    protected function revenueForDate(Carbon $date): float
    {
        return (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereDate('paid_at', $date)
            ->sum('total');
    }

    protected function aovForPeriod(Carbon $start, Carbon $end): float
    {
        $result = Order::query()
            ->whereIn('status', ['paid', 'processing', 'ready_to_ship', 'shipped', 'delivered', 'completed'])
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('SUM(total) as sum, COUNT(*) as count')
            ->first();
        if (! $result || ! $result->count) {
            return 0.0;
        }
        return (float) $result->sum / (int) $result->count;
    }

    protected function percentChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}