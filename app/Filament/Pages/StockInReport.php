<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\StockTransaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockInReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected string $view = 'filament.pages.stock-in-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $title = 'Laporan Barang Masuk';

    protected static ?string $navigationLabel = 'Barang Masuk';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'month' => now()->format('m'),
            'year' => (string) now()->year,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan')
                    ->schema([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->required()
                            ->live(),
                        Select::make('year')
                            ->label('Tahun')
                            ->options(collect(range(now()->year, now()->year - 5))
                                ->mapWithKeys(fn (int $y): array => [(string) $y => (string) $y])
                                ->toArray())
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockTransaction::query()
                    ->where('type', 'in')
                    ->join('products', 'stock_transactions.product_id', '=', 'products.id')
                    ->select(
                        'stock_transactions.*',
                        'products.cost_price as unit_cost',
                        DB::raw('stock_transactions.quantity * products.cost_price as total_cost')
                    )
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->summarize(
                        Sum::make()
                            ->label('Total Barang')
                    ),
                TextColumn::make('unit_cost')
                    ->label('Harga Beli @')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('total_cost')
                    ->label('Subtotal')
                    ->money('IDR', locale: 'id')
                    ->summarize(
                        Sum::make()
                            ->label('Total Belanja')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ),
                TextColumn::make('reference')
                    ->label('Referensi'),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $month = $this->data['month'] ?? now()->format('m');
                $year = $this->data['year'] ?? now()->format('Y');

                return $query
                    ->whereMonth('stock_transactions.created_at', $month)
                    ->whereYear('stock_transactions.created_at', $year);
            })
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->stackedOnMobile();
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    /**
     * Get the statistics to display in the widget at the top of the page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStats(): array
    {
        $month = $this->data['month'] ?? now()->format('m');
        $year = $this->data['year'] ?? now()->format('Y');

        $query = StockTransaction::query()
            ->where('type', 'in')
            ->join('products', 'stock_transactions.product_id', '=', 'products.id')
            ->whereMonth('stock_transactions.created_at', $month)
            ->whereYear('stock_transactions.created_at', $year);

        $totalCost = (float) $query->sum(DB::raw('stock_transactions.quantity * products.cost_price'));
        $totalItems = (int) $query->sum('quantity');
        $transactionCount = $query->count();

        return [
            [
                'label' => 'Total Belanja',
                'value' => 'Rp '.number_format($totalCost, 0, ',', '.'),
                'description' => 'Nilai pengadaan barang',
                'icon' => 'heroicon-m-shopping-cart',
                'color' => 'primary',
            ],
            [
                'label' => 'Total Barang Masuk',
                'value' => number_format($totalItems, 0, ',', '.').' unit',
                'description' => 'Volume stok masuk',
                'icon' => 'heroicon-m-archive-box',
                'color' => 'success',
            ],
            [
                'label' => 'Frekuensi Masuk',
                'value' => $transactionCount.' Kali',
                'description' => 'Jumlah transaksi input',
                'icon' => 'heroicon-m-arrow-path',
                'color' => 'warning',
            ],
        ];
    }
}
