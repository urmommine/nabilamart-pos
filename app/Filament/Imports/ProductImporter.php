<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;

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
                        ['name' => str($state)->trim()->headline()->toString()],
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
        if (blank($sku)) {
            return null;
        }

        $barcode = trim($this->data['barcode'] ?? '');

        // 1. Find by identifiers
        $productBySku = Product::where('sku', $sku)->first();
        $productByBarcode = filled($barcode) ? Product::where('barcode', $barcode)->first() : null;

        // 2. Conflict Handling: If SKU is taken by another product
        if ($productBySku) {
            // A. Row has a barcode that matches a DIFFERENT product
            if ($productByBarcode && $productBySku->id !== $productByBarcode->id) {
                $sku = $this->generateUniqueSku($sku);
                $this->data['sku'] = $sku;
                return $productByBarcode;
            }

            // B. Row has a NEW barcode - user likely wants a new product with a variation of the SKU
            if (!$productByBarcode && filled($barcode)) {
                $sku = $this->generateUniqueSku($sku);
                $this->data['sku'] = $sku;
                return new Product(['sku' => $sku]);
            }
        }

        // 3. Standard Resolution
        if ($productBySku) {
            return $productBySku;
        }

        if ($productByBarcode) {
            return $productByBarcode;
        }

        // 4. Create new
        return new Product(['sku' => $sku]);
    }

    private function generateUniqueSku(string $sku): string
    {
        $originalSku = $sku;
        $counter = 1;

        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter++;
        }

        return $sku;
    }

    protected function beforeSave(): void
    {
        $record = $this->record;

        // 1. Clean and Normalize Basic Data
        $record->name = str($record->name)->trim()->toString();
        $record->sku = str($record->sku)->trim()->toString();

        // 2. Validation: Prevent $0 selling price which is usually a mistake
        if ($record->selling_price <= 0) {
            throw new RowImportFailedException("Harga jual tidak boleh 0 atau kurang.");
        }

        // 3. Smart Dependency: If purchase price is provided, ensure track_cost is true
        if ($record->purchase_price > 0) {
            $record->track_cost = true;
        }

        // 4. Cleanup: If unlimited stock, don't allow numeric stock values
        if ($record->unlimited_stock) {
            $record->stock = 0;
            $record->min_stock = null;
        }

        // 5. Ensure safe defaults
        if (is_null($record->min_stock) && !$record->unlimited_stock) {
            $record->min_stock = 5;
        }

        if (is_null($record->unlimited_stock)) {
            $record->unlimited_stock = false;
        }

        if (is_null($record->track_cost)) {
            $record->track_cost = isset($record->purchase_price) && $record->purchase_price > 0;
        }

        if (is_null($record->is_active)) {
            $record->is_active = true;
        }

        // 6. Clean empty barcode
        if (blank($record->barcode)) {
            $record->barcode = null;
        } else {
            $record->barcode = str($record->barcode)->trim()->toString();
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
