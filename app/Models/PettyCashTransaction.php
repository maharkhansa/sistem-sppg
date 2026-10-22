<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_responsible_id',
        'type',
        'transaction_date',
        'amount',
        'source_account_id',
        'stock_transaction_payment_id',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function purchaseResponsible(): BelongsTo
    {
        return $this->belongsTo(PurchaseResponsible::class);
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'source_account_id');
    }

    public function stockTransactionPayment(): BelongsTo
    {
        return $this->belongsTo(
            StockTransactionPayment::class,
            'stock_transaction_payment_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}