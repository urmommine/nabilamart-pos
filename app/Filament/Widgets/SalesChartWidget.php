<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan 7 Hari Terakhir';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = Carbon::today()->subDays(6);
        
        $salesData = Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->paid()
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $data = [];
        $labels = [];

        // Fill 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            $labels[] = $date->format('d M');
            $data[] = $salesData[$dateString] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (Rp)',
                    'data' => $data,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.3,
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
                    'display' => false,
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
