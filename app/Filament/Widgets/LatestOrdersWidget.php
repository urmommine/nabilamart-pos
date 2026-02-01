<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transaksi Terakhir';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->suffix(' item'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Bayar')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris' => 'info',
                        'transfer' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'paid' => '✓ Lunas',
                        'pending' => 'Pending',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('H:i')
                    ->description(fn(Order $record): string => $record->created_at->format('d M Y')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->slideOver()
                    ->infolist(\App\Filament\Resources\OrderResource::getInfolistSchema()),
            ])
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateDescription('Transaksi akan muncul di sini setelah ada penjualan.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->paginated([5, 10, 25]);
    }
}
