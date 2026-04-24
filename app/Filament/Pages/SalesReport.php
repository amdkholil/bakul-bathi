<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Width;

class SalesReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.sales-report';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $title = 'Laporan Penjualan Bulanan';

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
                \Filament\Schemas\Components\Section::make('Filter Laporan')
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
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Lunas' => 'Lunas',
                                'Hutang' => 'Hutang',
                            ])
                            ->live(),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::query())
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Hutang' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_price')
                    ->label('Total Omzet')
                    ->money('IDR', locale: 'id')
                    ->summarize(
                        Sum::make()
                            ->label('Total Omzet')
                            ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ),
                TextColumn::make('profit')
                    ->label('Total Bathi')
                    ->money('IDR', locale: 'id')
                    ->summarize(
                        Sum::make()
                            ->label('Total Bathi')
                            ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $month = $this->data['month'] ?? now()->format('m');
                $year = $this->data['year'] ?? now()->format('Y');
                $status = $this->data['status'] ?? null;

                return $query
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->when($status, fn (Builder $q) => $q->where('status', $status));
            })
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
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
        $status = $this->data['status'] ?? null;

        $query = Sale::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when($status, fn (Builder $q) => $q->where('status', $status));

        $totalOmzet = (float) $query->sum('total_price');
        $totalProfit = (float) $query->sum('profit');
        $count = $query->count();

        return [
            [
                'label' => 'Omzet',
                'value' => 'Rp ' . number_format($totalOmzet, 0, ',', '.'),
                'description' => 'Penerimaan kotor periode ini',
                'icon' => 'heroicon-m-presentation-chart-line',
                'color' => 'success',
            ],
            [
                'label' => 'Bathi',
                'value' => 'Rp ' . number_format($totalProfit, 0, ',', '.'),
                'description' => 'Keuntungan bersih periode ini',
                'icon' => 'heroicon-m-banknotes',
                'color' => 'primary',
            ],
            [
                'label' => 'Total Transaksi',
                'value' => $count . ' Transaksi',
                'description' => 'Frekuensi penjualan',
                'icon' => 'heroicon-m-shopping-cart',
                'color' => 'warning',
            ],
        ];
    }
}
