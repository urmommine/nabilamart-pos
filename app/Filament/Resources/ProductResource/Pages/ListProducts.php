<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray'),
            Actions\CreateAction::make(),
        ];
    }
}
