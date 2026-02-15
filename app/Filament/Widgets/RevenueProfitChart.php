<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueProfitChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan vs Keuntungan (30 Hari)';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = Carbon::today()->subDays(29);

        // Revenue Data
        $revenueData = Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->paid()
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Profit Data
        // Calculate profit based on current purchase price for tracked products
        $profitDataQuery = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('products.track_cost', true)
            ->whereDate('orders.created_at', '>=', $startDate)
            ->selectRaw('DATE(orders.created_at) as date, SUM((order_items.unit_price - products.purchase_price) * order_items.quantity) as profit')
            ->groupBy('date')
            ->get();

        $profitData = $profitDataQuery->pluck('profit', 'date')->toArray();

        $datasets = [
            'revenue' => [],
            'profit' => [],
        ];
        $labels = [];

        // Fill 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->format('Y-m-d');

            $labels[] = $date->format('d M');
            $datasets['revenue'][] = $revenueData[$dateString] ?? 0;
            $datasets['profit'][] = $profitData[$dateString] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $datasets['revenue'],
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Keuntungan Bersih',
                    'data' => $datasets['profit'],
                    'borderColor' => '#10b981', // emerald-500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }",
                    ],
                ],
            ],
        ];
    }
}
