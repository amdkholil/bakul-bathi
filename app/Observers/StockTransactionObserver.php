<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockTransaction;

class StockTransactionObserver
{
    /**
     * Handle the StockTransaction "created" event.
     */
    public function created(StockTransaction $stockTransaction): void
    {
        $product = $stockTransaction->product;
        if ($product) {
            if ($stockTransaction->type === 'in') {
                $product->increment('stock', $stockTransaction->quantity);
            } elseif ($stockTransaction->type === 'out') {
                $product->decrement('stock', $stockTransaction->quantity);
            }
        }
    }

    /**
     * Handle the StockTransaction "updated" event.
     */
    public function updated(StockTransaction $stockTransaction): void
    {
        // For simplicity, we assume StockTransactions are mostly immutable.
        // If they update it, we should revert original and apply new.
        if ($stockTransaction->wasChanged('quantity') || $stockTransaction->wasChanged('type') || $stockTransaction->wasChanged('product_id')) {
            $originalProduct = Product::find($stockTransaction->getOriginal('product_id'));
            if ($originalProduct) {
                // Revert original effect
                if ($stockTransaction->getOriginal('type') === 'in') {
                    $originalProduct->decrement('stock', $stockTransaction->getOriginal('quantity'));
                } else {
                    $originalProduct->increment('stock', $stockTransaction->getOriginal('quantity'));
                }
            }

            // Apply new effect
            $newProduct = $stockTransaction->product;
            if ($newProduct) {
                if ($stockTransaction->type === 'in') {
                    $newProduct->increment('stock', $stockTransaction->quantity);
                } else {
                    $newProduct->decrement('stock', $stockTransaction->quantity);
                }
            }
        }
    }

    /**
     * Handle the StockTransaction "deleted" event.
     */
    public function deleted(StockTransaction $stockTransaction): void
    {
        $product = $stockTransaction->product;
        if ($product) {
            // Reverse the effect
            if ($stockTransaction->type === 'in') {
                $product->decrement('stock', $stockTransaction->quantity);
            } elseif ($stockTransaction->type === 'out') {
                $product->increment('stock', $stockTransaction->quantity);
            }
        }
    }

    /**
     * Handle the StockTransaction "restored" event.
     */
    public function restored(StockTransaction $stockTransaction): void
    {
        $this->created($stockTransaction);
    }

    /**
     * Handle the StockTransaction "force deleted" event.
     */
    public function forceDeleted(StockTransaction $stockTransaction): void
    {
        $this->deleted($stockTransaction);
    }
}
