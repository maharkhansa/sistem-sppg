<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Kitchen;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $transactions = StockTransaction::with([
            'supplier',
            'details.item',
            'details.supplier',
        ])
            ->where('type', 'IN')
            ->latest()
            ->paginate(15);

        $suppliers = Supplier::orderBy('name')->get();

        return view(
            'stock_transactions.index',
            compact('transactions', 'suppliers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view(
            'stock_transactions.create',
            compact('suppliers', 'items')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],
            'transaction_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:items,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],
        ], [
            'supplier_id.required' =>
                'Supplier wajib dipilih.',
            'supplier_id.exists' =>
                'Supplier tidak ditemukan.',
            'transaction_date.required' =>
                'Tanggal barang masuk wajib diisi.',
            'items.required' =>
                'Minimal satu barang harus dipilih.',
            'items.min' =>
                'Minimal satu barang harus dipilih.',
            'items.*.item_id.required' =>
                'Barang wajib dipilih.',
            'items.*.item_id.distinct' =>
                'Barang yang sama tidak boleh ditambahkan lebih dari satu kali.',
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.numeric' =>
                'Jumlah barang harus berupa angka.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.unit_price.numeric' =>
                'Harga satuan harus berupa angka.',
            'items.*.unit_price.min' =>
                'Harga satuan tidak boleh negatif.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER BARANG DENGAN JUMLAH POSITIF
        |--------------------------------------------------------------------------
        */

        $itemsData = collect($validated['items'])
            ->filter(function ($row) {
                return (float) ($row['quantity'] ?? 0) > 0;
            })
            ->values();

        if ($itemsData->isEmpty()) {
            throw ValidationException::withMessages([
                'items' =>
                    'Isi jumlah minimal satu barang yang benar-benar masuk.',
            ]);
        }

        $supplierId = (int) $validated['supplier_id'];

        /*
        |--------------------------------------------------------------------------
        | SIMPAN TRANSAKSI DAN PERBARUI STOK
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $validated,
            $itemsData,
            $supplierId
        ) {
            $itemIds = $itemsData
                ->pluck('item_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $items = Item::whereIn('id', $itemIds)
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            /*
             * Pastikan barang berasal dari supplier yang dipilih.
             */
            foreach ($itemsData as $row) {
                $item = $items->get((int) $row['item_id']);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak ditemukan.',
                    ]);
                }

                if ((int) $item->supplier_id !== $supplierId) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Barang "' . $item->name .
                            '" tidak sesuai dengan supplier yang dipilih.',
                    ]);
                }
            }

            /*
             * Kunci catatan stok barang terkait.
             */
            $stocks = Stock::whereIn('item_id', $itemIds)
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            /*
             * Buat catatan stok awal jika belum tersedia.
             */
            foreach ($itemIds as $itemId) {
                if (!$stocks->has($itemId)) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }
            }

            /*
             * Buat nomor transaksi unik.
             */
            do {
                $transactionNumber = 'IN-' .
                    now()->format('YmdHis') . '-' .
                    Str::upper(Str::random(4));
            } while (
                StockTransaction::where(
                    'transaction_number',
                    $transactionNumber
                )->exists()
            );

            /*
             * Simpan header transaksi.
             */
            $transaction = StockTransaction::create([
                'transaction_number' => $transactionNumber,
                'type' => 'IN',
                'supplier_id' => $supplierId,
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            /*
             * Simpan detail dan tambahkan stok.
             */
            foreach ($itemsData as $row) {
                $itemId = (int) $row['item_id'];
                $item = $items->get($itemId);
                $quantity = (float) $row['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $unitPrice = (
                    isset($row['unit_price']) &&
                    $row['unit_price'] !== '' &&
                    $row['unit_price'] !== null
                )
                    ? (float) $row['unit_price']
                    : (float) ($item->price ?? 0);

                if ($unitPrice < 0) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Harga barang tidak boleh negatif.',
                    ]);
                }

                $unit = !empty($row['unit'])
                    ? $row['unit']
                    : $item->unit;

                $subtotal = $quantity * $unitPrice;

                $transaction->details()->create([
                    'item_id' => $itemId,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $stock = $stocks->get($itemId);

                $stock->quantity =
                    (float) $stock->quantity + $quantity;

                $stock->save();
            }
        });

        return redirect()
            ->route('stock-transactions.index')
            ->with(
                'success',
                'Barang masuk berhasil disimpan dan stok telah diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG KELUAR + FILTER
    |--------------------------------------------------------------------------
    */

    public function outIndex(Request $request)
    {
        $query = StockTransaction::with([
            'supplier',
            'purchaseOrder',
            'kitchen',
            'details.item',
            'details.supplier',
            'invoice',
            'notaKeluars.supplier',
        ])
            ->where('type', 'OUT');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where(
                    'transaction_number',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'notes',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhereHas('purchaseOrder', function ($po) use ($search) {
                        $po->where(
                            'po_number',
                            'like',
                            '%' . $search . '%'
                        );
                    })
                    ->orWhereHas('kitchen', function ($kitchen) use ($search) {
                        $kitchen->where(function ($subQuery) use ($search) {
                            $subQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                                ->orWhere(
                                    'id_sppg',
                                    'like',
                                    '%' . $search . '%'
                                );
                        });
                    });
            });
        }

        if ($request->filled('kitchen_id')) {
            $query->where(
                'kitchen_id',
                $request->input('kitchen_id')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->input('date_to')
            );
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        return view(
            'stock_transactions.out_index',
            compact('transactions', 'suppliers', 'kitchens')
        );
    }


