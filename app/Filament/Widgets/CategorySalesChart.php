<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategorySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan per Kategori';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('categories.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $data->pluck('total_revenue')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#ef4444', // red
                        '#fbbf24', // amber
                        '#10b981', // emerald
                        '#8b5cf6', // violet
                        '#ec4899', // pink
                        '#6366f1', // indigo
                        '#14b8a6', // teal
                    ],
                ],
            ],
            'labels' => $data->map(fn($item) => $item->name . ' (' . $item->total_quantity . ' item)')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
