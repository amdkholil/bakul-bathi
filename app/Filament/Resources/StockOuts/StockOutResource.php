<?php

namespace App\Filament\Resources\StockOuts;

use App\Filament\Resources\StockOuts\Pages\ManageStockOuts;
use App\Models\StockTransaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockOutResource extends Resource
{
    protected static ?string $model = StockTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Barang Keluar (Manual)';

    protected static ?string $modelLabel = 'Barang Keluar';

    protected static ?string $slug = 'stock-outs';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'out')->where('notes', 'not like', '%Penjualan otomatis%');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produk')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Hidden::make('type')
                    ->default('out'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Kuantitas')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('reference')
                    ->label('Referensi (opsional)')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('Alasan Keluar (Cth: Rusak, Kadaluarsa)')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas Keluar')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Alasan / Catatan')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->schema([
                        Forms\Components\DatePicker::make('from_date')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to_date')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from_date'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['to_date'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
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
            'index' => ManageStockOuts::route('/'),
        ];
    }
}
