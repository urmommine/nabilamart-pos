<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('dashboard_stats', now()->addMinutes(1), function () {
            // Today's sales
            $todaySales = Order::today()->paid()->sum('total_amount');
            $yesterdaySales = Order::whereDate('created_at', Carbon::yesterday())->paid()->sum('total_amount');
            $salesChange = $yesterdaySales > 0
                ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
                : 100;

            // Today's transactions
            $todayTransactions = Order::today()->paid()->count();
            $yesterdayTransactions = Order::whereDate('created_at', Carbon::yesterday())->paid()->count();

            // Active products
            $activeProducts = Product::active()->count();

            // Low stock products
            $lowStockCount = Product::active()->lowStock()->count();

            // Total Income
            $totalIncome = Order::paid()->sum('total_amount');

            // Net Profit
            // Calculate profit based on current purchase price for tracked products
            $netProfit = OrderItem::whereHas('order', function ($query) {
                $query->where('payment_status', 'paid');
            })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.track_cost', true)
                ->sum(DB::raw('(order_items.unit_price - products.purchase_price) * order_items.quantity'));

            return [
                Stat::make('Total Pendapatan', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                    ->description('Semua transaksi berhasil')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                Stat::make('Keuntungan Bersih', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                    ->description('Profit dari produk (Track Cost)')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),

                Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                    ->description($salesChange >= 0 ? '+' . number_format($salesChange, 1) . '%' : number_format($salesChange, 1) . '%')
                    ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($salesChange >= 0 ? 'success' : 'danger')
                    ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

                Stat::make('Transaksi Hari Ini', $todayTransactions)
                    ->description('Kemarin: ' . $yesterdayTransactions)
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('info'),

                Stat::make('Total Produk Aktif', $activeProducts)
                    ->description('Produk tersedia')
                    ->descriptionIcon('heroicon-m-cube')
                    ->color('primary'),

                Stat::make('Stok Menipis', $lowStockCount)
                    ->description($lowStockCount > 0 ? 'Perlu restock!' : 'Semua stok aman')
                    ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                    ->color($lowStockCount > 0 ? 'warning' : 'success')
                    ->url($lowStockCount > 0 ? route('filament.admin.resources.products.index', ['tableFilters[low_stock][value]' => 1]) : null),
            ];
        });
    }
}
