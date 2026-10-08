<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $transactions = StockTransaction::with('supplier')
            ->where('type', 'IN')
            ->latest()
            ->get();

        return view(
            'stock_transactions.index',
            compact('transactions')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function outIndex()
    {
        $transactions = StockTransaction::with([
            'kitchen',
            'purchaseOrder',
            'details.item',
            'invoice',
            'notaKeluars',
        ])
            ->where('type', 'OUT')
            ->latest()
            ->get();

        return view(
            'stock_transactions.out_index',
            compact('transactions')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $suppliers = \App\Models\Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        $items = Item::with('category')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'stock_transactions.create',
            compact(
                'suppliers',
                'items'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_date' =>
                'required|date',

            'supplier_id' =>
                'required|exists:suppliers,id',

            'notes' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.item_id' =>
                'required|exists:items,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.unit_price' =>
                'required|numeric|min:0',
        ]);


        DB::transaction(function () use ($data) {

            $transaction = StockTransaction::create([
                'transaction_number' =>
                    $this->generateTransactionNumber(),

                'transaction_date' =>
                    $data['transaction_date'],

                'type' =>
                    'IN',

                'supplier_id' =>
                    $data['supplier_id'],

                'notes' =>
                    $data['notes'] ?? null,

                'created_by' =>
                    auth()->id() ?? 1,
            ]);


            foreach ($data['items'] as $detail) {

                $item = Item::findOrFail(
                    $detail['item_id']
                );


                $quantity =
                    (float) $detail['quantity'];


                $unitPrice =
                    (float) $detail['unit_price'];


                $transaction->details()->create([
                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        $quantity,

                    'unit' =>
                        $item->unit,

                    'unit_price' =>
                        $unitPrice,

                    'subtotal' =>
                        $quantity * $unitPrice,
                ]);


                $stock = Stock::firstOrCreate(
                    [
                        'item_id' =>
                            $item->id,
                    ],
                    [
                        'quantity' =>
                            0,
                    ]
                );


                $stock->increment(
                    'quantity',
                    $quantity
                );
            }
        });


        return redirect()
            ->route('stock-transactions.index')
            ->with(
                'success',
                'Barang masuk berhasil disimpan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function edit(
        StockTransaction $stockTransaction
    ) {
        if ($stockTransaction->type !== 'OUT') {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi yang dipilih bukan transaksi Barang Keluar.'
                );
        }


        $stockTransaction->load([
            'details.item',
            'purchaseOrder',
            'kitchen',
            'invoice',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Ambil item yang sedang digunakan
        |--------------------------------------------------------------------------
        */

        $currentItemIds = $stockTransaction
            ->details
            ->pluck('item_id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Item aktif + item lama
        |--------------------------------------------------------------------------
        |
        | Item lama tetap ditampilkan meskipun statusnya tidak aktif.
        |
        */

        $items = Item::where(function ($query) use ($currentItemIds) {

                $query
                    ->where('status', true)
                    ->orWhereIn(
                        'id',
                        $currentItemIds
                    );

            })
            ->orderBy('name')
            ->get();


        return view(
            'stock_transactions.edit',
            compact(
                'stockTransaction',
                'items'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        StockTransaction $stockTransaction
    ) {
        if ($stockTransaction->type !== 'OUT') {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Hanya transaksi Barang Keluar yang dapat diedit.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        |
        | detail_id dibuat nullable karena barang baru yang ditambahkan
        | dari tombol "Tambah Barang" belum mempunyai detail_id.
        |
        */

        $data = $request->validate([
            'transaction_date' =>
                'required|date',

            'notes' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.detail_id' =>
                'nullable|integer',

            'items.*.item_id' =>
                'required|exists:items,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.unit_price' =>
                'required|numeric|min:0',
        ]);


        try {

            DB::transaction(function () use (
                $data,
                $stockTransaction
            ) {

                /*
                |--------------------------------------------------------------------------
                | Ambil transaksi + lock
                |--------------------------------------------------------------------------
                */

                $transaction = StockTransaction::with([
                    'details.item',
                    'invoice.details',
                    'purchaseOrder.details',
                ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $stockTransaction->id
                    );


                /*
                |--------------------------------------------------------------------------
                | DETAIL LAMA
                |--------------------------------------------------------------------------
                */

                $oldDetails =
                    $transaction
                        ->details()
                        ->get();


                $oldDetailIds =
                    $oldDetails
                        ->pluck('id')
                        ->map(
                            fn ($id) => (int) $id
                        )
                        ->toArray();


                /*
                |--------------------------------------------------------------------------
                | DETAIL YANG DIKIRIM DARI FORM
                |--------------------------------------------------------------------------
                */

                $submittedDetailIds =
                    collect($data['items'])
                        ->pluck('detail_id')
                        ->filter()
                        ->map(
                            fn ($id) => (int) $id
                        )
                        ->toArray();


                /*
                |--------------------------------------------------------------------------
                | Pastikan detail_id benar-benar milik transaksi ini
                |--------------------------------------------------------------------------
                */

                foreach (
                    $submittedDetailIds
                    as $detailId
                ) {

                    if (
                        !in_array(
                            $detailId,
                            $oldDetailIds,
                            true
                        )
                    ) {

                        throw new \Exception(
                            'Detail barang tidak valid.'
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | CEK BARANG DUPLIKAT
                |--------------------------------------------------------------------------
                */

                $usedItemIds = [];


                foreach (
                    $data['items']
                    as $newDetail
                ) {

                    $itemId =
                        (int) $newDetail['item_id'];


                    if (
                        in_array(
                            $itemId,
                            $usedItemIds,
                            true
                        )
                    ) {

                        $item =
                            Item::find($itemId);


                        throw new \Exception(
                            'Barang "' .
                            ($item?->name ?? '-') .
                            '" dipilih lebih dari satu kali.'
                        );
                    }


                    $usedItemIds[] =
                        $itemId;
                }


                /*
                |--------------------------------------------------------------------------
                | 1. KEMBALIKAN SEMUA STOK BARANG LAMA
                |--------------------------------------------------------------------------
                |
                | Contoh:
                |
                | Sebelum edit:
                | Beras 10
                | Minyak 5
                |
                | Stok dikembalikan dahulu:
                | Beras +10
                | Minyak +5
                |
                | Setelah itu baru stok versi baru dikurangi.
                |
                */

                foreach (
                    $oldDetails
                    as $oldDetail
                ) {

                    $stock =
                        Stock::where(
                            'item_id',
                            $oldDetail->item_id
                        )
                            ->lockForUpdate()
                            ->first();


                    if (!$stock) {

                        throw new \Exception(
                            'Stok untuk barang "' .
                            ($oldDetail->item?->name ?? 'tidak diketahui') .
                            '" tidak ditemukan.'
                        );
                    }


                    $stock->increment(
                        'quantity',
                        (float) $oldDetail->quantity
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | 2. HAPUS DETAIL LAMA YANG TIDAK ADA DI FORM
                |--------------------------------------------------------------------------
                */

                $deletedDetailIds =
                    array_diff(
                        $oldDetailIds,
                        $submittedDetailIds
                    );


                if (!empty($deletedDetailIds)) {

                    $transaction
                        ->details()
                        ->whereIn(
                            'id',
                            $deletedDetailIds
                        )
                        ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | 3. TERAPKAN BARANG VERSI BARU
                |--------------------------------------------------------------------------
                */

                $totalAmount = 0;


                foreach (
                    $data['items']
                    as $newDetail
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Detail ID
                    |--------------------------------------------------------------------------
                    |
                    | Jika ada = detail lama
                    | Jika kosong = barang baru
                    |
                    */

                    $detailId =
                        !empty(
                            $newDetail['detail_id']
                        )
                            ? (int)
                                $newDetail['detail_id']
                            : null;


                    /*
                    |--------------------------------------------------------------------------
                    | Ambil item
                    |--------------------------------------------------------------------------
                    */

                    $item =
                        Item::findOrFail(
                            $newDetail['item_id']
                        );


                    $quantity =
                        (float)
                        $newDetail['quantity'];


                    $unitPrice =
                        (float)
                        $newDetail['unit_price'];


                    /*
                    |--------------------------------------------------------------------------
                    | Ambil stok
                    |--------------------------------------------------------------------------
                    */

                    $stock =
                        Stock::where(
                            'item_id',
                            $item->id
                        )
                            ->lockForUpdate()
                            ->first();


                    if (!$stock) {

                        $stock =
                            Stock::create([
                                'item_id' =>
                                    $item->id,

                                'quantity' =>
                                    0,
                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CEK STOK
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $stock->quantity
                        <
                        $quantity
                    ) {

                        throw new \Exception(
                            'Stok "' .
                            $item->name .
                            '" tidak mencukupi. ' .
                            'Stok tersedia: ' .
                            $stock->quantity .
                            ', kebutuhan: ' .
                            $quantity
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | KURANGI STOK
                    |--------------------------------------------------------------------------
                    */

                    $stock->decrement(
                        'quantity',
                        $quantity
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG SUBTOTAL
                    |--------------------------------------------------------------------------
                    */

                    $subtotal =
                        $quantity *
                        $unitPrice;


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE DETAIL LAMA
                    |--------------------------------------------------------------------------
                    */

                    if ($detailId) {

                        $detail =
                            $oldDetails
                                ->firstWhere(
                                    'id',
                                    $detailId
                                );


                        if (!$detail) {

                            throw new \Exception(
                                'Detail barang tidak ditemukan.'
                            );
                        }


                        $detail->update([
                            'item_id' =>
                                $item->id,

                            'quantity' =>
                                $quantity,

                            'unit' =>
                                $item->unit,

                            'unit_price' =>
                                $unitPrice,

                            'subtotal' =>
                                $subtotal,
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | BUAT DETAIL BARU
                    |--------------------------------------------------------------------------
                    */

                    else {

                        $transaction
                            ->details()
                            ->create([
                                'item_id' =>
                                    $item->id,

                                'quantity' =>
                                    $quantity,

                                'unit' =>
                                    $item->unit,

                                'unit_price' =>
                                    $unitPrice,

                                'subtotal' =>
                                    $subtotal,
                            ]);
                    }


                    $totalAmount +=
                        $subtotal;
                }


                /*
                |--------------------------------------------------------------------------
                | 4. UPDATE HEADER BARANG KELUAR
                |--------------------------------------------------------------------------
                */

                $transaction->update([
                    'transaction_date' =>
                        $data['transaction_date'],

                    'notes' =>
                        $data['notes'] ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | 5. SINKRONISASI INVOICE
                |--------------------------------------------------------------------------
                */

                if ($transaction->invoice) {

                    $invoice =
                        $transaction->invoice;


                    /*
                    |--------------------------------------------------------------------------
                    | Ambil detail PO
                    |--------------------------------------------------------------------------
                    */

                    $poDetails =
                        $transaction
                            ->purchaseOrder
                            ?->details
                            ?? collect();


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE HEADER INVOICE
                    |--------------------------------------------------------------------------
                    */

                    $invoice->update([
                        'invoice_date' =>
                            $data['transaction_date'],

                        'total_amount' =>
                            $totalAmount,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE / TAMBAH DETAIL INVOICE
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $transaction
                            ->details()
                            ->get()
                        as $outDetail
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Cari supplier berdasarkan PO
                        |--------------------------------------------------------------------------
                        */

                        $poDetail =
                            $poDetails->firstWhere(
                                'item_id',
                                $outDetail->item_id
                            );


                        if (!$poDetail) {

                            throw new \Exception(
                                'Barang "' .
                                ($outDetail->item?->name ?? 'tidak diketahui') .
                                '" tidak ditemukan pada Purchase Order.'
                            );
                        }


                        $supplierId =
                            $poDetail->supplier_id;


                        if (!$supplierId) {

                            throw new \Exception(
                                'Supplier untuk barang "' .
                                ($outDetail->item?->name ?? 'tidak diketahui') .
                                '" tidak ditemukan pada Purchase Order.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Cari detail invoice
                        |--------------------------------------------------------------------------
                        */

                        $invoiceDetail =
                            $invoice
                                ->details()
                                ->where(
                                    'item_id',
                                    $outDetail->item_id
                                )
                                ->first();


                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE DETAIL INVOICE
                        |--------------------------------------------------------------------------
                        */

                        if ($invoiceDetail) {

                            $invoiceDetail->update([
                                'supplier_id' =>
                                    $supplierId,

                                'item_id' =>
                                    $outDetail->item_id,

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


                        /*
                        |--------------------------------------------------------------------------
                        | TAMBAH DETAIL INVOICE BARU
                        |--------------------------------------------------------------------------
                        */

                        else {

                            $invoice
                                ->details()
                                ->create([
                                    'supplier_id' =>
                                        $supplierId,

                                    'item_id' =>
                                        $outDetail->item_id,

                                    'quantity' =>
                                        $outDetail->quantity,

                                    'unit' =>
                                        $outDetail->unit,

                                    'unit_price' =>
                                        $outDetail->unit_price,

                                    'subtotal' =>
                                        $outDetail->subtotal,

                                    'notes' =>
                                        null,
                                ]);
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS DETAIL INVOICE YANG SUDAH TIDAK ADA DI OUT
                    |--------------------------------------------------------------------------
                    */

                    $currentItemIds =
                        $transaction
                            ->details()
                            ->pluck('item_id')
                            ->toArray();


                    if (!empty($currentItemIds)) {

                        $invoice
                            ->details()
                            ->whereNotIn(
                                'item_id',
                                $currentItemIds
                            )
                            ->delete();

                    } else {

                        $invoice
                            ->details()
                            ->delete();
                    }
                }
            });


            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'success',
                    'Barang Keluar berhasil diperbarui dan Invoice berhasil disinkronkan.'
                );

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    private function generateTransactionNumber()
    {
        $date =
            now()->format('Ymd');


        $lastTransaction =
            StockTransaction::where(
                'type',
                'IN'
            )
                ->whereDate(
                    'transaction_date',
                    now()->toDateString()
                )
                ->latest('id')
                ->first();


        $number =
            $lastTransaction
                ? (
                    (int) substr(
                        $lastTransaction->transaction_number,
                        -3
                    )
                ) + 1
                : 1;


        return 'IN-' .
            $date .
            '-' .
            str_pad(
                $number,
                3,
                '0',
                STR_PAD_LEFT
            );
    }
}