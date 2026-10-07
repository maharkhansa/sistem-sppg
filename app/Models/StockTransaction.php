<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'type',
        'supplier_id',
        'kitchen_id',
        'purchase_order_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockTransactionDetail::class);
    }

    public function invoice(): HasOne
    {
    return $this->hasOne(Invoice::class);
    }

    public function notaKeluars(): HasMany
    {
    return $this->hasMany(
        NotaKeluar::class
    );
    }
}