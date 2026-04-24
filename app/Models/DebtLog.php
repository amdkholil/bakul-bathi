<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'amount_paid',
        'description',
    ];

    /**
     * Get the sale that owns the debt log.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
