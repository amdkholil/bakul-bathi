<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtLogResource\Pages;
use App\Models\DebtLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class DebtLogResource extends Resource
{
    protected static ?string $model = DebtLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('sale_id')
                    ->relationship('sale', 'invoice_number', fn ($query) => $query->where('status', 'Hutang'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('amount_paid')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Payment Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->stackedOnMobile();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDebtLogs::route('/'),
        ];
    }
}
