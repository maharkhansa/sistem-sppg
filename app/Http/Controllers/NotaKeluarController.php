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

        return view(
            'nota_keluars.index',
            compact('notaKeluars')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL / CETAK NOTA
    |--------------------------------------------------------------------------
    */

    public function show(NotaKeluar $notaKeluar)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONSHIP NOTA
        |--------------------------------------------------------------------------
        */

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
        | AMBIL INVOICE BERDASARKAN STOCK TRANSACTION
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
        | DETAIL INVOICE UNTUK SUPPLIER NOTA INI
        |--------------------------------------------------------------------------
        */

        $invoiceDetails = collect();


        if ($invoice && $notaKeluar->supplier_id) {

            $invoiceDetails = $invoice->details
                ->filter(function ($detail) use ($notaKeluar) {

                    return (int) $detail->supplier_id ===
                        (int) $notaKeluar->supplier_id;

                })
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL NOTA BERDASARKAN DETAIL INVOICE
        |--------------------------------------------------------------------------
        */

        $notaTotalAmount = $invoiceDetails->sum(
            function ($detail) {

                return (float) (
                    $detail->subtotal ?? 0
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | GENERATE ULANG BARCODE SESUAI SUPPLIER
        |--------------------------------------------------------------------------
        */

        $barcodeRaw = $this->generateBarcodeNumber(
            $notaKeluar->supplier,
            $notaKeluar->nota_date,
            $notaKeluar->id
        );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN BARCODE BARU KE DATABASE
        |--------------------------------------------------------------------------
        */

        if (
            $notaKeluar->barcode_number !==
            $barcodeRaw
        ) {

            $notaKeluar->update([
                'barcode_number' => $barcodeRaw,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | JIKA ID ORDER BELUM ADA
        |--------------------------------------------------------------------------
        |
        | Ini berguna untuk Nota lama yang dibuat sebelum kolom
        | customer_order_number ditambahkan.
        |
        */

        if (
            empty(
                $notaKeluar->customer_order_number
            )
        ) {

            $customerOrderNumber =
                $this->generateCustomerOrderNumber(
                    $notaKeluar->kitchen_id,
                    $notaKeluar->stock_transaction_id
                );

            $notaKeluar->update([
                'customer_order_number' =>
                    $customerOrderNumber,
            ]);

            /*
            | Refresh agar data terbaru masuk ke object.
            */

            $notaKeluar->refresh();

            /*
            | Load kembali relationship.
            */

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
        | KIRIM DATA KE VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'nota_keluars.show',
            compact(
                'notaKeluar',
                'invoice',
                'invoiceDetails',
                'notaTotalAmount',
                'barcodeRaw'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MEMBUAT NOTA DARI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function createFromOut(
        StockTransaction $stockTransaction
    ) {

        /*
        |--------------------------------------------------------------------------
        | PASTIKAN TRANSAKSI BARANG KELUAR
        |--------------------------------------------------------------------------
        */

        if ($stockTransaction->type !== 'OUT') {

            return back()->with(
                'error',
                'Transaksi bukan merupakan Barang Keluar.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD DATA
        |--------------------------------------------------------------------------
        */

        $stockTransaction->load([
            'details.item',
            'purchaseOrder.details.supplier',
            'purchaseOrder.details.item',
            'kitchen',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN PURCHASE ORDER ADA
        |--------------------------------------------------------------------------
        */

        if (!$stockTransaction->purchaseOrder) {

            return back()->with(
                'error',
                'Barang Keluar belum memiliki Purchase Order.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN DETAIL BARANG ADA
        |--------------------------------------------------------------------------
        */

        if ($stockTransaction->details->isEmpty()) {

            return back()->with(
                'error',
                'Barang Keluar belum memiliki detail barang.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK APAKAH SUDAH ADA NOTA
        |--------------------------------------------------------------------------
        */

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
        | KELOMPOKKAN BARANG BERDASARKAN SUPPLIER
        |--------------------------------------------------------------------------
        */

        $supplierGroups = [];


        foreach (
            $stockTransaction->details
            as $outDetail
        ) {

            /*
            |--------------------------------------------------------------------------
            | CARI DETAIL PO BERDASARKAN ITEM
            |--------------------------------------------------------------------------
            */

            $poDetail = $stockTransaction
                ->purchaseOrder
                ->details
                ->first(
                    function ($detail) use ($outDetail) {

                        return (int) $detail->item_id ===
                            (int) $outDetail->item_id;

                    }
                );


            /*
            |--------------------------------------------------------------------------
            | SUPPLIER WAJIB ADA
            |--------------------------------------------------------------------------
            */

            if (
                !$poDetail ||
                !$poDetail->supplier_id
            ) {

                return back()->with(
                    'error',
                    'Supplier untuk barang "' .
                    ($outDetail->item?->name ?? '-') .
                    '" tidak ditemukan pada Purchase Order.'
                );
            }


            $supplierId =
                $poDetail->supplier_id;


            /*
            |--------------------------------------------------------------------------
            | BUAT GROUP SUPPLIER
            |--------------------------------------------------------------------------
            */

            if (
                !isset(
                    $supplierGroups[$supplierId]
                )
            ) {

                $supplierGroups[$supplierId] = [];

            }


            $supplierGroups[$supplierId][] = [

                'out_detail' =>
                    $outDetail,

                'po_detail' =>
                    $poDetail,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | BUAT ID ORDER SATU KALI UNTUK SATU BARANG KELUAR
        |--------------------------------------------------------------------------
        |
        | Semua supplier dalam satu Barang Keluar mendapatkan ID Order
        | yang sama.
        |
        | Contoh:
        |
        | 001 - SPPG Jogonegoro
        |
        |--------------------------------------------------------------------------
        */

        $customerOrderNumber =
            $this->generateCustomerOrderNumber(
                $stockTransaction->kitchen_id,
                $stockTransaction->id
            );


        /*
        |--------------------------------------------------------------------------
        | BUAT NOTA UNTUK SETIAP SUPPLIER
        |--------------------------------------------------------------------------
        */

        $createdNotas = [];


        DB::transaction(
            function () use (
                $supplierGroups,
                $stockTransaction,
                $customerOrderNumber,
                &$createdNotas
            ) {

                foreach (
                    $supplierGroups
                    as $supplierId => $details
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL SUPPLIER
                    |--------------------------------------------------------------------------
                    */

                    $supplier =
                        Supplier::find($supplierId);


                    if (!$supplier) {

                        throw new \Exception(
                            'Supplier tidak ditemukan.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NOMOR NOTA
                    |--------------------------------------------------------------------------
                    */

                    $notaNumber =
                        $this->generateNotaNumber(
                            $stockTransaction
                                ->transaction_date
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL NOTA
                    |--------------------------------------------------------------------------
                    */

                    $totalAmount = 0;


                    foreach (
                        $details
                        as $row
                    ) {

                        $outDetail =
                            $row['out_detail'];


                        $totalAmount +=
                            (float) (
                                $outDetail
                                    ->subtotal ?? 0
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | BUAT NOTA
                    |--------------------------------------------------------------------------
                    */

                    $notaKeluar =
                        NotaKeluar::create([

                            'stock_transaction_id' =>
                                $stockTransaction->id,

                            'purchase_order_id' =>
                                $stockTransaction
                                    ->purchase_order_id,

                            'kitchen_id' =>
                                $stockTransaction
                                    ->kitchen_id,

                            'supplier_id' =>
                                $supplierId,

                            'nota_number' =>
                                $notaNumber,

                            'barcode_number' =>
                                null,

                            /*
                            |--------------------------------------------------------------------------
                            | ID ORDER
                            |--------------------------------------------------------------------------
                            */

                            'customer_order_number' =>
                                $customerOrderNumber,

                            'nota_date' =>
                                $stockTransaction
                                    ->transaction_date,

                            'total_amount' =>
                                $totalAmount,

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | BUAT BARCODE SETELAH ID NOTA TERSEDIA
                    |--------------------------------------------------------------------------
                    */

                    $barcodeNumber =
                        $this->generateBarcodeNumber(
                            $supplier,
                            $stockTransaction
                                ->transaction_date,
                            $notaKeluar->id
                        );


                    $notaKeluar->update([

                        'barcode_number' =>
                            $barcodeNumber,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DETAIL NOTA
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $details
                        as $row
                    ) {

                        $outDetail =
                            $row['out_detail'];

                        $poDetail =
                            $row['po_detail'];


                        $notaKeluar
                            ->details()
                            ->create([

                                'item_id' =>
                                    $outDetail
                                        ->item_id,

                                'supplier_id' =>
                                    $poDetail
                                        ->supplier_id,

                                'quantity' =>
                                    $outDetail
                                        ->quantity,

                                'unit' =>
                                    $outDetail
                                        ->unit,

                                'unit_price' =>
                                    $outDetail
                                        ->unit_price,

                                'subtotal' =>
                                    $outDetail
                                        ->subtotal,

                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN KE ARRAY
                    |--------------------------------------------------------------------------
                    */

                    $createdNotas[] =
                        $notaKeluar;
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT KE NOTA PERTAMA
        |--------------------------------------------------------------------------
        */

        if (!empty($createdNotas)) {

            return redirect()->route(
                'nota-keluars.show',
                $createdNotas[0]
            )->with(
                'success',
                'Nota berhasil dibuat.'
            );
        }


        return back()->with(
            'error',
            'Nota gagal dibuat.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE ID PELANGGAN / ID ORDER
    |--------------------------------------------------------------------------
    |
    | Format:
    |
    | 001 - SPPG Jogonegoro
    | 002 - SPPG Jogonegoro
    | 003 - SPPG Jogonegoro
    |
    | Nomor urut berdasarkan SPPG.
    |
    |--------------------------------------------------------------------------
    */

    private function generateCustomerOrderNumber(
        $kitchenId,
        $currentStockTransactionId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA SPPG
        |--------------------------------------------------------------------------
        */

        $kitchen = Kitchen::find(
            $kitchenId
        );


        $kitchenName =
            $kitchen?->name ?? 'SPPG';


        /*
        |--------------------------------------------------------------------------
        | CARI NOTA TERAKHIR PADA SPPG
        |--------------------------------------------------------------------------
        */

        $query = NotaKeluar::where(
            'kitchen_id',
            $kitchenId
        )
            ->whereNotNull(
                'customer_order_number'
            );


        /*
        |--------------------------------------------------------------------------
        | UNTUK DATA LAMA / SHOW
        |--------------------------------------------------------------------------
        |
        | Jika sedang mencari ID Order untuk Nota yang sudah ada,
        | jangan menghitung Nota dari transaksi yang sama.
        |
        */

        if ($currentStockTransactionId) {

            $query->where(
                'stock_transaction_id',
                '!=',
                $currentStockTransactionId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL ID ORDER TERAKHIR
        |--------------------------------------------------------------------------
        */

        $lastNota =
            $query
                ->orderByDesc('id')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | NOMOR AWAL
        |--------------------------------------------------------------------------
        */

        $nextNumber = 1;


        if (
            $lastNota &&
            $lastNota->customer_order_number
        ) {

            /*
            | Contoh:
            |
            | 005 - SPPG Jogonegoro
            */

            $parts = explode(
                ' - ',
                $lastNota->customer_order_number,
                2
            );


            if (
                isset($parts[0]) &&
                is_numeric(trim($parts[0]))
            ) {

                $lastNumber =
                    (int) trim(
                        $parts[0]
                    );


                $nextNumber =
                    $lastNumber + 1;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | HASIL AKHIR
        |--------------------------------------------------------------------------
        */

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
    |
    | Format:
    |
    | GM-20261008-001
    | ZN-20261008-001
    | SR-20261008-001
    | TF-20261008-001
    |
    |--------------------------------------------------------------------------
    */

    private function generateBarcodeNumber(
        ?Supplier $supplier,
        $notaDate,
        ?int $notaId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | PREFIX SUPPLIER
        |--------------------------------------------------------------------------
        */

        $prefix = match (
            $supplier?->nota_template
        ) {

            'gemilang' =>
                'GM',

            'zenzi' =>
                'ZN',

            'sumber_rejeki' =>
                'SR',

            'top_fast' =>
                'TF',

            default =>
                'OT',

        };


        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        */

        $date =
            Carbon::parse($notaDate);


        $dateCode =
            $date->format('Ymd');


        /*
        |--------------------------------------------------------------------------
        | CARI NOTA SUPPLIER PADA TANGGAL TERSEBUT
        |--------------------------------------------------------------------------
        */

        $query = NotaKeluar::where(
            'supplier_id',
            $supplier?->id
        )
            ->whereDate(
                'nota_date',
                $date->format('Y-m-d')
            );


        /*
        |--------------------------------------------------------------------------
        | NOTA YANG SEDANG DIBUAT / DIBUKA
        |--------------------------------------------------------------------------
        */

        if ($notaId) {

            $query->where(
                'id',
                '<=',
                $notaId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | HITUNG NOMOR URUT
        |--------------------------------------------------------------------------
        */

        $nextNumber =
            $query->count();


        if ($nextNumber < 1) {

            $nextNumber = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | HASIL BARCODE
        |--------------------------------------------------------------------------
        */

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

    private function generateNotaNumber(
        $date
    ) {

        $date =
            Carbon::parse($date);


        $prefix =
            'NK-' .
            $date->format('Ymd');


        $lastNota =
            NotaKeluar::where(
                'nota_number',
                'like',
                $prefix . '-%'
            )
                ->orderByDesc('id')
                ->first();


        $nextNumber = 1;


        if ($lastNota) {

            $parts =
                explode(
                    '-',
                    $lastNota->nota_number
                );


            if (count($parts) >= 3) {

                $lastNumber =
                    (int) end($parts);


                $nextNumber =
                    $lastNumber + 1;
            }
        }


        return sprintf(
            '%s-%03d',
            $prefix,
            $nextNumber
        );
    }
}