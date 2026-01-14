<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecialDiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'specialDiscounts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'fixed' => 'Jumlah Tetap',
                        'percentage' => 'Persentase',
                    ])
                    ->required()
                    ->default('fixed'),
                Forms\Components\TextInput::make('discount_value')
                    ->label('Nilai Diskon')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Tipe Diskon')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Jumlah Tetap',
                        'percentage' => 'Persentase',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'info',
                        'percentage' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->formatStateUsing(fn ($state, $record) => $record->discount_type === 'fixed' 
                        ? 'Rp ' . number_format($state, 0, ',', '.') 
                        : $state . '%'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
