<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Models\StockTransaction;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     */
    public function created(SaleItem $saleItem): void
    {
        if ($saleItem->product_id) {
            StockTransaction::create([
                'product_id' => $saleItem->product_id,
                'type' => 'out',
                'quantity' => $saleItem->quantity,
                'reference' => 'Sale #'.$saleItem->sale_id,
                'notes' => 'Penjualan otomatis',
            ]);
        }
    }

    /**
     * Handle the SaleItem "updated" event.
     */
    public function updated(SaleItem $saleItem): void
    {
        if ($saleItem->wasChanged('quantity') || $saleItem->wasChanged('product_id')) {
            $originalQuantity = $saleItem->getOriginal('quantity');
            $originalProductId = $saleItem->getOriginal('product_id');

            // Restore original product stock via an "in" transaction
            if ($originalProductId) {
                StockTransaction::create([
                    'product_id' => $originalProductId,
                    'type' => 'in',
                    'quantity' => $originalQuantity,
                    'reference' => 'Sale Update #'.$saleItem->sale_id,
                    'notes' => 'Revisi penjualan otomatis (restore)',
                ]);
            }

            // Deduct new quantity via an "out" transaction
            if ($saleItem->product_id) {
                StockTransaction::create([
                    'product_id' => $saleItem->product_id,
                    'type' => 'out',
                    'quantity' => $saleItem->quantity,
                    'reference' => 'Sale Update #'.$saleItem->sale_id,
                    'notes' => 'Revisi penjualan otomatis (potong)',
                ]);
            }
        }
    }

    /**
     * Handle the SaleItem "deleted" event.
     */
    public function deleted(SaleItem $saleItem): void
    {
        if ($saleItem->product_id) {
            StockTransaction::create([
                'product_id' => $saleItem->product_id,
                'type' => 'in',
                'quantity' => $saleItem->quantity,
                'reference' => 'Sale Deleted #'.$saleItem->sale_id,
                'notes' => 'Penghapusan penjualan otomatis',
            ]);
        }
    }

    /**
     * Handle the SaleItem "restored" event.
     */
    public function restored(SaleItem $saleItem): void
    {
        //
    }

    /**
     * Handle the SaleItem "force deleted" event.
     */
    public function forceDeleted(SaleItem $saleItem): void
    {
        $this->deleted($saleItem);
    }
}
