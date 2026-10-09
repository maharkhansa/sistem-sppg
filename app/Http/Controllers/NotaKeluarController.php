<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\NotaKeluar;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Kitchen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotaKeluarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR NOTA KELUAR
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $notaKeluars = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
        ])
            ->latest()
            ->get();

        return view('nota_keluars.index', compact('notaKeluars'));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL / CETAK NOTA
    |--------------------------------------------------------------------------
    */

    public function show(NotaKeluar $notaKeluar)
    {
        $notaKeluar->load([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL INVOICE DARI TRANSAKSI BARANG KELUAR
        |--------------------------------------------------------------------------
        */

        $invoice = Invoice::with([
            'details.item',
            'details.supplier',
            'kitchen',
        ])
            ->where(
                'stock_transaction_id',
                $notaKeluar->stock_transaction_id
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | DETAIL INVOICE SESUAI SUPPLIER NOTA
        |--------------------------------------------------------------------------
        */

        $invoiceDetails = collect();

        if ($invoice && $notaKeluar->supplier_id) {
            $invoiceDetails = $invoice->details
                ->filter(function ($detail) use ($notaKeluar) {
                    return (int) $detail->supplier_id
                        === (int) $notaKeluar->supplier_id;
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL NOTA BERDASARKAN DETAIL INVOICE
        |--------------------------------------------------------------------------
        */

        $notaTotalAmount = $invoiceDetails->sum(function ($detail) {
            return (float) ($detail->subtotal ?? 0);
        });

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        |
        | Barcode tetap menggunakan tanggal nota tersimpan.
        | Perubahan tanggal invoice tidak mengubah barcode yang sudah ada.
        */

        $barcodeRaw = $notaKeluar->barcode_number;

        if (empty($barcodeRaw)) {
            $barcodeRaw = $this->generateBarcodeNumber(
                $notaKeluar->supplier,
                $notaKeluar->nota_date,
                $notaKeluar->id
            );

            $notaKeluar->update([
                'barcode_number' => $barcodeRaw,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ID ORDER UNTUK NOTA LAMA
        |--------------------------------------------------------------------------
        */

        if (empty($notaKeluar->customer_order_number)) {
            $customerOrderNumber = $this->generateCustomerOrderNumber(
                $notaKeluar->kitchen_id,
                $notaKeluar->stock_transaction_id
            );

            $notaKeluar->update([
                'customer_order_number' => $customerOrderNumber,
            ]);

            $notaKeluar->refresh();

            $notaKeluar->load([
                'stockTransaction',
                'purchaseOrder',
                'kitchen',
                'supplier',
                'details.item',
                'details.supplier',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TANGGAL INVOICE SEBAGAI SUMBER TANGGAL NOTA
        |--------------------------------------------------------------------------
        |
        | Variabel $invoice dikirim ke Blade.
        | Blade menggunakan $invoice->invoice_date terlebih dahulu.
        */

        $deliveryDate = $invoice?->invoice_date
            ?? $notaKeluar->nota_date
            ?? $notaKeluar->created_at;

        return view('nota_keluars.show', compact(
            'notaKeluar',
            'invoice',
            'invoiceDetails',
            'notaTotalAmount',
            'barcodeRaw',
            'deliveryDate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBUAT NOTA DARI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function createFromOut(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type !== 'OUT') {
            return back()->with(
                'error',
                'Transaksi bukan merupakan Barang Keluar.'
            );
        }

        $stockTransaction->load([
            'details.item',
            'purchaseOrder.details.supplier',
            'purchaseOrder.details.item',
            'kitchen',
        ]);

        if (!$stockTransaction->purchaseOrder) {
            return back()->with(
                'error',
                'Barang Keluar belum memiliki Purchase Order.'
            );
        }

        if ($stockTransaction->details->isEmpty()) {
            return back()->with(
                'error',
                'Barang Keluar belum memiliki detail barang.'
            );
        }

        $existingNota = NotaKeluar::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->exists();

        if ($existingNota) {
            return back()->with(
                'error',
                'Nota untuk Barang Keluar ini sudah dibuat.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | KELOMPOKKAN DETAIL BERDASARKAN SUPPLIER DARI PURCHASE ORDER
        |--------------------------------------------------------------------------
        */

        $supplierGroups = [];

        foreach ($stockTransaction->details as $outDetail) {
            $poDetail = $stockTransaction->purchaseOrder
                ->details
                ->first(function ($detail) use ($outDetail) {
                    return (int) $detail->item_id
                        === (int) $outDetail->item_id;
                });

            if (!$poDetail || !$poDetail->supplier_id) {
                return back()->with(
                    'error',
                    'Supplier untuk barang "'
                    . ($outDetail->item?->name ?? '-')
                    . '" tidak ditemukan pada Purchase Order.'
                );
            }

            $supplierId = $poDetail->supplier_id;

            if (!isset($supplierGroups[$supplierId])) {
                $supplierGroups[$supplierId] = [];
            }

            $supplierGroups[$supplierId][] = [
                'out_detail' => $outDetail,
                'po_detail' => $poDetail,
            ];
        }

        $customerOrderNumber = $this->generateCustomerOrderNumber(
            $stockTransaction->kitchen_id,
            $stockTransaction->id
        );

        $createdNotas = [];

        DB::transaction(function () use (
            $supplierGroups,
            $stockTransaction,
            $customerOrderNumber,
            &$createdNotas
        ) {
            foreach ($supplierGroups as $supplierId => $details) {
                $supplier = Supplier::find($supplierId);

                if (!$supplier) {
                    throw new \RuntimeException(
                        'Supplier tidak ditemukan.'
                    );
                }

                $transactionDate = $stockTransaction->transaction_date
                    ?? now()->toDateString();

                $notaNumber = $this->generateNotaNumber(
                    $transactionDate
                );

                $totalAmount = 0;

                foreach ($details as $row) {
                    $outDetail = $row['out_detail'];

                    $totalAmount += (float) (
                        $outDetail->subtotal ?? 0
                    );
                }

                $notaKeluar = NotaKeluar::create([
                    'stock_transaction_id' =>
                        $stockTransaction->id,

                    'purchase_order_id' =>
                        $stockTransaction->purchase_order_id,

                    'kitchen_id' =>
                        $stockTransaction->kitchen_id,

                    'supplier_id' =>
                        $supplierId,

                    'nota_number' =>
                        $notaNumber,

                    'barcode_number' =>
                        null,

                    'customer_order_number' =>
                        $customerOrderNumber,

                    'nota_date' =>
                        $transactionDate,

                    'total_amount' =>
                        $totalAmount,
                ]);

                $barcodeNumber = $this->generateBarcodeNumber(
                    $supplier,
                    $transactionDate,
                    $notaKeluar->id
                );

                $notaKeluar->update([
                    'barcode_number' => $barcodeNumber,
                ]);

                foreach ($details as $row) {
                    $outDetail = $row['out_detail'];
                    $poDetail = $row['po_detail'];

                    $notaKeluar->details()->create([
                        'item_id' =>
                            $outDetail->item_id,

                        'supplier_id' =>
                            $poDetail->supplier_id,

                        'quantity' =>
                            $outDetail->quantity,

                        'unit' =>
                            $outDetail->unit,

                        'unit_price' =>
                            $outDetail->unit_price,

                        'subtotal' =>
                            $outDetail->subtotal,
                    ]);
                }

                $createdNotas[] = $notaKeluar;
            }
        });

        if (!empty($createdNotas)) {
            return redirect()
                ->route('nota-keluars.show', $createdNotas[0])
                ->with('success', 'Nota berhasil dibuat.');
        }

        return back()->with(
            'error',
            'Nota gagal dibuat.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT INFORMASI NOTA
    |--------------------------------------------------------------------------
    |
    | Tanggal tidak diedit di sini.
    | Tanggal yang ditampilkan pada Nota mengikuti tanggal Invoice.
    | Alamat pengiriman tetap bisa diperbarui.
    |--------------------------------------------------------------------------
    */

    public function updateInformasi(
        Request $request,
        NotaKeluar $notaKeluar
    ) {
        $validated = $request->validate([
            'delivery_address' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'delivery_address.required' =>
                'Alamat pengiriman wajib diisi.',

            'delivery_address.max' =>
                'Alamat pengiriman maksimal 2000 karakter.',
        ]);

        $notaKeluar->delivery_address =
            $validated['delivery_address'];

        $notaKeluar->save();

        return back()->with(
            'success',
            'Alamat pengiriman nota berhasil diperbarui.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE ID PELANGGAN / ID ORDER
    |--------------------------------------------------------------------------
    */

    private function generateCustomerOrderNumber(
        $kitchenId,
        $currentStockTransactionId = null
    ) {
        $kitchen = Kitchen::find($kitchenId);

        $kitchenName = $kitchen?->name ?? 'SPPG';

        $query = NotaKeluar::where(
            'kitchen_id',
            $kitchenId
        )->whereNotNull('customer_order_number');

        if ($currentStockTransactionId) {
            $query->where(
                'stock_transaction_id',
                '!=',
                $currentStockTransactionId
            );
        }

        $lastNota = $query
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastNota && $lastNota->customer_order_number) {
            $parts = explode(
                ' - ',
                $lastNota->customer_order_number,
                2
            );

            if (isset($parts[0]) && is_numeric(trim($parts[0]))) {
                $nextNumber = (int) trim($parts[0]) + 1;
            }
        }

        return sprintf(
            '%03d - %s',
            $nextNumber,
            $kitchenName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE BARCODE NOTA
    |--------------------------------------------------------------------------
    */

    private function generateBarcodeNumber(
        ?Supplier $supplier,
        $notaDate,
        ?int $notaId = null
    ) {
        $prefix = match ($supplier?->nota_template) {
            'gemilang' => 'GM',
            'zenzi' => 'ZN',
            'sumber_rejeki' => 'SR',
            'top_fast' => 'TF',
            default => 'OT',
        };

        $date = Carbon::parse(
            $notaDate ?? now()
        );

        $dateCode = $date->format('Ymd');

        $query = NotaKeluar::where(
            'supplier_id',
            $supplier?->id
        )->whereDate(
            'nota_date',
            $date->format('Y-m-d')
        );

        if ($notaId) {
            $query->where('id', '<=', $notaId);
        }

        $nextNumber = max(1, $query->count());

        return sprintf(
            '%s-%s-%03d',
            $prefix,
            $dateCode,
            $nextNumber
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR NOTA
    |--------------------------------------------------------------------------
    */

    private function generateNotaNumber($date)
    {
        $date = Carbon::parse($date);

        $prefix = 'NK-' . $date->format('Ymd');

        $lastNota = NotaKeluar::where(
            'nota_number',
            'like',
            $prefix . '-%'
        )
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastNota) {
            $parts = explode('-', $lastNota->nota_number);

            if (count($parts) >= 3) {
                $nextNumber = (int) end($parts) + 1;
            }
        }

        return sprintf(
            '%s-%03d',
            $prefix,
            $nextNumber
        );
    }
}