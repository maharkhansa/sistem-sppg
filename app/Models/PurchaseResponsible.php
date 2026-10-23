<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PurchaseResponsible extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(
            Vendor::class,
            'purchase_responsible_vendor'
        )->withTimestamps();
    }
}