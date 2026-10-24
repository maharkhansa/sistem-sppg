<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'contact_person',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'receipt_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_vendor')
            ->withTimestamps();
    }

    public function purchaseResponsibles(): BelongsToMany
    {
        return $this->belongsToMany(
            PurchaseResponsible::class,
            'purchase_responsible_vendor'
        )->withTimestamps();
    }
}