<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect_printer')
                ->label('Hubungkan Printer')
                ->icon('heroicon-o-printer')
                ->action(fn() => $this->dispatch('connect-printer'))
                ->color('gray'),
        ];
    }
}
