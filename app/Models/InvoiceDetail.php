<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'supplier_id',
        'item_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'notes',
        'section_name',
        'section_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
         'section_order' => 'integer',
    ];

    /**
     * Invoice
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class
        );
    }

    /**
     * Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    /**
     * Barang
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class
        );
    }
}