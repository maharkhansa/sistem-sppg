<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'expense_type_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}