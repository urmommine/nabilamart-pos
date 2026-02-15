<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.analytics';

    protected static ?string $navigationLabel = 'Analisis';

    protected static ?string $title = 'Analisis & Laporan';

    protected static ?int $navigationSort = 2;

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PeriodComparisonStats::class,
            \App\Filament\Widgets\RevenueProfitChart::class,
            \App\Filament\Widgets\TopProductsChart::class,
            \App\Filament\Widgets\CategorySalesChart::class,
        ];
    }
}
