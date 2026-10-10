<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kitchen extends Model
{
    use HasFactory;

    protected $fillable = [
        // Data SPPG
        'id_sppg',
        'name',
        'kabupaten_kota',
        'provinsi',
        'alamat',
        'status',

        // Data Mitra / Yayasan
        'foundation_name',

        // Pengawas Keuangan
        'finance_officer_name',
        'finance_officer_nik',

        // Kepala SPPG
        'head_sppg_name',
        'head_sppg_nip',

        // Perwakilan Mitra / Yayasan
        'foundation_rep_name',
        'foundation_rep_nik',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function lpdhs(): HasMany
    {
        return $this->hasMany(LPDH::class);
    }
}