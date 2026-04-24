<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ShopStatsOverview extends BaseWidget
{
    /**
     * The polling interval for the widget.
     *
     * @var string|null
     */
    // protected static ?string $pollingInterval = '15s';

    /**
     * Get the statistics to display in the widget.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        // 1. Total Sales Today (Omzet)
        $todaySales = Sale::whereDate('created_at', Carbon::today())
            ->sum('total_price');

        // 2. Total Profit Today (Bathi)
        $todayProfit = Sale::whereDate('created_at', Carbon::today())
            ->sum('profit');

        // 3. Low Stock Alert
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        // 4. Total Receivables (Total Hutang)
        $totalReceivables = Sale::where('status', 'Hutang')
            ->sum('total_price');

        return [
            Stat::make('Omzet Hari Ini', $this->formatRupiah((float) $todaySales))
                ->description('Total penjualan kotor hari ini')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('success')
                ->chart([7, 3, 5, 2, 10, 3, 12]),

            Stat::make('Bathi Hari Ini', $this->formatRupiah((float) $todayProfit))
                ->description('Total keuntungan bersih hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([3, 5, 2, 8, 4, 7, 10]),

            Stat::make('Stok Menipis', $lowStockCount . ' Produk')
                ->description($lowStockCount > 0 ? 'Segera lakukan restock' : 'Semua stok aman')
                ->descriptionIcon('heroicon-m-archive-box-arrow-down')
                ->color($lowStockCount > 0 ? 'danger' : 'success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Total Hutang Pelanggan', $this->formatRupiah((float) $totalReceivables))
                ->description('Piutang yang belum terbayar')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),
        ];
    }

    /**
     * Format currency value to Indonesian Rupiah.
     *
     * @param float $value
     * @return string
     */
    private function formatRupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
