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
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('sku')
                ->label('SKU')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:50']),
            ImportColumn::make('barcode')
                ->label('Barcode')
                ->rules(['nullable', 'string', 'max:150']),
            ImportColumn::make('category')
                ->label('Kategori')
                ->relationship(resolveUsing: function (?string $state): ?Category {
                    if (blank($state)) {
                        return null;
                    }

                    return Category::firstOrCreate(
                        ['name' => trim($state)],
                        ['is_active' => true]
                    );
                }),
            ImportColumn::make('description')
                ->label('Deskripsi')
                ->rules(['nullable', 'string']),
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
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('unlimited_stock')
                ->label('Stok Tak Terbatas')
                ->boolean()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('track_cost')
                ->label('Lacak Harga Modal')
                ->boolean()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $sku = trim($this->data['sku'] ?? '');

        // SKU is required — skip this row if missing
        if (blank($sku)) {
            return null;
        }

        // First try to find by SKU
        $product = Product::where('sku', $sku)->first();

        if ($product) {
            return $product;
        }

        // Also check by barcode if provided to avoid unique constraint violation
        $barcode = trim($this->data['barcode'] ?? '');
        if (filled($barcode)) {
            $product = Product::where('barcode', $barcode)->first();
            if ($product) {
                return $product;
            }
        }

        // Create new product
        return new Product(['sku' => $sku]);
    }

    protected function beforeSave(): void
    {
        // Ensure safe defaults for optional columns when missing/null
        $record = $this->record;

        if (is_null($record->min_stock)) {
            $record->min_stock = 5;
        }

        if (is_null($record->unlimited_stock)) {
            $record->unlimited_stock = false;
        }

        if (is_null($record->track_cost)) {
            $record->track_cost = true;
        }

        if (is_null($record->is_active)) {
            $record->is_active = true;
        }

        // Clean empty barcode to null to avoid empty-string unique constraint issues
        if (blank($record->barcode)) {
            $record->barcode = null;
        }
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
