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

    /*
    |--------------------------------------------------------------------------
    | Relasi Barang Keluar
    |--------------------------------------------------------------------------
    */
    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Purchase Order
    |--------------------------------------------------------------------------
    */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Dapur SPPG
    |--------------------------------------------------------------------------
    */
    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Supplier
    |--------------------------------------------------------------------------
    */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Nota Lama
    |--------------------------------------------------------------------------
    | Dipertahankan untuk kompatibilitas dengan kode lama.
    | Data utama tampilan Nota menggunakan detail Invoice.
    |--------------------------------------------------------------------------
    */
    public function details(): HasMany
    {
        return $this->hasMany(NotaKeluarDetail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Alokasi Detail Invoice ke Nota
    |--------------------------------------------------------------------------
    | Digunakan agar setiap Nota menampilkan barang dari Invoice
    | sesuai supplier dan bagian yang bersangkutan.
    |--------------------------------------------------------------------------
    */
    public function invoiceNotaAllocations(): HasMany
    {
        return $this->hasMany(
            InvoiceNotaAllocation::class,
            'nota_keluar_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Barang Keluar
    |--------------------------------------------------------------------------
    */
    public function scopeForStockTransaction($query, int $stockTransactionId)
    {
        return $query->where(
            'stock_transaction_id',
            $stockTransactionId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Supplier
    |--------------------------------------------------------------------------
    */
    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Bagian
    |--------------------------------------------------------------------------
    | Bagian kosong berarti Nota tanpa pembagian bagian.
    |--------------------------------------------------------------------------
    */
    public function scopeForSection($query, ?string $sectionName)
    {
        $sectionName = trim((string) $sectionName);

        if ($sectionName === '') {
            return $query->where(function ($q) {
                $q->whereNull('section_name')
                    ->orWhere('section_name', '');
            });
        }

        return $query->where('section_name', $sectionName);
    }
}