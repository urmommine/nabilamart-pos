<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\SalesChartWidget;

class Dashboard extends BaseDashboard
{
    /**
     * Get the widgets that should be displayed on the dashboard.
     * Overrides the default behavior that shows all discovered widgets.
     */
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            SalesChartWidget::class,
            LatestOrdersWidget::class,
            LowStockWidget::class,
        ];
    }
}
