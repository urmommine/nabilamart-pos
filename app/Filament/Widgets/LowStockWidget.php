<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ Produk Stok Menipis';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->active()
                    ->lowStock()
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->description(fn (Product $record): string => $record->sku),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Saat Ini')
                    ->badge()
                    ->color(fn (Product $record): string => 
                        $record->stock <= 0 ? 'danger' : 'warning'
                    ),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Harga Jual')
                    ->money('IDR'),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn (Product $record): string => 
                        route('filament.admin.resources.products.edit', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('Semua stok aman! 🎉')
            ->emptyStateDescription('Tidak ada produk dengan stok menipis.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25]);
    }
}
