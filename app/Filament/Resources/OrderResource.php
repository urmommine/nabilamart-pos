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
use Illuminate\Support\HtmlString; // Pastikan import ini ada di bagian atas file

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
                            ,
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Bayar')
                            ->options([
                                'cash' => 'Tunai',
                                'qris' => 'QRIS',
                                'transfer' => 'Transfer',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Status')
                            ->options([
                                'paid' => 'Lunas',
                                'pending' => 'Pending',
                                'cancelled' => 'Dibatalkan',
'unpaid' => 'Belum Bayar',
        'debt' => 'Hutang',
                            ])
			    ->native(false)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
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
    ->sortable()    
    ->color(fn(string $state): string => match ($state) {
        'paid' => 'success',
        'pending' => 'warning',
        'cancelled' => 'danger',
        'unpaid' => 'gray',   // Biasanya abu-abu karena belum ada aksi
        'debt' => 'danger',   // Atau 'warning' jika ingin warna oranye
        default => 'gray',
    })
    ->formatStateUsing(fn(string $state): string => match ($state) {
        'paid' => 'Lunas',
        'pending' => 'Pending',
        'cancelled' => 'Dibatalkan',
        'unpaid' => 'Belum Bayar',
        'debt' => 'Hutang',
        default => ucfirst($state), // Mengubah huruf pertama jadi kapital jika tidak ada match
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
'unpaid' => 'Belum Bayar',
        'debt' => 'Hutang',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->slideOver(),
                Tables\Actions\EditAction::make(),
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
            ->schema(self::getInfolistSchema());
    }

    public static function getInfolistSchema(): array
    {
        return [
            Infolists\Components\Grid::make(2)
                ->schema([
                    Infolists\Components\Group::make([
                        Infolists\Components\Section::make('Informasi Transaksi')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice_number')
                                    ->label('No. Invoice')
                                    ->weight('bold')
                                    ->copyable()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Kasir')
                                    ->icon('heroicon-m-user'),
                                Infolists\Components\TextEntry::make('customer.name')
                                    ->label('Pelanggan')
                                    ->placeholder('Walk-in Customer')
                                    ->icon('heroicon-m-user-group'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu Transaksi')
                                    ->dateTime('d M Y, H:i:s')
                                    ->icon('heroicon-m-clock'),
                            ])->columns(2),

                        Infolists\Components\Section::make('Status & Pembayaran')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                'paid' => 'success',
                'pending' => 'warning',
                'cancelled' => 'danger',
                'unpaid' => 'gray',
                'debt' => 'danger',
                default => 'gray',
            })
            ->formatStateUsing(fn(string $state): string => match ($state) {
                'paid' => 'Lunas',
                'pending' => 'Pending',
                'cancelled' => 'Dibatalkan',
                'unpaid' => 'Belum Bayar',
                'debt' => 'Hutang',
                default => ucfirst($state),
            }),
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('Metode Pembayaran')
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
                            ])->columns(2),
                    ]),

                    Infolists\Components\Group::make([
                        Infolists\Components\Section::make('Ringkasan Biaya')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('discount')
                                    ->label('Diskon')
                                    ->money('IDR')
                                    ->color('danger'),
                                Infolists\Components\TextEntry::make('tax')
                                    ->label('Pajak')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('total_amount')
    ->label('Total Akhir')
    ->money('IDR', locale: 'id')
    ->weight('black')
    ->size('lg')
    ->color(fn ($record): string => $record->amount_paid < $record->total_amount ? 'danger' : 'success'),

Infolists\Components\TextEntry::make('amount_paid')
    ->label('Jumlah Bayar')
    ->money('IDR', locale: 'id')
    ->weight('bold')
    ->color(fn ($record): string => $record->amount_paid < $record->total_amount ? 'danger' : 'success')
    // Opsional: Menambahkan info sisa kurangnya jika belum lunas
    ->helperText(fn ($record): HtmlString => 
        $record->amount_paid < $record->total_amount 
            ? new HtmlString('<span class="text-danger-600 font-bold dark:text-danger-400">Kurang: Rp ' . number_format($record->total_amount - $record->amount_paid, 0, ',', '.') . '</span>')
            : new HtmlString('<span class="text-success-600 font-bold dark:text-success-400">✔ Pembayaran Lunas</span>')
    ),
                                Infolists\Components\TextEntry::make('change')
                                    ->label('Kembalian')
                                    ->money('IDR')
                                    ->color('primary'),
                            ])->columns(2),

                        Infolists\Components\Section::make('Catatan')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('')
                                    ->placeholder('Tidak ada catatan tambahan.')
                                    ->weight('italic'),
                            ])->collapsed(),
                    ]),
                ]),

            Infolists\Components\Section::make('Daftar Produk yang Dibeli')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('product_name')
                                ->label('Produk')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('quantity')
                                ->label('Jumlah'),
                            //->alignCenter(),
                            Infolists\Components\TextEntry::make('unit_price')
                                ->label('Harga Satuan')
                                ->money('IDR'),
                            // ->alignEnd(),
                            Infolists\Components\TextEntry::make('total_price')
                                ->label('Total Harga')
                                ->money('IDR')
                                ->weight('bold'),
                            // ->alignEnd(),
                        ])->columns(4)
                        ->grid(1),
                ]),
        ];
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
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Orders are created from POS terminal
    }
}
