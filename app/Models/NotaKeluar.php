<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transaction_id',
        'purchase_order_id',
        'kitchen_id',
        'supplier_id',
        'nota_number',
        'barcode_number',
        'customer_order_number',
        'nota_date',
        'total_amount',
    ];

    protected $casts = [
        'nota_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(
            StockTransaction::class
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(
            Kitchen::class
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function details(): HasMany
    {
        return $this->hasMany(
            NotaKeluarDetail::class
        );
    }
}