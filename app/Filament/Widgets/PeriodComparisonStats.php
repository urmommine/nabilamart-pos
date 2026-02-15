<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PeriodComparisonStats extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Revenue
        $revenueThisMonth = Order::query()
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->paid()
            ->sum('total_amount');

        $revenueLastMonth = Order::query()
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->paid()
            ->sum('total_amount');

        $revenueChange = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Profit
        $profitThisMonth = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('products.track_cost', true)
            ->whereBetween('orders.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum(DB::raw('(order_items.unit_price - products.purchase_price) * order_items.quantity'));

        $profitLastMonth = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('products.track_cost', true)
            ->whereBetween('orders.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum(DB::raw('(order_items.unit_price - products.purchase_price) * order_items.quantity'));

        $profitChange = $profitLastMonth > 0
            ? (($profitThisMonth - $profitLastMonth) / $profitLastMonth) * 100
            : ($profitThisMonth > 0 ? 100 : 0);

        // Orders Count
        $ordersThisMonth = Order::query()
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->paid()
            ->count();

        $ordersLastMonth = Order::query()
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->paid()
            ->count();

        $ordersChange = $ordersLastMonth > 0
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100
            : ($ordersThisMonth > 0 ? 100 : 0);

        return [
            Stat::make('Pendapatan (Bulan Ini)', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '% vs bulan lalu' : number_format($revenueChange, 1) . '% vs bulan lalu')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Keuntungan Bersih (Bulan Ini)', 'Rp ' . number_format($profitThisMonth, 0, ',', '.'))
                ->description($profitChange >= 0 ? '+' . number_format($profitChange, 1) . '% vs bulan lalu' : number_format($profitChange, 1) . '% vs bulan lalu')
                ->descriptionIcon($profitChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profitChange >= 0 ? 'success' : 'danger'),

            Stat::make('Pesanan (Bulan Ini)', $ordersThisMonth)
                ->description($ordersChange >= 0 ? '+' . number_format($ordersChange, 1) . '% vs bulan lalu' : number_format($ordersChange, 1) . '% vs bulan lalu')
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger'),
        ];
    }
}
