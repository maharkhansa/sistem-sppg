<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\NotaKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = StockTransaction::with([
            'supplier',
            'details.item',
            'creator',
        ])
            ->where('type', 'IN');

        // PENCARIAN
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'transaction_number',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {

                        $supplierQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );

                    });

            });
        }

        // FILTER TANGGAL
        if ($request->filled('date')) {

            $query->whereDate(
                'transaction_date',
                $request->date
            );
        }

        // FILTER SUPPLIER
        if ($request->filled('supplier_id')) {

            $query->where(
                'supplier_id',
                $request->supplier_id
            );
        }

        $transactions = $query
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'stock_transactions.index',
            compact(
                'transactions',
                'suppliers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL TRANSAKSI BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function show(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type !== 'IN') {

            return redirect()
                ->route('stock-transactions.index')
                ->with(
                    'error',
                    'Transaksi yang dipilih bukan transaksi Barang Masuk.'
                );
        }

        $stockTransaction->load([
            'supplier',
            'details.item',
            'creator',
        ]);

        return view(
            'stock_transactions.show',
            compact('stockTransaction')
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
        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA SUPPLIER AKTIF
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::where('status', true)
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA BARANG AKTIF
        |--------------------------------------------------------------------------
        |
        | Barang akan dikelompokkan di Blade berdasarkan supplier_id.
        |
        */

        $items = Item::with([
            'category',
            'supplier',
        ])
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
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'transaction_date' => [
                'required',
                'date',
            ],

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required',
                'exists:items,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        try {

            DB::transaction(function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | LOCK SUPPLIER
                |--------------------------------------------------------------------------
                */

                $supplier = Supplier::lockForUpdate()
                    ->findOrFail(
                        $data['supplier_id']
                    );


                /*
                |--------------------------------------------------------------------------
                | BUAT NOMOR TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transactionNumber =
                    $this->generateTransactionNumber(
                        $data['transaction_date'],
                        'IN'
                    );


                /*
                |--------------------------------------------------------------------------
                | BUAT HEADER TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transaction =
                    StockTransaction::create([

                        'transaction_number' =>
                            $transactionNumber,

                        'transaction_date' =>
                            $data['transaction_date'],

                        'type' =>
                            'IN',

                        'supplier_id' =>
                            $supplier->id,

                        'notes' =>
                            $data['notes'] ?? null,

                        'created_by' =>
                            auth()->id() ?? 1,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | CEK DUPLIKAT BARANG
                |--------------------------------------------------------------------------
                */

                $itemIds = [];

                foreach ($data['items'] as $detail) {

                    $itemId =
                        (int) $detail['item_id'];

                    if (
                        in_array(
                            $itemId,
                            $itemIds,
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

                    $itemIds[] =
                        $itemId;
                }


                /*
                |--------------------------------------------------------------------------
                | SIMPAN DETAIL DAN TAMBAH STOK
                |--------------------------------------------------------------------------
                */

                foreach ($data['items'] as $detail) {

                    /*
                    |--------------------------------------------------------------------------
                    | LOCK ITEM
                    |--------------------------------------------------------------------------
                    */

                    $item =
                        Item::lockForUpdate()
                            ->findOrFail(
                                $detail['item_id']
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | PASTIKAN BARANG MEMANG MILIK SUPPLIER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $item->supplier_id !==
                        (int) $supplier->id
                    ) {

                        throw new \Exception(
                            'Barang "' .
                            $item->name .
                            '" bukan milik supplier "' .
                            $supplier->name .
                            '".'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NILAI TRANSAKSI
                    |--------------------------------------------------------------------------
                    */

                    $quantity =
                        (float) $detail['quantity'];

                    $unitPrice =
                        (float) $detail['unit_price'];

                    $subtotal =
                        $quantity *
                        $unitPrice;


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DETAIL
                    |--------------------------------------------------------------------------
                    */

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


                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL / BUAT STOK
                    |--------------------------------------------------------------------------
                    */

                    $stock =
                        Stock::lockForUpdate()
                            ->where(
                                'item_id',
                                $item->id
                            )
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
                    | TAMBAH STOK
                    |--------------------------------------------------------------------------
                    */

                    $stock->increment(
                        'quantity',
                        $quantity
                    );

                }

            });


            return redirect()
                ->route(
                    'stock-transactions.index'
                )
                ->with(
                    'success',
                    'Stok supplier berhasil disimpan. Semua barang berhasil ditambahkan ke stok.'
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

        $currentItemIds =
            $stockTransaction
                ->details
                ->pluck('item_id')
                ->filter()
                ->unique()
                ->values();

        $items =
            Item::where(function ($query) use (
                $currentItemIds
            ) {

                $query
                    ->where(
                        'status',
                        true
                    )
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

        $data = $request->validate([

            'transaction_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.detail_id' => [
                'nullable',
                'integer',
            ],

            'items.*.item_id' => [
                'required',
                'exists:items,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);


        try {

            DB::transaction(function () use (
                $data,
                $stockTransaction
            ) {

                /*
                |--------------------------------------------------------------------------
                | AMBIL TRANSAKSI + LOCK
                |--------------------------------------------------------------------------
                */

                $transaction =
                    StockTransaction::with([
                        'details.item',
                        'invoice.details',
                        'purchaseOrder.details',
                        'kitchen',
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
                | DETAIL FORM
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
                | VALIDASI DETAIL
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
                | 1. KEMBALIKAN STOK LAMA
                |--------------------------------------------------------------------------
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
                | 2. HAPUS DETAIL YANG DIHILANGKAN
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
                | 3. TERAPKAN BARANG BARU / PERUBAHAN
                |--------------------------------------------------------------------------
                */

                $totalAmount = 0;

                foreach (
                    $data['items']
                    as $newDetail
                ) {

                    $detailId =
                        !empty(
                            $newDetail['detail_id']
                        )
                            ? (int)
                                $newDetail['detail_id']
                            : null;

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
                    | AMBIL STOK
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
                    | SUBTOTAL
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
                    | TAMBAH DETAIL BARU
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
                | 4. UPDATE HEADER
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
                            ->with('item')
                            ->get()
                        as $outDetail
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | CARI BARANG DI PO
                        |--------------------------------------------------------------------------
                        */

                        $poDetail =
                            $poDetails->firstWhere(
                                'item_id',
                                $outDetail->item_id
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | TENTUKAN SUPPLIER
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $poDetail &&
                            $poDetail->supplier_id
                        ) {

                            $supplierId =
                                $poDetail->supplier_id;

                        } else {

                            $supplierId =
                                $outDetail
                                    ->item
                                    ?->supplier_id;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | VALIDASI SUPPLIER
                        |--------------------------------------------------------------------------
                        */

                        if (!$supplierId) {

                            throw new \Exception(
                                'Supplier untuk barang "' .
                                ($outDetail->item?->name ?? 'tidak diketahui') .
                                '" belum ditentukan. ' .
                                'Silakan tentukan supplier pada data Barang.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CARI DETAIL INVOICE
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
                        | TAMBAH DETAIL INVOICE
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
                    | HAPUS DETAIL INVOICE YANG TIDAK ADA
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


                /*
                |--------------------------------------------------------------------------
                | 6. SINKRONISASI NOTA KELUAR
                |--------------------------------------------------------------------------
                */

                $existingNotas =
                    NotaKeluar::where(
                        'stock_transaction_id',
                        $transaction->id
                    )
                        ->get();


                /*
                |--------------------------------------------------------------------------
                | CUSTOMER ORDER NUMBER
                |--------------------------------------------------------------------------
                */

                $customerOrderNumber =
                    $existingNotas
                        ->first()?->customer_order_number;

                if (
                    empty(
                        $customerOrderNumber
                    )
                ) {

                    $customerOrderNumber =
                        $this->generateCustomerOrderNumber(
                            $transaction->kitchen_id,
                            $transaction->id
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | KELOMPOKKAN BARANG BERDASARKAN SUPPLIER
                |--------------------------------------------------------------------------
                */

                $supplierGroups = [];

                foreach (
                    $transaction
                        ->details()
                        ->with('item')
                        ->get()
                    as $outDetail
                ) {

                    $poDetail =
                        $poDetails->firstWhere(
                            'item_id',
                            $outDetail->item_id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLIER PO / ITEM
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $poDetail &&
                        $poDetail->supplier_id
                    ) {

                        $supplierId =
                            (int)
                            $poDetail->supplier_id;

                    } else {

                        $supplierId =
                            (int)
                            (
                                $outDetail
                                    ->item
                                    ?->supplier_id
                                ?? 0
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDASI
                    |--------------------------------------------------------------------------
                    */

                    if (!$supplierId) {

                        throw new \Exception(
                            'Supplier untuk barang "' .
                            ($outDetail->item?->name ?? 'tidak diketahui') .
                            '" belum ditentukan. ' .
                            'Silakan tentukan supplier pada data Barang.'
                        );
                    }


                    if (
                        !isset(
                            $supplierGroups[$supplierId]
                        )
                    ) {

                        $supplierGroups[$supplierId] = [];
                    }

                    $supplierGroups[$supplierId][] =
                        $outDetail;
                }


                /*
                |--------------------------------------------------------------------------
                | SUPPLIER YANG MASIH DIGUNAKAN
                |--------------------------------------------------------------------------
                */

                $currentSupplierIds =
                    array_map(
                        'intval',
                        array_keys(
                            $supplierGroups
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | HAPUS NOTA SUPPLIER LAMA
                |--------------------------------------------------------------------------
                */

                foreach (
                    $existingNotas
                    as $existingNota
                ) {

                    if (
                        !in_array(
                            (int)
                            $existingNota->supplier_id,
                            $currentSupplierIds,
                            true
                        )
                    ) {

                        $existingNota
                            ->details()
                            ->delete();

                        $existingNota->delete();
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | BUAT / UPDATE NOTA PER SUPPLIER
                |--------------------------------------------------------------------------
                */

                foreach (
                    $supplierGroups
                    as $supplierId => $details
                ) {

                    $supplier =
                        Supplier::find(
                            $supplierId
                        );

                    if (!$supplier) {

                        throw new \Exception(
                            'Supplier dengan ID ' .
                            $supplierId .
                            ' tidak ditemukan.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL NOTA
                    |--------------------------------------------------------------------------
                    */

                    $notaTotal = 0;

                    foreach (
                        $details
                        as $outDetail
                    ) {

                        $notaTotal +=
                            (float)
                            (
                                $outDetail->subtotal
                                ?? 0
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CARI NOTA EXISTING
                    |--------------------------------------------------------------------------
                    */

                    $notaKeluar =
                        $existingNotas
                            ->firstWhere(
                                'supplier_id',
                                $supplierId
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | BUAT NOTA BARU
                    |--------------------------------------------------------------------------
                    */

                    if (!$notaKeluar) {

                        $notaKeluar =
                            NotaKeluar::create([

                                'stock_transaction_id' =>
                                    $transaction->id,

                                'purchase_order_id' =>
                                    $transaction
                                        ->purchase_order_id,

                                'kitchen_id' =>
                                    $transaction
                                        ->kitchen_id,

                                'supplier_id' =>
                                    $supplierId,

                                'nota_number' =>
                                    $this->generateNotaNumber(
                                        $transaction
                                            ->transaction_date
                                    ),

                                'barcode_number' =>
                                    null,

                                'customer_order_number' =>
                                    $customerOrderNumber,

                                'nota_date' =>
                                    $transaction
                                        ->transaction_date,

                                'total_amount' =>
                                    $notaTotal,

                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | BARCODE NOTA
                        |--------------------------------------------------------------------------
                        */

                        $barcodeNumber =
                            $this->generateBarcodeNumber(
                                $supplier,
                                $transaction
                                    ->transaction_date,
                                $notaKeluar->id
                            );

                        $notaKeluar->update([

                            'barcode_number' =>
                                $barcodeNumber,

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE NOTA
                    |--------------------------------------------------------------------------
                    */

                    else {

                        $notaKeluar->update([

                            'purchase_order_id' =>
                                $transaction
                                    ->purchase_order_id,

                            'kitchen_id' =>
                                $transaction
                                    ->kitchen_id,

                            'customer_order_number' =>
                                $customerOrderNumber,

                            'nota_date' =>
                                $transaction
                                    ->transaction_date,

                            'total_amount' =>
                                $notaTotal,

                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | BARCODE
                        |--------------------------------------------------------------------------
                        */

                        $barcodeNumber =
                            $this->generateBarcodeNumber(
                                $supplier,
                                $transaction
                                    ->transaction_date,
                                $notaKeluar->id
                            );

                        if (
                            $notaKeluar
                                ->barcode_number
                            !==
                            $barcodeNumber
                        ) {

                            $notaKeluar->update([

                                'barcode_number' =>
                                    $barcodeNumber,

                            ]);
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS DETAIL NOTA LAMA
                    |--------------------------------------------------------------------------
                    */

                    $notaKeluar
                        ->details()
                        ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | ISI DETAIL NOTA
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $details
                        as $outDetail
                    ) {

                        $notaKeluar
                            ->details()
                            ->create([

                                'item_id' =>
                                    $outDetail
                                        ->item_id,

                                'supplier_id' =>
                                    $supplierId,

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
                }
            });


            return redirect()
                ->route(
                    'stock-transactions.out'
                )
                ->with(
                    'success',
                    'Barang Keluar berhasil diperbarui. Invoice dan Nota Keluar berhasil disinkronkan sesuai supplier.'
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
    | GENERATE CUSTOMER ORDER NUMBER
    |--------------------------------------------------------------------------
    */

    private function generateCustomerOrderNumber(
        $kitchenId,
        $currentStockTransactionId = null
    ) {

        $kitchen =
            \App\Models\Kitchen::find(
                $kitchenId
            );

        $kitchenName =
            $kitchen?->name ?? 'SPPG';

        $query =
            NotaKeluar::where(
                'kitchen_id',
                $kitchenId
            )
                ->whereNotNull(
                    'customer_order_number'
                );

        if ($currentStockTransactionId) {

            $query->where(
                'stock_transaction_id',
                '!=',
                $currentStockTransactionId
            );
        }

        $lastNota =
            $query
                ->orderByDesc('id')
                ->first();

        $nextNumber = 1;

        if (
            $lastNota &&
            $lastNota->customer_order_number
        ) {

            $parts =
                explode(
                    ' - ',
                    $lastNota->customer_order_number,
                    2
                );

            if (
                isset($parts[0]) &&
                is_numeric(
                    trim($parts[0])
                )
            ) {

                $lastNumber =
                    (int)
                    trim(
                        $parts[0]
                    );

                $nextNumber =
                    $lastNumber + 1;
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


        $date =
            Carbon::parse(
                $notaDate
            );

        $dateCode =
            $date->format(
                'Ymd'
            );


        $query =
            NotaKeluar::where(
                'supplier_id',
                $supplier?->id
            )
                ->whereDate(
                    'nota_date',
                    $date->format(
                        'Y-m-d'
                    )
                );

        if ($notaId) {

            $query->where(
                'id',
                '<=',
                $notaId
            );
        }


        $nextNumber =
            $query->count();

        if ($nextNumber < 1) {

            $nextNumber = 1;

        }


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
            Carbon::parse(
                $date
            );

        $prefix =
            'NK-' .
            $date->format(
                'Ymd'
            );


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
                    (int)
                    end($parts);

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


    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    private function generateTransactionNumber(
        $transactionDate,
        string $type = 'IN'
    ): string {

        $date =
            Carbon::parse(
                $transactionDate
            );

        $dateCode =
            $date->format(
                'Ymd'
            );

        $type =
            strtoupper(
                $type
            );

        $prefix =
            $type .
            '-' .
            $dateCode .
            '-';


        $lastTransaction =
            StockTransaction::where(
                'type',
                $type
            )
                ->whereDate(
                    'transaction_date',
                    $date->format(
                        'Y-m-d'
                    )
                )
                ->orderByDesc('id')
                ->first();

        $nextNumber = 1;

        if ($lastTransaction) {

            $lastNumber =
                (int)
                substr(
                    $lastTransaction
                        ->transaction_number,
                    -3
                );

            $nextNumber =
                $lastNumber + 1;
        }


        do {

            $transactionNumber =
                $prefix .
                str_pad(
                    $nextNumber,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

            $exists =
                StockTransaction::where(
                    'transaction_number',
                    $transactionNumber
                )
                    ->exists();

            if ($exists) {

                $nextNumber++;

            }

        } while ($exists);


        return $transactionNumber;
    }
}