<?php

namespace App\Models;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransactionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transaction_id',
        'account_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'source_type',
        'purchase_responsible_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function purchaseResponsible(): BelongsTo
    {
        return $this->belongsTo(PurchaseResponsible::class);
    }
}