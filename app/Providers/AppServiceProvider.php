<?php

namespace App\Providers;

use App\Models\SaleItem;
use App\Models\StockTransaction;
use App\Observers\SaleItemObserver;
use App\Observers\StockTransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SaleItem::observe(SaleItemObserver::class);
        StockTransaction::observe(StockTransactionObserver::class);
    }
}