/*
|--------------------------------------------------------------------------
| DETAIL TRANSAKSI BARANG MASUK / BARANG KELUAR
|--------------------------------------------------------------------------
*/

    public function show($stockTransaction)
    {
        $stockTransaction = StockTransaction::with([
            'supplier',
            'kitchen',
            'purchaseOrder',
            'details.item',
            'details.supplier',
            'invoice',
            'notaKeluars.supplier',
        ])->findOrFail($stockTransaction);

        return view(
            'stock_transactions.show',
            compact('stockTransaction')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT TRANSAKSI
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $stockTransaction = StockTransaction::with([
            'supplier',
            'details.item',
            'details.supplier',
        ])->findOrFail($id);

        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        return view(
            'stock_transactions.edit',
            compact(
                'stockTransaction',
                'items',
                'suppliers',
                'kitchens'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG MASUK / BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $existingTransaction = StockTransaction::findOrFail($id);

        $rules = [
            'transaction_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:items,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];

        if ($existingTransaction->type === 'IN') {
            $rules['supplier_id'] = [
                'required',
                'integer',
                'exists:suppliers,id',
            ];
        } else {
            $rules['items.*.supplier_id'] = [
                'required',
                'integer',
                'exists:suppliers,id',
            ];
        }

        $validated = $request->validate($rules, [
            'items.required' =>
                'Minimal satu barang harus dipilih.',
            'items.min' =>
                'Minimal satu barang harus dipilih.',
            'items.*.item_id.required' =>
                'Barang wajib dipilih.',
            'items.*.item_id.exists' =>
                'Barang tidak ditemukan.',
            'items.*.item_id.distinct' =>
                'Barang yang sama tidak boleh ditambahkan lebih dari satu kali.',
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.supplier_id.required' =>
                'Supplier setiap barang wajib dipilih.',
            'items.*.supplier_id.exists' =>
                'Supplier barang tidak ditemukan.',
        ]);

        /*
         * Barang dengan jumlah 0 tidak ikut disimpan.
         */
        $validated['items'] = collect($validated['items'])
            ->filter(function ($row) {
                return (float) ($row['quantity'] ?? 0) > 0;
            })
            ->all();

        if (count($validated['items']) === 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'Minimal satu barang harus memiliki jumlah lebih dari 0.',
            ]);
        }

        DB::transaction(function () use ($validated, $id) {
            $transaction = StockTransaction::with('details')
                ->lockForUpdate()
                ->findOrFail($id);

            $oldItemIds = $transaction->details
                ->pluck('item_id');

            $newItemIds = collect($validated['items'])
                ->pluck('item_id')
                ->map(fn ($itemId) => (int) $itemId);

            $allItemIds = $oldItemIds
                ->merge($newItemIds)
                ->unique()
                ->sort()
                ->values();

            $stocks = Stock::whereIn('item_id', $allItemIds)
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            /*
             * Kembalikan dampak transaksi lama terhadap stok.
             */
            foreach ($transaction->details as $detail) {
                $stock = $stocks->get($detail->item_id);

                if (!$stock) {
                    if ($transaction->type === 'IN') {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok barang lama tidak ditemukan. Transaksi tidak dapat diperbarui dengan aman.',
                        ]);
                    }

                    $stock = Stock::create([
                        'item_id' => $detail->item_id,
                        'quantity' => 0,
                    ]);

                    $stocks->put($detail->item_id, $stock);
                }

                if ($transaction->type === 'IN') {
                    $newQuantity = (float) $stock->quantity
                        - (float) $detail->quantity;

                    if ($newQuantity < 0) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok saat ini tidak cukup untuk membatalkan barang masuk lama. Periksa transaksi stok terkait terlebih dahulu.',
                        ]);
                    }

                    $stock->quantity = $newQuantity;
                } else {
                    $stock->quantity = (float) $stock->quantity
                        + (float) $detail->quantity;
                }

                $stock->save();
            }

            $items = Item::whereIn('id', $newItemIds)
                ->get()
                ->keyBy('id');

            $transaction->details()->delete();

            /*
             * Terapkan detail transaksi yang baru.
             */
            foreach ($validated['items'] as $row) {
                $itemId = (int) $row['item_id'];
                $item = $items->get($itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak ditemukan.',
                    ]);
                }

                $quantity = (float) $row['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $unitPrice = (
                    isset($row['unit_price']) &&
                    $row['unit_price'] !== '' &&
                    $row['unit_price'] !== null
                )
                    ? (float) $row['unit_price']
                    : (float) ($item->price ?? 0);

                if ($unitPrice < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Harga barang tidak boleh negatif.',
                    ]);
                }

                $stock = $stocks->get($itemId);

                if (!$stock) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }

                if ($transaction->type === 'IN') {
                    $supplierId = (int) $validated['supplier_id'];

                    if ((int) $item->supplier_id !== $supplierId) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Barang tidak sesuai dengan supplier yang dipilih.',
                        ]);
                    }

                    $stock->quantity =
                        (float) $stock->quantity + $quantity;
                } else {
                    $supplierId = (int) $row['supplier_id'];

                    if ((float) $stock->quantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok ' . $item->name
                                . ' tidak mencukupi. Stok tersedia: '
                                . number_format(
                                    (float) $stock->quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . ', kebutuhan: '
                                . number_format(
                                    $quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . '.',
                        ]);
                    }

                    $stock->quantity =
                        (float) $stock->quantity - $quantity;
                }

                $stock->save();

                $transaction->details()->create([
                    'item_id' => $itemId,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'unit' => !empty($row['unit'])
                        ? $row['unit']
                        : $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $quantity * $unitPrice,
                ]);
            }

            $transaction->transaction_date =
                $validated['transaction_date'];

            $transaction->notes = $validated['notes'] ?? null;

            if ($transaction->type === 'IN') {
                $transaction->supplier_id =
                    (int) $validated['supplier_id'];
            }

            $transaction->save();

            /*
             * Sinkronkan Invoice jika transaksi adalah Barang Keluar.
             */
            if ($transaction->type === 'OUT') {
                $invoice = Invoice::where(
                    'stock_transaction_id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->first();

                if ($invoice) {
                    $transaction->load([
                        'details.item',
                        'details.supplier',
                    ]);

                    $invoice->details()->delete();

                    $invoiceTotal = 0;

                    foreach ($transaction->details as $outDetail) {
                        $subtotal =
                            (float) $outDetail->quantity
                            * (float) $outDetail->unit_price;

                        $invoice->details()->create([
                            'supplier_id' => $outDetail->supplier_id,
                            'item_id' => $outDetail->item_id,
                            'quantity' => $outDetail->quantity,
                            'unit' => $outDetail->unit,
                            'unit_price' => $outDetail->unit_price,
                            'subtotal' => $subtotal,
                            'notes' => null,
                        ]);

                        $invoiceTotal += $subtotal;
                    }

                    $invoice->update([
                        'invoice_date' => $transaction->transaction_date,
                        'purchase_order_id' =>
                            $transaction->purchase_order_id,
                        'kitchen_id' => $transaction->kitchen_id,
                        'total_amount' => $invoiceTotal,
                    ]);
                }
            }
        });

        $transactionType = StockTransaction::whereKey($id)
            ->value('type');

        return redirect()
            ->route(
                $transactionType === 'OUT'
                    ? 'stock-transactions.out'
                    : 'stock-transactions.index'
            )
            ->with(
                'success',
                $transactionType === 'OUT'
                    ? 'Transaksi barang keluar berhasil diperbarui. Invoice terkait juga telah disinkronkan jika tersedia.'
                    : 'Transaksi barang masuk berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS BARANG MASUK / BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transaction = StockTransaction::with('details')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $hasInvoice = Invoice::where(
                    'stock_transaction_id',
                    $transaction->id
                )->exists();

                if ($hasInvoice) {
                    throw ValidationException::withMessages([
                        'transaction' =>
                            'Transaksi tidak dapat dihapus karena masih terhubung dengan Invoice. Tangani Invoice terkait terlebih dahulu.',
                    ]);
                }

                $itemIds = $transaction->details
                    ->pluck('item_id')
                    ->unique()
                    ->sort()
                    ->values();

                $stocks = Stock::whereIn('item_id', $itemIds)
                    ->orderBy('item_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('item_id');

                foreach ($transaction->details as $detail) {
                    $stock = $stocks->get($detail->item_id);

                    if (!$stock) {
                        if ($transaction->type === 'IN') {
                            throw ValidationException::withMessages([
                                'items' =>
                                    'Stok barang tidak ditemukan; transaksi tidak dapat dihapus.',
                            ]);
                        }

                        $stock = Stock::create([
                            'item_id' => $detail->item_id,
                            'quantity' => 0,
                        ]);

                        $stocks->put($detail->item_id, $stock);
                    }

                    if ($transaction->type === 'IN') {
                        $remaining = (float) $stock->quantity
                            - (float) $detail->quantity;

                        if ($remaining < 0) {
                            throw ValidationException::withMessages([
                                'items' =>
                                    'Stok saat ini tidak cukup untuk membatalkan barang masuk. Periksa transaksi stok terkait terlebih dahulu.',
                            ]);
                        }

                        $stock->quantity = $remaining;
                    } else {
                        $stock->quantity = (float) $stock->quantity
                            + (float) $detail->quantity;
                    }

                    $stock->save();
                }

                $transaction->details()->delete();
                $transaction->delete();
            });

            return redirect()
                ->back()
                ->with('success', 'Transaksi berhasil dihapus.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Transaksi gagal dihapus. Periksa apakah transaksi masih terhubung dengan Invoice, Nota Keluar, atau data lain.'
                );
        }
    }
}