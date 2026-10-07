<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'code',
        'name',
        'unit',
        'minimum_stock',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'minimum_stock' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderDetails(): HasMany
    {
    return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function stockTransactionDetails(): HasMany
    {
        return $this->hasMany(StockTransactionDetail::class);
    }
    public function stock(): HasOne
    {
    return $this->hasOne(Stock::class);
    }
}