<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Kitchen;
use App\Models\Invoice;
use App\Models\NotaKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        return view('stock_transactions.index', compact(
            'transactions',
            'suppliers'
        ));
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

        return view('stock_transactions.create', compact(
            'suppliers',
            'items'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_note_number' => [
                'required',
                'string',
                'max:100',
            ],
            'vendor_name' => [
                'required',
                'string',
                'max:150',
            ],
            'transaction_date' => [
                'required',
                'date',
            ],
            'porter_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'purchase_discount_type' => [
                'nullable',
                'in:amount,percent',
            ],
            'purchase_discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
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
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.discount' => [
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
            'purchase_note_number.required' =>
                'Nomor nota pembelian wajib diisi.',
            'vendor_name.required' =>
                'Nama vendor wajib diisi.',
            'transaction_date.required' =>
                'Tanggal pembelian wajib diisi.',
            'porter_cost.numeric' =>
                'Biaya kuli harus berupa angka.',
            'porter_cost.min' =>
                'Biaya kuli tidak boleh negatif.',
            'purchase_discount_type.required' =>
                'Jenis diskon tambahan nota wajib dipilih.',
            'purchase_discount_type.in' =>
                'Jenis diskon tambahan nota tidak valid.',
            'purchase_discount_value.required' =>
                'Nilai diskon tambahan nota wajib diisi.',
            'purchase_discount_value.numeric' =>
                'Nilai diskon tambahan nota harus berupa angka.',
            'purchase_discount_value.min' =>
                'Diskon tambahan nota tidak boleh negatif.',
            'items.required' =>
                'Tambahkan minimal satu barang.',
            'items.min' =>
                'Tambahkan minimal satu barang.',
            'items.*.item_id.distinct' =>
                'Barang yang sama tidak boleh ditambahkan dua kali.',
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.unit_price.required' =>
                'Harga satuan wajib diisi.',
            'items.*.unit_price.min' =>
                'Harga satuan tidak boleh negatif.',
            'items.*.discount.min' =>
                'Diskon barang tidak boleh negatif.',
        ]);

        $itemsData = collect($validated['items'])
            ->filter(
                fn ($row) => (float) ($row['quantity'] ?? 0) > 0
            )
            ->values();

        if ($itemsData->isEmpty()) {
            throw ValidationException::withMessages([
                'items' =>
                    'Isi jumlah minimal satu barang yang benar-benar masuk.',
            ]);
        }

        $porterCost = round(
            (float) ($validated['porter_cost'] ?? 0),
            2
        );

        $purchaseDiscountType =
            $validated['purchase_discount_type'] ?? 'amount';

        $purchaseDiscountValue = round(
            (float) ($validated['purchase_discount_value'] ?? 0),
            2
        );

        if (
            $purchaseDiscountType === 'percent'
            && $purchaseDiscountValue > 100
        ) {
            throw ValidationException::withMessages([
                'purchase_discount_value' =>
                    'Diskon persentase tidak boleh lebih dari 100%.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $itemsData,
            $porterCost,
            $purchaseDiscountType,
            $purchaseDiscountValue
        ) {
            $itemIds = $itemsData
                ->pluck('item_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            $items = Item::whereIn('id', $itemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $stockItemIds = $items
                ->filter(fn ($item) => $item->track_stock)
                ->keys();

            $stocks = Stock::whereIn('item_id', $stockItemIds)
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            foreach ($stockItemIds as $itemId) {
                if (!$stocks->has($itemId)) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }
            }

            do {
                $transactionNumber = 'IN-'
                    . now()->format('YmdHis')
                    . '-'
                    . Str::upper(Str::random(4));
            } while (
                StockTransaction::where(
                    'transaction_number',
                    $transactionNumber
                )->exists()
            );

            $transaction = StockTransaction::create([
                'transaction_number' => $transactionNumber,
                'type' => 'IN',
                'supplier_id' => null,
                'vendor_name' => trim($validated['vendor_name']),
                'purchase_note_number' => trim(
                    $validated['purchase_note_number']
                ),
                'porter_cost' => $porterCost,
                'purchase_discount_type' => $purchaseDiscountType,
                'purchase_discount_value' => $purchaseDiscountValue,
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($itemsData as $row) {
                $itemId = (int) $row['item_id'];
                $item = $items->get($itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak ditemukan.',
                    ]);
                }

                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];
                $discount = (float) ($row['discount'] ?? 0);

                /*
                 * DISKON BARANG ADALAH DISKON TOTAL PER BARIS.
                 *
                 * Contoh:
                 * Quantity    = 10
                 * Harga       = 7.000
                 * Diskon      = 1.000
                 * Subtotal    = (10 x 7.000) - 1.000
                 *             = 69.000
                 */
                $subtotalBeforeDiscount = $quantity * $unitPrice;

                if ($discount > $subtotalBeforeDiscount) {
                    throw ValidationException::withMessages([
                        'items' => 'Diskon barang "'
                            . $item->name
                            . '" tidak boleh melebihi total harga barang.',
                    ]);
                }

                $unit = !empty($row['unit'])
                    ? $row['unit']
                    : $item->unit;

                $subtotal = round(
                    max(0, $subtotalBeforeDiscount - $discount),
                    2
                );

                $transaction->details()->create([
                    'item_id' => $itemId,
                    'supplier_id' => $item->supplier_id,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                    'allocated_porter_cost' => 0,
                ]);

                /*
                 * Perbarui stok hanya jika item memang melacak stok.
                 */
                if ($item->track_stock) {
                    $stock = $stocks->get($itemId);

                    if (!$stock) {
                        $stock = Stock::create([
                            'item_id' => $itemId,
                            'quantity' => 0,
                        ]);

                        $stocks->put($itemId, $stock);
                    }

                    $stock->quantity =
                        (float) $stock->quantity + $quantity;

                    $stock->save();
                }
            }

            $goodsSubtotal = round(
                (float) $transaction->details()->sum('subtotal'),
                2
            );

            $purchaseDiscountAmount = $this->calculatePurchaseDiscount(
                $goodsSubtotal,
                $purchaseDiscountType,
                $purchaseDiscountValue
            );

            if ($purchaseDiscountAmount > $goodsSubtotal) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon tambahan nota tidak boleh melebihi subtotal barang.',
                ]);
            }

            $this->allocatePorterCost(
                $transaction,
                $porterCost
            );
        });

        return redirect()
            ->route('stock-transactions.index')
            ->with(
                'success',
                'Barang masuk berhasil disimpan. Stok, diskon nota, dan alokasi biaya kuli telah diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG KELUAR
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
        ])->where('type', 'OUT');

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
                            $subQuery
                                ->where(
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
            $query->where('kitchen_id', $request->input('kitchen_id'));
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

        return view('stock_transactions.out_index', compact(
            'transactions',
            'suppliers',
            'kitchens'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL TRANSAKSI
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
            'purchaseOrder.details',
        ])->findOrFail($id);

        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        if ($stockTransaction->type === 'IN') {
            return view('stock_transactions.edit_in', compact(
                'stockTransaction',
                'items',
                'suppliers',
                'kitchens'
            ));
        }

        return view('stock_transactions.edit', compact(
            'stockTransaction',
            'items',
            'suppliers',
            'kitchens'
        ));
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
                // Sengaja tidak memakai distinct:
                // satu barang boleh muncul pada beberapa baris.
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
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],
            'items.*.section_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'items.*.section_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'items.*.reimburse' => [
                'nullable',
                'boolean',
            ],
        ];

        if ($existingTransaction->type === 'IN') {
            $rules['vendor_name'] = [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ];

            $rules['purchase_note_number'] = [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ];

            $rules['porter_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ];

            $rules['purchase_discount_type'] = [
                'sometimes',
                'nullable',
                'in:amount,percent',
            ];

            $rules['purchase_discount_value'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];
        }

        if ($existingTransaction->type === 'OUT') {
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
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.discount.numeric' =>
                'Diskon barang harus berupa angka.',
            'items.*.discount.min' =>
                'Diskon barang tidak boleh negatif.',
            'items.*.supplier_id.required' =>
                'Supplier setiap barang wajib dipilih.',
            'items.*.supplier_id.exists' =>
                'Supplier barang tidak ditemukan.',
            'items.*.section_order.integer' =>
                'Urutan Bagian harus berupa angka.',
            'items.*.reimburse.boolean' =>
                'Status Reimburse tidak valid.',
            'porter_cost.numeric' =>
                'Biaya kuli harus berupa angka.',
            'porter_cost.min' =>
                'Biaya kuli tidak boleh negatif.',
            'purchase_discount_type.in' =>
                'Jenis diskon tambahan nota tidak valid.',
            'purchase_discount_value.numeric' =>
                'Nilai diskon tambahan nota harus berupa angka.',
            'purchase_discount_value.min' =>
                'Diskon tambahan nota tidak boleh negatif.',
        ]);

        if ($existingTransaction->type === 'IN') {
            $discountType = array_key_exists(
                'purchase_discount_type',
                $validated
            )
                ? ($validated['purchase_discount_type'] ?: 'amount')
                : (
                    $existingTransaction->purchase_discount_type
                    ?: 'amount'
                );

            $discountValue = array_key_exists(
                'purchase_discount_value',
                $validated
            )
                ? (float) ($validated['purchase_discount_value'] ?? 0)
                : (float) (
                    $existingTransaction->purchase_discount_value ?? 0
                );

            if ($discountType === 'percent' && $discountValue > 100) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon persentase tidak boleh lebih dari 100%.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $id) {
            $transaction = StockTransaction::with([
                'details.item',
                'purchaseOrder.details',
            ])
                ->lockForUpdate()
                ->findOrFail($id);

            $oldDetails = $transaction->details->values();

            $purchaseOrderDetails = $transaction->purchaseOrder?->details
                ?? collect();

            $oldItemIds = $oldDetails->pluck('item_id');

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
            * Kembalikan dampak semua detail lama terhadap stok.
            * Jika satu barang memiliki beberapa baris, setiap baris
            * dikembalikan satu kali sesuai jumlah masing-masing.
            */
            foreach ($oldDetails as $detail) {
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

            $items = Item::whereIn('id', $newItemIds->unique())
                ->get()
                ->keyBy('id');

            /*
            * Hapus detail lama setelah dampak stoknya dikembalikan.
            * Seluruh detail baru kemudian disimpan dari input form.
            */
            $transaction->details()->delete();

            /*
            * Penanda agar detail lama yang sama tidak dipakai
            * berulang kali saat mencocokkan baris baru.
            */
            $usedOldDetailIds = [];
            $usedPoDetailIds = [];

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
                    isset($row['unit_price'])
                    && $row['unit_price'] !== ''
                    && $row['unit_price'] !== null
                )
                    ? (float) $row['unit_price']
                    : (float) ($item->price ?? 0);

                $discount = (float) ($row['discount'] ?? 0);

                if ($unitPrice < 0 || $discount < 0) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Harga dan diskon barang tidak boleh negatif.',
                    ]);
                }

                $subtotalBeforeDiscount = $quantity * $unitPrice;

                if (
                    $transaction->type === 'IN'
                    && $discount > $subtotalBeforeDiscount
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Diskon barang "'
                            . $item->name
                            . '" tidak boleh melebihi total harga barang.',
                    ]);
                }

                /*
                * Cari stok berdasarkan ID barang.
                * Semua baris barang yang sama memakai record stok
                * yang sama, bukan membuat stok duplikat.
                */
                $stock = $stocks->get($itemId);

                if (!$stock) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }

                if ($transaction->type === 'IN') {
                    $supplierId = $item->supplier_id
                        ? (int) $item->supplier_id
                        : null;

                    $stock->quantity =
                        (float) $stock->quantity + $quantity;
                } else {
                    $supplierId = (int) ($row['supplier_id'] ?? 0);

                    if (!Supplier::whereKey($supplierId)->exists()) {
                        throw ValidationException::withMessages([
                            'items' => 'Supplier barang tidak ditemukan.',
                        ]);
                    }

                    /*
                    * Validasi dilakukan untuk setiap baris terhadap
                    * stok terkini setelah baris sebelumnya diproses.
                    */
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

                /*
                * Cocokkan detail lama satu per satu.
                * Utamakan barang, supplier, dan Bagian yang sama.
                * Ini mencegah semua baris duplikat mengambil detail lama
                * yang sama sehingga Bagian/Reimburse tertukar.
                */
                $sectionName = trim(
                    (string) ($row['section_name'] ?? '')
                );

                $oldDetail = $oldDetails->first(
                    function ($detail) use (
                        $itemId,
                        $supplierId,
                        $sectionName,
                        &$usedOldDetailIds,
                        $transaction
                    ) {
                        if (in_array(
                            $detail->id,
                            $usedOldDetailIds
                        )) {
                            return false;
                        }

                        if ((int) $detail->item_id !== $itemId) {
                            return false;
                        }

                        $oldSupplierId = $detail->supplier_id
                            ? (int) $detail->supplier_id
                            : null;

                        if (
                            $transaction->type === 'OUT'
                            && $oldSupplierId !== $supplierId
                        ) {
                            return false;
                        }

                        if ($sectionName !== '') {
                            return trim(
                                (string) ($detail->section_name ?? '')
                            ) === $sectionName;
                        }

                        return true;
                    }
                );

                /*
                * Jika tidak ditemukan kecocokan Bagian, ambil detail
                * lama pertama yang belum dipakai untuk barang/supplier
                * yang sama.
                */
                if (!$oldDetail) {
                    $oldDetail = $oldDetails->first(
                        function ($detail) use (
                            $itemId,
                            $supplierId,
                            &$usedOldDetailIds,
                            $transaction
                        ) {
                            if (in_array(
                                $detail->id,
                                $usedOldDetailIds
                            )) {
                                return false;
                            }

                            if ((int) $detail->item_id !== $itemId) {
                                return false;
                            }

                            $oldSupplierId = $detail->supplier_id
                                ? (int) $detail->supplier_id
                                : null;

                            return $transaction->type !== 'OUT'
                                || $oldSupplierId === $supplierId;
                        }
                    );
                }

                if ($oldDetail) {
                    $usedOldDetailIds[] = $oldDetail->id;
                }

                /*
                * Cari detail PO yang belum dipakai.
                * Utamakan Bagian yang sama jika tersedia.
                */
                $poDetail = $purchaseOrderDetails->first(
                    function ($detail) use (
                        $itemId,
                        $supplierId,
                        $sectionName,
                        &$usedPoDetailIds
                    ) {
                        if (in_array(
                            $detail->id,
                            $usedPoDetailIds
                        )) {
                            return false;
                        }

                        if ((int) $detail->item_id !== $itemId) {
                            return false;
                        }

                        $detailSupplierId = $detail->supplier_id
                            ?? $detail->item?->supplier_id
                            ?? null;

                        $detailSupplierId = $detailSupplierId
                            ? (int) $detailSupplierId
                            : null;

                        if ($detailSupplierId !== $supplierId) {
                            return false;
                        }

                        if ($sectionName !== '') {
                            return trim(
                                (string) ($detail->section_name ?? '')
                            ) === $sectionName;
                        }

                        return true;
                    }
                );

                if (!$poDetail) {
                    $poDetail = $purchaseOrderDetails->first(
                        function ($detail) use (
                            $itemId,
                            $supplierId,
                            &$usedPoDetailIds
                        ) {
                            if (in_array(
                                $detail->id,
                                $usedPoDetailIds
                            )) {
                                return false;
                            }

                            if ((int) $detail->item_id !== $itemId) {
                                return false;
                            }

                            $detailSupplierId = $detail->supplier_id
                                ?? $detail->item?->supplier_id
                                ?? null;

                            $detailSupplierId = $detailSupplierId
                                ? (int) $detailSupplierId
                                : null;

                            return $detailSupplierId === $supplierId;
                        }
                    );
                }

                if ($poDetail) {
                    $usedPoDetailIds[] = $poDetail->id;
                }

                /*
                * Pertahankan Bagian dari form.
                * Jika kosong, gunakan data lama atau data PO.
                */
                if ($sectionName === '') {
                    $sectionName = trim((string) (
                        $oldDetail?->section_name
                        ?? $poDetail?->section_name
                        ?? ''
                    ));
                }

                $sectionOrder = $row['section_order'] ?? null;

                if ($sectionOrder === null || $sectionOrder === '') {
                    $sectionOrder = $oldDetail?->section_order
                        ?? $poDetail?->section_order
                        ?? 0;
                }

                /*
                * Checkbox form menggunakan hidden 0 dan checkbox 1.
                * Jika nilai reimburse ada dalam input, ikuti nilai itu.
                * Jika tidak ada, pertahankan nilai detail lama/PO.
                */
                if (array_key_exists('reimburse', $row)) {
                    $isReimburse = (bool) $row['reimburse'];
                } else {
                    $oldNotes = trim(
                        (string) ($oldDetail?->notes ?? '')
                    );

                    $poNotes = trim(
                        (string) ($poDetail?->notes ?? '')
                    );

                    $isReimburse = (
                        $oldNotes === '*Reimburse'
                        || $poNotes === '*Reimburse'
                    );
                }

                $subtotal = $transaction->type === 'IN'
                    ? round(
                        max(0, $subtotalBeforeDiscount - $discount),
                        2
                    )
                    : round($quantity * $unitPrice, 2);

                $detailData = [
                    'item_id' => $itemId,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'unit' => !empty($row['unit'])
                        ? $row['unit']
                        : $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                if ($transaction->type === 'IN') {
                    $detailData['discount'] = $discount;
                    $detailData['allocated_porter_cost'] = 0;
                }

                if ($transaction->type === 'OUT') {
                    $detailData['section_name'] = $sectionName !== ''
                        ? $sectionName
                        : null;

                    $detailData['section_order'] = (int) $sectionOrder;
                }

                /*
                * Simpan penanda Reimburse jika kolom tersedia.
                */
                $detailTable = $transaction->details()
                    ->getModel()
                    ->getTable();

                if (Schema::hasColumn($detailTable, 'notes')) {
                    $detailData['notes'] = $isReimburse
                        ? '*Reimburse'
                        : null;
                } elseif (
                    $transaction->type === 'OUT'
                    && $isReimburse
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Kolom notes pada detail transaksi belum tersedia. Tambahkan kolom tersebut sebelum menyimpan Reimburse.',
                    ]);
                }

                $transaction->details()->create($detailData);
            }

            /*
            * Simpan header transaksi.
            */
            $transaction->transaction_date =
                $validated['transaction_date'];

            $transaction->notes = $validated['notes'] ?? null;

            if ($transaction->type === 'IN') {
                $transaction->supplier_id = null;

                if (array_key_exists('vendor_name', $validated)) {
                    $transaction->vendor_name = $validated['vendor_name'];
                }

                if (array_key_exists('purchase_note_number', $validated)) {
                    $transaction->purchase_note_number =
                        $validated['purchase_note_number'];
                }

                if (array_key_exists('porter_cost', $validated)) {
                    $transaction->porter_cost = round(
                        (float) ($validated['porter_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('purchase_discount_type', $validated)) {
                    $transaction->purchase_discount_type =
                        $validated['purchase_discount_type'] ?: 'amount';
                } elseif (!$transaction->purchase_discount_type) {
                    $transaction->purchase_discount_type = 'amount';
                }

                if (array_key_exists('purchase_discount_value', $validated)) {
                    $transaction->purchase_discount_value = round(
                        (float) ($validated['purchase_discount_value'] ?? 0),
                        2
                    );
                } elseif ($transaction->purchase_discount_value === null) {
                    $transaction->purchase_discount_value = 0;
                }

                if (
                    $transaction->purchase_discount_type === 'percent'
                    && (float) $transaction->purchase_discount_value > 100
                ) {
                    throw ValidationException::withMessages([
                        'purchase_discount_value' =>
                            'Diskon persentase tidak boleh lebih dari 100%.',
                    ]);
                }

                $goodsSubtotal = round(
                    (float) $transaction->details()->sum('subtotal'),
                    2
                );

                $discountAmount = $this->calculatePurchaseDiscount(
                    $goodsSubtotal,
                    (string) (
                        $transaction->purchase_discount_type ?? 'amount'
                    ),
                    (float) (
                        $transaction->purchase_discount_value ?? 0
                    )
                );

                if ($discountAmount > $goodsSubtotal) {
                    throw ValidationException::withMessages([
                        'purchase_discount_value' =>
                            'Diskon tambahan nota tidak boleh melebihi subtotal barang.',
                    ]);
                }
            }

            $transaction->save();

            if ($transaction->type === 'IN') {
                $this->allocatePorterCost(
                    $transaction,
                    (float) ($transaction->porter_cost ?? 0)
                );
            }

            if ($transaction->type === 'OUT') {
                app(InvoiceController::class)->syncFromOut($transaction);
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
                    ? 'Barang Keluar berhasil diperbarui. Stok dan Invoice telah disinkronkan jika Invoice tersedia.'
                    : 'Transaksi Barang Masuk berhasil diperbarui. Stok, diskon nota, dan alokasi biaya kuli telah dihitung ulang.'
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
            $transactionType = null;

            DB::transaction(function () use ($id, &$transactionType) {
                $transaction = StockTransaction::with([
                    'details.item',
                    'purchaseOrder',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $transactionType = $transaction->type;

                $invoice = Invoice::where(
                    'stock_transaction_id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->first();

                $notaIds = NotaKeluar::where(
                    'stock_transaction_id',
                    $transaction->id
                )->pluck('id')->all();

                $detailsByItem = $transaction->details
                    ->filter(fn ($detail) => $detail->item_id !== null)
                    ->groupBy('item_id');

                $itemIds = $detailsByItem->keys()
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values();

                $stocks = Stock::whereIn('item_id', $itemIds)
                    ->orderBy('item_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('item_id');

                if ($transaction->type === 'IN') {
                    foreach ($detailsByItem as $itemId => $details) {
                        $itemId = (int) $itemId;

                        $requiredQuantity = (float) $details->sum(
                            fn ($detail) => (float) $detail->quantity
                        );

                        $stock = $stocks->get($itemId);

                        $availableQuantity = $stock
                            ? (float) $stock->quantity
                            : 0;

                        if ($availableQuantity < $requiredQuantity) {
                            $itemName = $details->first()->item?->name
                                ?? Item::whereKey($itemId)->value('name')
                                ?? 'Barang ID ' . $itemId;

                            $formatQuantity = function ($quantity) {
                                return rtrim(
                                    rtrim(
                                        number_format(
                                            (float) $quantity,
                                            2,
                                            ',',
                                            '.'
                                        ),
                                        '0'
                                    ),
                                    ','
                                );
                            };

                            throw ValidationException::withMessages([
                                'transaction' => sprintf(
                                    'Barang "%s" tidak dapat dihapus. Jumlah pada transaksi yang ingin dibatalkan: %s. Stok tersedia saat ini: %s. Penghapusan dibatalkan agar stok tidak menjadi minus. Periksa transaksi Barang Keluar terkait terlebih dahulu.',
                                    $itemName,
                                    $formatQuantity($requiredQuantity),
                                    $formatQuantity($availableQuantity)
                                ),
                            ]);
                        }
                    }
                }

                if (Schema::hasTable('invoice_nota_allocations')) {
                    $allocationQuery = DB::table(
                        'invoice_nota_allocations'
                    );

                    $hasAllocation = false;

                    if (!empty($notaIds)) {
                        $allocationQuery->whereIn(
                            'nota_keluar_id',
                            $notaIds
                        );

                        $hasAllocation = true;
                    }

                    if (
                        $invoice
                        && Schema::hasTable('invoice_details')
                    ) {
                        $invoiceDetailIds = DB::table('invoice_details')
                            ->where('invoice_id', $invoice->id)
                            ->pluck('id')
                            ->all();

                        if (!empty($invoiceDetailIds)) {
                            if ($hasAllocation) {
                                $allocationQuery->orWhereIn(
                                    'invoice_detail_id',
                                    $invoiceDetailIds
                                );
                            } else {
                                $allocationQuery->whereIn(
                                    'invoice_detail_id',
                                    $invoiceDetailIds
                                );
                            }

                            $hasAllocation = true;
                        }
                    }

                    if ($hasAllocation) {
                        $allocationQuery->delete();
                    }
                }

                if (!empty($notaIds)) {
                    foreach ([
                        'nota_keluar_details',
                        'nota_keluar_items',
                    ] as $detailTable) {
                        if (
                            Schema::hasTable($detailTable)
                            && Schema::hasColumn(
                                $detailTable,
                                'nota_keluar_id'
                            )
                        ) {
                            DB::table($detailTable)
                                ->whereIn('nota_keluar_id', $notaIds)
                                ->delete();
                        }
                    }

                    NotaKeluar::where(
                        'stock_transaction_id',
                        $transaction->id
                    )->delete();
                }

                if ($invoice) {
                    if (Schema::hasTable('invoice_details')) {
                        DB::table('invoice_details')
                            ->where('invoice_id', $invoice->id)
                            ->delete();
                    }

                    $invoice->delete();
                }

                foreach ($transaction->details as $detail) {
                    if (!$detail->item_id) {
                        continue;
                    }

                    $itemId = (int) $detail->item_id;
                    $stock = $stocks->get($itemId);

                    if (!$stock) {
                        if ($transaction->type === 'IN') {
                            throw ValidationException::withMessages([
                                'transaction' =>
                                    'Data stok barang masuk tidak ditemukan. Transaksi tidak dihapus.',
                            ]);
                        }

                        $stock = Stock::create([
                            'item_id' => $itemId,
                            'quantity' => 0,
                        ]);

                        $stocks->put($itemId, $stock);
                    }

                    if ($transaction->type === 'IN') {
                        $stock->quantity = (float) $stock->quantity
                            - (float) $detail->quantity;
                    } else {
                        $stock->quantity = (float) $stock->quantity
                            + (float) $detail->quantity;
                    }

                    $stock->save();
                }

                $purchaseOrderId = $transaction->purchase_order_id;

                $transaction->details()->delete();
                $transaction->delete();

                if (
                    $purchaseOrderId
                    && Schema::hasTable('purchase_orders')
                    && Schema::hasColumn('purchase_orders', 'status')
                ) {
                    $poUpdate = [
                        'status' => 'RECEIVED',
                    ];

                    if (
                        Schema::hasColumn(
                            'purchase_orders',
                            'processed_at'
                        )
                    ) {
                        $poUpdate['processed_at'] = null;
                    }

                    DB::table('purchase_orders')
                        ->where('id', $purchaseOrderId)
                        ->update($poUpdate);
                }
            });

            return redirect()
                ->route(
                    $transactionType === 'OUT'
                        ? 'stock-transactions.out'
                        : 'stock-transactions.index'
                )
                ->with(
                    'success',
                    $transactionType === 'OUT'
                        ? 'Transaksi Barang Keluar beserta Invoice dan Nota terkait berhasil dihapus. Stok telah disesuaikan.'
                        : 'Transaksi Barang Masuk berhasil dihapus. Stok telah disesuaikan.'
                );

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Transaksi gagal dihapus. Periksa storage/logs/laravel.log untuk detail error.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function editIn(StockTransaction $stockTransaction)
    {
        abort_unless($stockTransaction->type === 'IN', 404);

        $stockTransaction->load([
            'details.item',
            'details.supplier',
        ]);

        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        return view('stock_transactions.edit_in', compact(
            'stockTransaction',
            'suppliers',
            'items',
            'kitchens'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | HITUNG DISKON TAMBAHAN NOTA
    |--------------------------------------------------------------------------
    */

    private function calculatePurchaseDiscount(
        float $goodsSubtotal,
        string $discountType,
        float $discountValue
    ): float {
        $goodsSubtotal = round(max(0, $goodsSubtotal), 2);
        $discountValue = round(max(0, $discountValue), 2);

        if ($discountType === 'percent') {
            if ($discountValue > 100) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon persentase tidak boleh lebih dari 100%.',
                ]);
            }

            return round(
                $goodsSubtotal * $discountValue / 100,
                2
            );
        }

        return round(min($goodsSubtotal, $discountValue), 2);
    }

    /*
    |--------------------------------------------------------------------------
    | ALOKASI BIAYA KULI
    |--------------------------------------------------------------------------
    */

    private function allocatePorterCost(
        StockTransaction $transaction,
        float $porterCost
    ): void {
        $porterCost = round($porterCost, 2);

        if ($porterCost < 0) {
            throw ValidationException::withMessages([
                'porter_cost' =>
                    'Biaya kuli tidak boleh kurang dari Rp0.',
            ]);
        }

        $details = $transaction->details()
            ->orderBy('id')
            ->get();

        $totalSubtotal = round(
            (float) $details->sum('subtotal'),
            2
        );

        if ($porterCost > 0 && $totalSubtotal <= 0) {
            throw ValidationException::withMessages([
                'porter_cost' =>
                    'Biaya kuli tidak dapat dialokasikan karena total subtotal barang adalah Rp0.',
            ]);
        }

        if ($details->isEmpty()) {
            if ($porterCost > 0) {
                throw ValidationException::withMessages([
                    'porter_cost' =>
                        'Tambahkan barang terlebih dahulu sebelum mengisi biaya kuli.',
                ]);
            }

            return;
        }

        $remainingCost = $porterCost;
        $lastIndex = $details->count() - 1;

        foreach ($details as $index => $detail) {
            if ($porterCost <= 0 || $totalSubtotal <= 0) {
                $allocated = 0;
            } elseif ($index === $lastIndex) {
                $allocated = $remainingCost;
            } else {
                $allocated = round(
                    $porterCost
                        * (
                            (float) $detail->subtotal
                            / $totalSubtotal
                        ),
                    2
                );

                $remainingCost = round(
                    $remainingCost - $allocated,
                    2
                );
            }

            $detail->allocated_porter_cost = $allocated;
            $detail->save();
        }
    }
}