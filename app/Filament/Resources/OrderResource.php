<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Riwayat Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('No. Invoice')
                            ->disabled(),
                        Forms\Components\TextInput::make('user.name')
                            ->label('Kasir')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total')
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Metode Bayar')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_status')
                            ->label('Status')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Bayar')
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
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Bayar')
                    ->options([
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->action(function (Order $record, \Filament\Tables\Actions\Action $action) {
                        $record->load(['items', 'user']);

                        $storeSettings = [
                            'name' => \App\Models\StoreSetting::get(\App\Models\StoreSetting::STORE_NAME, 'POS Store'),
                            'address' => \App\Models\StoreSetting::get(\App\Models\StoreSetting::STORE_ADDRESS, ''),
                            'phone' => \App\Models\StoreSetting::get(\App\Models\StoreSetting::STORE_PHONE, ''),
                            'footer' => \App\Models\StoreSetting::get(\App\Models\StoreSetting::RECEIPT_FOOTER, 'Terima Kasih!'),
                        ];

                        $items = $record->items->map(function ($item) {
                            return [
                                'name' => $item->product_name,
                                'qty' => $item->quantity,
                                'price' => $item->unit_price,
                                'total' => $item->total_price,
                            ];
                        })->toArray();

                        $receiptData = [
                            'storeName' => $storeSettings['name'],
                            'storeAddress' => $storeSettings['address'],
                            'storePhone' => $storeSettings['phone'],
                            'footer' => $storeSettings['footer'],
                            'invoice' => $record->invoice_number,
                            'date' => $record->created_at->format('d/m/Y H:i'),
                            'cashier' => $record->user->name ?? '-',
                            'customer' => $record->customer->name ?? 'Walk-in Customer',
                            'items' => $items,
                            'subtotal' => $record->subtotal,
                            'discount' => $record->discount,
                            'tax' => $record->tax,
                            'total' => $record->total_amount,
                            'amount_paid' => $record->amount_paid,
                            'change' => $record->change,
                            'payment_method' => $record->payment_method,
                        ];

                        $action->getLivewire()->dispatch('print-invoice', data: $receiptData);
                    })
                    ->color('success'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_number')
                            ->label('No. Invoice'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Kasir'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal')
                            ->dateTime('d M Y H:i:s'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Metode Bayar')
                            ->badge(),
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Status')
                            ->badge(),
                    ])->columns(3),

                Infolists\Components\Section::make('Rincian Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('discount')
                            ->label('Diskon')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('tax')
                            ->label('Pajak')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total')
                            ->money('IDR')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('amount_paid')
                            ->label('Dibayar')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('change')
                            ->label('Kembalian')
                            ->money('IDR'),
                    ])->columns(3),

                Infolists\Components\Section::make('Item Transaksi')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produk'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Harga')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('total_price')
                                    ->label('Total')
                                    ->money('IDR'),
                            ])->columns(4),
                    ]),

                Infolists\Components\Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Tidak ada catatan'),
                    ])->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Orders are created from POS terminal
    }
}
