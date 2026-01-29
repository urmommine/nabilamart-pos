<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama Produk')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('sku')
                ->label('SKU')
                ->requiredMapping()
                ->rules(['required', 'max:50']),
            ImportColumn::make('barcode')
                ->label('Barcode')
                ->rules(['nullable', 'max:50']),
            ImportColumn::make('category')
                ->label('Kategori')
                ->relationship(resolveUsing: function (string $state): ?Category {
                    return Category::firstOrCreate(
                        ['name' => trim($state)],
                        ['is_active' => true]
                    );
                }),
            ImportColumn::make('description')
                ->label('Deskripsi'),
            ImportColumn::make('purchase_price')
                ->label('Harga Beli')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('selling_price')
                ->label('Harga Jual')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('stock')
                ->label('Stok')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:0']),
            ImportColumn::make('min_stock')
                ->label('Stok Minimum')
                ->numeric()
                ->rules(['integer', 'min:0']),
            ImportColumn::make('unlimited_stock')
                ->label('Stok Tak Terbatas')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('track_cost')
                ->label('Lacak Harga Modal')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        // First try to find by SKU
        $product = Product::where('sku', $this->data['sku'])->first();

        if ($product) {
            return $product;
        }

        // Also check by barcode if provided to avoid unique constraint violation
        if (!empty($this->data['barcode'])) {
            $product = Product::where('barcode', $this->data['barcode'])->first();
            if ($product) {
                return $product;
            }
        }

        // Create new product
        return new Product(['sku' => $this->data['sku']]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import produk berhasil dan ' . number_format($import->successful_rows) . ' ' . str('baris')->plural($import->successful_rows) . ' berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diimpor.';
        }

        return $body;
    }
}
