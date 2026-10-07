<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transaction_id',
        'item_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}