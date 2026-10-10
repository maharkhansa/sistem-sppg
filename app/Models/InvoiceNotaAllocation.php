<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceNotaAllocation extends Model
{
    use HasFactory;

    protected $table = 'invoice_nota_allocations';

    protected $fillable = [
        'nota_keluar_id',
        'invoice_detail_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function notaKeluar(): BelongsTo
    {
        return $this->belongsTo(NotaKeluar::class, 'nota_keluar_id');
    }

    public function invoiceDetail(): BelongsTo
    {
        return $this->belongsTo(InvoiceDetail::class, 'invoice_detail_id');
    }
}
