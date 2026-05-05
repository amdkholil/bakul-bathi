<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockReportResource\Pages;
use App\Models\StockTransaction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockReportResource extends Resource
{
    protected static ?string $model = StockTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Laporan Stok';

    protected static ?string $modelLabel = 'Transaksi Stok';

    protected static ?string $slug = 'stock-report';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'in' => 'Barang Masuk',
                        'out' => 'Barang Keluar',
                    ]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produk')
                    ->searchable(),
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
            ->recordActions([])
            ->toolbarActions([])
            ->stackedOnMobile();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStockReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
