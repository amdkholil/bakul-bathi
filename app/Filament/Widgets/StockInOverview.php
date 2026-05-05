<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\StockTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockInOverview extends BaseWidget
{
    /**
     * Get the statistics to display in the widget.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        // 1. Total Stock In Today (Quantity)
        $todayQuantity = StockTransaction::where('type', 'in')
            ->whereDate('created_at', Carbon::today())
            ->sum('quantity');

        // 2. Total Expenditure Today (Value based on product cost_price)
        $todayValue = StockTransaction::where('stock_transactions.type', 'in')
            ->whereDate('stock_transactions.created_at', Carbon::today())
            ->join('products', 'stock_transactions.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_transactions.quantity * products.cost_price'));

        // 3. Total Stock In This Month (Quantity)
        $monthQuantity = StockTransaction::where('type', 'in')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('quantity');

        // 4. Total Expenditure This Month
        $monthValue = StockTransaction::where('stock_transactions.type', 'in')
            ->whereMonth('stock_transactions.created_at', Carbon::now()->month)
            ->whereYear('stock_transactions.created_at', Carbon::now()->year)
            ->join('products', 'stock_transactions.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_transactions.quantity * products.cost_price'));

        return [
            Stat::make('Barang Masuk Hari Ini', number_format((float) $todayQuantity, 0, ',', '.').' Unit')
                ->description('Total unit barang masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Biaya Pengadaan Hari Ini', $this->formatRupiah((float) $todayValue))
                ->description('Total nilai pengadaan hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Barang Masuk Bulan Ini', number_format((float) $monthQuantity, 0, ',', '.').' Unit')
                ->description('Volume stok masuk periode ini')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Total Belanja Bulan Ini', $this->formatRupiah((float) $monthValue))
                ->description('Total nilai pengadaan periode ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }

    /**
     * Format currency value to Indonesian Rupiah.
     */
    private function formatRupiah(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
