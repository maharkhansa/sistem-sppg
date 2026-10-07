<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaKeluarDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'nota_keluar_id',
        'supplier_id',
        'item_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function notaKeluar(): BelongsTo
    {
        return $this->belongsTo(
            NotaKeluar::class
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class
        );
    }
}