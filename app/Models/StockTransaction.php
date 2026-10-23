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
        'vendor_name',
        'purchase_note_number',
        'porter_cost',
        'fuel_cost',
        'fuel_cost_details',
        'parking_cost',
        'cooking_cost',
        'other_cost',
        'purchase_discount_type',
        'purchase_discount_value',
        'purchase_responsible_id',
        'payment_type',
        'payment_status',
        'paid_at',
        'due_date',
        'kitchen_id',
        'purchase_order_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'porter_cost' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'fuel_cost_details' => 'array',
        'parking_cost' => 'decimal:2',
        'cooking_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'purchase_discount_value' => 'decimal:2',
        'paid_at' => 'date',
        'due_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseResponsible(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseResponsible::class,
            'purchase_responsible_id'
        );
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
        return $this->hasMany(NotaKeluar::class);
    }

     public function payments(): HasMany
    {
        return $this->hasMany(StockTransactionPayment::class);
    }
}