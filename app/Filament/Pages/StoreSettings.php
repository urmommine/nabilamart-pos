<?php

namespace App\Filament\Pages;

use App\Models\StoreSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class StoreSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.store-settings';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pengaturan Toko';

    protected static ?string $title = 'Pengaturan Toko';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'store_name' => StoreSetting::get(StoreSetting::STORE_NAME, ''),
            'store_address' => StoreSetting::get(StoreSetting::STORE_ADDRESS, ''),
            'store_phone' => StoreSetting::get(StoreSetting::STORE_PHONE, ''),
            'store_email' => StoreSetting::get(StoreSetting::STORE_EMAIL, ''),
            'tax_percentage' => StoreSetting::get(StoreSetting::TAX_PERCENTAGE, '0'),
            'printer_name' => StoreSetting::get(StoreSetting::PRINTER_NAME, ''),
            'printer_type' => StoreSetting::get(StoreSetting::PRINTER_TYPE, 'usb'),
            'printer_ip' => StoreSetting::get(StoreSetting::PRINTER_IP, ''),
            'receipt_footer' => StoreSetting::get(StoreSetting::RECEIPT_FOOTER, ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Toko')
                    ->description('Informasi ini akan ditampilkan pada struk')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('store_address')
                            ->label('Alamat')
                            ->rows(2)
                            ->maxLength(500),
                        Forms\Components\TextInput::make('store_phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('store_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Pajak')
                    ->schema([
                        Forms\Components\TextInput::make('tax_percentage')
                            ->label('Persentase Pajak (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Set 0 jika tidak ada pajak'),
                    ]),

                Forms\Components\Section::make('Pengaturan Printer')
                    ->description('Konfigurasi untuk thermal printer')
                    ->schema([
                        Forms\Components\Select::make('printer_type')
                            ->label('Tipe Koneksi Printer')
                            ->options([
                                'usb' => 'USB / Local',
                                'network' => 'Network (IP)',
                                'bluetooth' => 'Bluetooth (Printer-JS)',
                                'usb_web' => 'USB (WebUSB - PrintHub)',
                            ])
                            ->default('usb')
                            ->live(),
                        Forms\Components\TextInput::make('printer_name')
                            ->label('Nama Printer')
                            ->helperText('Nama printer di Windows, misal: POS-58')
                            ->visible(fn(Forms\Get $get) => $get('printer_type') === 'usb'),
                        Forms\Components\TextInput::make('printer_ip')
                            ->label('IP Address Printer')
                            ->helperText('Contoh: 192.168.1.100')
                            ->visible(fn(Forms\Get $get) => $get('printer_type') === 'network'),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Struk')
                    ->schema([
                        Forms\Components\Textarea::make('receipt_footer')
                            ->label('Footer Struk')
                            ->rows(2)
                            ->placeholder('Terima Kasih Sudah Berbelanja!')
                            ->helperText('Teks yang muncul di bagian bawah struk'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        StoreSetting::set(StoreSetting::STORE_NAME, $data['store_name'] ?? '');
        StoreSetting::set(StoreSetting::STORE_ADDRESS, $data['store_address'] ?? '');
        StoreSetting::set(StoreSetting::STORE_PHONE, $data['store_phone'] ?? '');
        StoreSetting::set(StoreSetting::STORE_EMAIL, $data['store_email'] ?? '');
        StoreSetting::set(StoreSetting::TAX_PERCENTAGE, $data['tax_percentage'] ?? '0');
        StoreSetting::set(StoreSetting::PRINTER_NAME, $data['printer_name'] ?? '');
        StoreSetting::set(StoreSetting::PRINTER_TYPE, $data['printer_type'] ?? 'usb');
        StoreSetting::set(StoreSetting::PRINTER_IP, $data['printer_ip'] ?? '');
        StoreSetting::set(StoreSetting::RECEIPT_FOOTER, $data['receipt_footer'] ?? '');

        // Clear all setting caches
        Cache::forget('store_setting_' . StoreSetting::STORE_NAME);
        Cache::forget('store_setting_' . StoreSetting::STORE_ADDRESS);
        Cache::forget('store_setting_' . StoreSetting::STORE_PHONE);
        Cache::forget('store_setting_' . StoreSetting::STORE_EMAIL);
        Cache::forget('store_setting_' . StoreSetting::TAX_PERCENTAGE);
        Cache::forget('store_setting_' . StoreSetting::PRINTER_NAME);
        Cache::forget('store_setting_' . StoreSetting::PRINTER_TYPE);
        Cache::forget('store_setting_' . StoreSetting::PRINTER_IP);
        Cache::forget('store_setting_' . StoreSetting::RECEIPT_FOOTER);

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
