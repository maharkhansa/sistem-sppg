<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'stock_transaction_id',
        'purchase_order_id',
        'kitchen_id',
        'total_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Invoice berasal dari transaksi OUT
     */
    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(
            StockTransaction::class
        );
    }

    /**
     * Invoice berasal dari PO
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    /**
     * Invoice untuk Kitchen / SPPG
     */
    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(
            Kitchen::class
        );
    }

    /**
     * Detail barang Invoice
     */
    public function details(): HasMany
    {
        return $this->hasMany(
            InvoiceDetail::class
        );
    }

    /**
     * User yang membuat Invoice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Biaya tambahan Invoice
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(
            InvoiceExpense::class
        );
    }
}