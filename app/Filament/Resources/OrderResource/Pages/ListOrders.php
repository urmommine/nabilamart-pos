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
            \Filament\Actions\ActionGroup::make([
                Action::make('connect_bluetooth')
                    ->label('Bluetooth')
                    ->icon('heroicon-o-signal')
                    ->action(fn() => $this->dispatch('connect-printer', type: 'bluetooth')),
                Action::make('connect_usb')
                    ->label('USB')
                    ->icon('heroicon-o-bolt')
                    ->action(fn() => $this->dispatch('connect-printer', type: 'usb')),
            ])
                ->label('Hubungkan Printer')
                ->icon('heroicon-o-printer')
                ->button()
                ->color('gray'),
        ];
    }
}
