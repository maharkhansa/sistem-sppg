<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitchen_id',
        'po_number',
        'po_date',
        'status',
        'approved_at',
        'notes',
        'created_by',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function stockTransaction(): HasOne
    {
        return $this->hasOne(StockTransaction::class);
    }
    public function invoice(): HasOne
    {
    return $this->hasOne(Invoice::class);
    }

    public function notaKeluar(): HasOne
    {
    return $this->hasOne(
        NotaKeluar::class
    );
    }
}