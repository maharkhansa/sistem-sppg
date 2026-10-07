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
        'nota_number',
        'nota_date',
        'stock_transaction_id',
        'purchase_order_id',
        'kitchen_id',
        'supplier_id',
        'total_amount',
        'status',
        'notes',
        'created_by',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}