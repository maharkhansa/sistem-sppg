<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function invoiceExpenses(): HasMany
    {
        return $this->hasMany(InvoiceExpense::class);
    }
}