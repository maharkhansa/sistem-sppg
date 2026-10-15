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
        'nota_type',
        'section_name',
        'section_order',
        'nota_number',
        'barcode_number',
        'customer_order_number',
        'nota_date',
        'total_amount',
        'delivery_address',
    ];

    protected $casts = [
        'nota_date' => 'date',
        'total_amount' => 'decimal:2',
        'section_order' => 'integer',
    ];

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(NotaKeluarDetail::class);
    }

    public function invoiceNotaAllocations(): HasMany
    {
        return $this->hasMany(InvoiceNotaAllocation::class);
    }

    public function scopeForStockTransaction($query, $stockTransactionId)
    {
        return $query->where(
            'stock_transaction_id',
            $stockTransactionId
        );
    }

    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForSection($query, $sectionName)
    {
        if ($sectionName === null || trim((string) $sectionName) === '') {
            return $query->where(function ($q) {
                $q->whereNull('section_name')
                    ->orWhere('section_name', '');
            });
        }

        return $query->where('section_name', $sectionName);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('nota_type', $type);
    }

    public function isExpenseNota(): bool
    {
        return $this->nota_type === 'expense';
    }
}