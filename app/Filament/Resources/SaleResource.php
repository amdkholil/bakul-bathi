<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string | UnitEnum | null $navigationGroup = 'Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Section::make('')
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->default('INV-' . strtoupper(now()->format('Ymd-His')))
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required(),
                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Lunas' => 'Lunas',
                                        'Hutang' => 'Hutang',
                                    ])
                                    ->required()
                                    ->default('Lunas')
                                    ->native(false),
                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'Tunai' => 'Tunai',
                                        'Transfer' => 'Transfer',
                                        'QRIS' => 'QRIS',
                                    ])
                                    ->required()
                                    ->default('Tunai')
                                    ->native(false),
                            ])->columns(2),

                        \Filament\Schemas\Components\Section::make('')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('price_at_sale', $product->selling_price);
                                                    $set('cost_at_sale', $product->cost_price);
                                                }
                                            })
                                            ->columnSpan(3),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live()
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('price_at_sale')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('cost_at_sale')
                                            ->numeric()
                                            ->hidden(),
                                    ])
                                    ->columns(6)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        static::calculateTotals($get, $set);
                                    })
                                    ->deleteAction(fn (\Filament\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => static::calculateTotals($get, $set))),
                            ]),
                    ])->columnSpan(2),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Section::make('Summary')
                            ->schema([
                                Forms\Components\TextInput::make('total_price')
                                    ->numeric()
                                    ->readOnly()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('total_cost')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->hidden(),
                                Forms\Components\TextInput::make('profit')
                                    ->numeric()
                                    ->readOnly()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('Rp'),
                                Forms\Components\Placeholder::make('summary_text')
                                    ->content(fn (Get $get) => 'Total Untung: Rp ' . number_format($get('profit'), 0, ',', '.')),
                            ]),
                    ])->columnSpan(1),
            ]);
    }

    protected static function calculateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $totalPrice = 0;
        $totalCost = 0;

        foreach ($items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price_at_sale'] ?? 0);
            $cost = (float) ($item['cost_at_sale'] ?? 0);

            $totalPrice += ($qty * $price);
            $totalCost += ($qty * $cost);
        }

        $set('total_price', $totalPrice);
        $set('total_cost', $totalCost);
        $set('profit', $totalPrice - $totalCost);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->placeholder('Guest')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ),
                Tables\Columns\TextColumn::make('profit')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Hutang' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Lunas' => 'Lunas',
                        'Hutang' => 'Hutang',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, string $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, string $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
