<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference',
        'notes',
    ];

    /**
     * Get the product associated with the stock transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
