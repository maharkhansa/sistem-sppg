<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use App\Models\Item;
use App\Models\Kitchen;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransactionDetail;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with([
            'kitchen',
            'details.supplier',
            'details.item',
            'details.expenseType',
        ])->latest()->get();

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $items = Item::with(['category', 'supplier'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        foreach ($items as $item) {
            $lastIn = StockTransactionDetail::where('item_id', $item->id)
                ->whereHas('stockTransaction', function ($query) {
                    $query->where('type', 'IN');
                })
                ->latest('id')
                ->first();

            $item->last_purchase_price = $lastIn?->unit_price ?? 0;
        }

        $expenseTypes = ExpenseType::where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'purchase_orders.create',
            compact('kitchens', 'items', 'expenseTypes')
        );
    }

    public function store(Request $request)
    {
        /*
         * Validasi data utama PO terlebih dahulu.
         */
        $data = $request->validate([
            'po_date' => ['required', 'date'],
            'kitchen_id' => ['required', 'exists:kitchens,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        /*
         * Gunakan input items dari Request setelah validasi.
         */
        $items = $request->input('items', []);

        /*
         * Validasi setiap detail berdasarkan jenisnya.
         */
        foreach ($items as $index => $row) {
            if (!is_array($row)) {
                throw ValidationException::withMessages([
                    "items.$index" => 'Format detail PO tidak valid.',
                ]);
            }

            $type = $row['detail_type'] ?? 'stock';

            if (!in_array($type, ['stock', 'expense'], true)) {
                throw ValidationException::withMessages([
                    "items.$index.detail_type" =>
                        'Jenis detail PO tidak valid.',
                ]);
            }

            $rules = [
                "items.$index.quantity" => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                "items.$index.unit_price" => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                "items.$index.unit" => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                "items.$index.notes" => [
                    'nullable',
                    'string',
                ],

                "items.$index.section_name" => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                "items.$index.section_order" => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                "items.$index.reimburse" => [
                    'nullable',
                    'boolean',
                ],
            ];

            if ($type === 'expense') {
                $rules["items.$index.expense_type_id"] = [
                    'required',
                    'exists:expense_types,id',
                ];

                $rules["items.$index.expense_date"] = [
                    'required',
                    'date',
                ];

                $rules["items.$index.supplier_name"] = [
                    'nullable',
                    'string',
                    'max:255',
                ];

                $rules["items.$index.item_id"] = [
                    'nullable',
                ];

                $rules["items.$index.supplier_id"] = [
                    'nullable',
                ];
            } else {
                $rules["items.$index.item_id"] = [
                    'required',
                    'exists:items,id',
                ];

                $rules["items.$index.supplier_id"] = [
                    'required',
                    'exists:suppliers,id',
                ];
            }

            $request->validate($rules);
        }

        DB::transaction(function () use ($data, $items) {
            $purchaseOrder = PurchaseOrder::create([
                'kitchen_id' => $data['kitchen_id'],
                'po_number' => $this->generatePoNumber(
                    $data['po_date']
                ),
                'po_date' => $data['po_date'],
                'status' => 'RECEIVED',
                'approved_at' => null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($items as $detail) {
                $type = $detail['detail_type'] ?? 'stock';

                $quantity = (float) ($detail['quantity'] ?? 0);
                $unitPrice = (float) ($detail['unit_price'] ?? 0);

                if ($quantity <= 0 || $unitPrice < 0) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Kuantitas harus lebih dari 0 dan harga tidak boleh negatif.',
                    ]);
                }

                $subtotal = round($quantity * $unitPrice, 2);

                /*
                 * BIAYA NON-STOK
                 */
                if ($type === 'expense') {
                    $expenseType = ExpenseType::findOrFail(
                        $detail['expense_type_id']
                    );

                    $unit = trim(
                        (string) ($detail['unit'] ?? '')
                    );

                    if ($unit === '') {
                        $unit = 'kali';
                    }

                    $supplierName = trim(
                        (string) ($detail['supplier_name'] ?? '')
                    );

                    $purchaseOrder->details()->create([
                        'detail_type' => 'expense',
                        'expense_type_id' => $expenseType->id,
                        'expense_date' => $detail['expense_date'],

                        'supplier_id' => null,
                        'supplier_name' => $supplierName !== ''
                            ? $supplierName
                            : null,

                        'item_id' => null,
                        'quantity' => $quantity,
                        'unit' => $unit,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'notes' => $detail['notes'] ?? null,

                        'section_name' => null,
                        'section_order' => null,
                    ]);

                    continue;
                }

                /*
                 * BARANG PERSEDIAAN
                 */
                $item = Item::with('supplier')->findOrFail(
                    $detail['item_id']
                );

                $supplierId = $item->supplier_id;

                if (!$supplierId) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Supplier belum ditentukan pada master barang '
                            . $item->name . '.',
                    ]);
                }

                $isReimburse = filter_var(
                    $detail['reimburse'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $itemNotes = $isReimburse
                    ? '*Reimburse'
                    : ($detail['notes'] ?? null);

                $purchaseOrder->details()->create([
                    'detail_type' => 'stock',
                    'expense_type_id' => null,
                    'expense_date' => null,

                    'supplier_id' => $supplierId,
                    'supplier_name' => null,

                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => $itemNotes,

                    'section_name' => !empty($detail['section_name'])
                        ? trim((string) $detail['section_name'])
                        : null,

                    'section_order' => !empty($detail['section_name'])
                        && isset($detail['section_order'])
                            ? (int) $detail['section_order']
                            : null,
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil disimpan.');
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'po_date' => ['required', 'date'],
            'kitchen_id' => ['required', 'exists:kitchens,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        $items = $request->input('items', []);

        // Validasi detail PO.
        foreach ($items as $index => $row) {
            if (!is_array($row)) {
                throw ValidationException::withMessages([
                    "items.$index" => 'Format detail PO tidak valid.',
                ]);
            }

            $type = $row['detail_type'] ?? 'stock';

            if (!in_array($type, ['stock', 'expense'], true)) {
                throw ValidationException::withMessages([
                    "items.$index.detail_type" => 'Jenis detail PO tidak valid.',
                ]);
            }

            $rules = [
                "items.$index.quantity" => [
                    'required', 'numeric', 'min:0.01',
                ],
                "items.$index.unit_price" => [
                    'required', 'numeric', 'min:0',
                ],
                "items.$index.unit" => [
                    'nullable', 'string', 'max:30',
                ],
                "items.$index.notes" => [
                    'nullable', 'string',
                ],
                "items.$index.section_name" => [
                    'nullable', 'string', 'max:100',
                ],
                "items.$index.section_order" => [
                    'nullable', 'integer', 'min:1',
                ],
                "items.$index.reimburse" => [
                    'nullable', 'boolean',
                ],
            ];

            if ($type === 'expense') {
                $rules["items.$index.expense_type_id"] = [
                    'required', 'exists:expense_types,id',
                ];
                $rules["items.$index.expense_date"] = [
                    'required', 'date',
                ];
                $rules["items.$index.supplier_name"] = [
                    'nullable', 'string', 'max:255',
                ];
            } else {
                $rules["items.$index.item_id"] = [
                    'required', 'exists:items,id',
                ];
                $rules["items.$index.supplier_id"] = [
                    'required', 'exists:suppliers,id',
                ];
            }

            $request->validate($rules);
        }

        DB::transaction(function () use ($purchaseOrder, $data, $items) {
            // Kunci PO agar tidak diproses atau diedit bersamaan.
            $po = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($po->status !== 'RECEIVED') {
                throw ValidationException::withMessages([
                    'purchase_order' =>
                        'PO yang sudah diproses tidak dapat diedit.',
                ]);
            }

            // Perbarui header PO. Nomor PO tetap.
            $po->update([
                'kitchen_id' => $data['kitchen_id'],
                'po_date' => $data['po_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Hapus detail lama. Semua perubahan berada dalam transaksi.
            $po->details()->delete();

            foreach ($items as $detail) {
                $type = $detail['detail_type'] ?? 'stock';

                $quantity = (float) $detail['quantity'];
                $unitPrice = (float) $detail['unit_price'];
                $subtotal = round($quantity * $unitPrice, 2);

                if ($type === 'expense') {
                    $expenseType = ExpenseType::findOrFail(
                        $detail['expense_type_id']
                    );

                    $unit = trim((string) ($detail['unit'] ?? ''));

                    $supplierName = trim(
                        (string) ($detail['supplier_name'] ?? '')
                    );

                    $po->details()->create([
                        'detail_type' => 'expense',
                        'expense_type_id' => $expenseType->id,
                        'expense_date' => $detail['expense_date'],

                        'supplier_id' => null,
                        'supplier_name' => $supplierName !== ''
                            ? $supplierName
                            : null,

                        'item_id' => null,
                        'quantity' => $quantity,
                        'unit' => $unit !== '' ? $unit : 'kali',
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'notes' => $detail['notes'] ?? null,

                        'section_name' => null,
                        'section_order' => null,
                    ]);

                    continue;
                }

                // Barang persediaan mengikuti supplier master barang.
                $item = Item::findOrFail($detail['item_id']);

                if (!$item->supplier_id) {
                    throw ValidationException::withMessages([
                        'items' => 'Supplier belum ditentukan pada master barang '
                            . $item->name . '.',
                    ]);
                }

                $isReimburse = filter_var(
                    $detail['reimburse'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $itemNotes = $isReimburse
                    ? '*Reimburse'
                    : ($detail['notes'] ?? null);

                $sectionName = trim(
                    (string) ($detail['section_name'] ?? '')
                );

                $po->details()->create([
                    'detail_type' => 'stock',
                    'expense_type_id' => null,
                    'expense_date' => null,

                    'supplier_id' => $item->supplier_id,
                    'supplier_name' => null,

                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => $itemNotes,

                    'section_name' => $sectionName !== ''
                        ? $sectionName
                        : null,

                    'section_order' => $sectionName !== ''
                        && isset($detail['section_order'])
                            ? (int) $detail['section_order']
                            : null,
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($po->status !== 'RECEIVED') {
                throw ValidationException::withMessages([
                    'purchase_order' =>
                        'PO yang sudah diproses tidak dapat dihapus.',
                ]);
            }

            $po->delete();
        });

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        // PO yang sudah diproses tidak boleh diedit.
        if ($purchaseOrder->status !== 'RECEIVED') {
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Purchase Order yang sudah diproses tidak dapat diedit');
        }

        // Ambil detail PO beserta relasinya.
        $purchaseOrder->load([
            'kitchen',
            'details.supplier',
            'details.item',
            'details.expenseType',
        ]);

        $kitchens = Kitchen::where('status', true)
            ->orderBy('name')
            ->get();

        $items = Item::with(['category', 'supplier'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        foreach ($items as $item) {
            $lastIn = StockTransactionDetail::where('item_id', $item->id)
                ->whereHas('stockTransaction', function ($query) {
                    $query->where('type', 'IN');
                })
                ->latest('id')
                ->first();

            $item->last_purchase_price = $lastIn?->unit_price ?? 0;
        }

        $expenseTypes = ExpenseType::where('status', true)
            ->orderBy('name')
            ->get();

        // Siapkan detail lama agar dapat dipulihkan oleh JavaScript form.
        $oldItems = $purchaseOrder->details->map(function ($detail) {
            $item = $detail->item;

            return [
                'detail_type' => $detail->detail_type ?? 'stock',

                'item_id' => $detail->item_id,
                'supplier_id' => $detail->supplier_id,
                'supplier_name' => $detail->supplier_name
                    ?? $detail->supplier?->name,

                'expense_type_id' => $detail->expense_type_id,
                'expense_date' => $detail->expense_date,

                'code' => $item?->code,
                'unit' => $detail->unit,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,

                'reimburse' => trim((string) $detail->notes) === '*Reimburse',

                'notes' => $detail->notes,

                'section_name' => $detail->section_name,
                'section_order' => $detail->section_order,
            ];
        })->values();

        return view(
            'purchase_orders.create',
            compact(
                'purchaseOrder',
                'kitchens',
                'items',
                'expenseTypes',
                'oldItems'
            )
        );
    }

    public function process(Request $request, PurchaseOrder $purchaseOrder)
    {
        /*
         * Tanggal Invoice diinput saat PO diproses.
         * Tanggal ini juga digunakan sebagai tanggal OUT/Nota.
         */
        $validated = $request->validate([
            'invoice_date' => ['required', 'date'],
        ]);

        $invoiceDate = $validated['invoice_date'];

        $invoiceId = null;
        $alreadyProcessed = false;
        $emptyDetails = false;

        try {
            DB::transaction(function () use (
                $purchaseOrder,
                $invoiceDate,
                &$invoiceId,
                &$alreadyProcessed,
                &$emptyDetails
            ) {
                /*
                 * Ambil dan kunci PO di dalam transaksi.
                 */
                $lockedPurchaseOrder = PurchaseOrder::whereKey(
                    $purchaseOrder->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Periksa kembali status setelah memperoleh kunci.
                 */
                if ($lockedPurchaseOrder->status === 'PROCESSED') {
                    $alreadyProcessed = true;

                    return;
                }

                $lockedPurchaseOrder->load([
                    'details.item',
                    'details.supplier',
                    'details.expenseType',
                ]);

                if ($lockedPurchaseOrder->details->isEmpty()) {
                    $emptyDetails = true;

                    return;
                }

                /*
                 * Buat OUT dari PO.
                 * Tanggal OUT mengikuti tanggal Invoice.
                 */
                $stockTransaction = StockTransaction::create([
                    'transaction_number' => $this->generateOutNumber(
                        $invoiceDate
                    ),
                    'transaction_date' => $invoiceDate,
                    'type' => 'OUT',
                    'supplier_id' => null,
                    'kitchen_id' => $lockedPurchaseOrder->kitchen_id,
                    'purchase_order_id' => $lockedPurchaseOrder->id,
                    'notes' => 'OUT dari PO '
                        . $lockedPurchaseOrder->po_number,
                    'created_by' => auth()->id() ?? 1,
                ]);

                $stockDetails = $lockedPurchaseOrder->details->filter(
                    fn ($detail) =>
                        ($detail->detail_type ?? 'stock') !== 'expense'
                );

                foreach ($stockDetails as $detail) {
                    $item = $detail->item;

                    if (!$item) {
                        throw new \RuntimeException(
                            'Barang pada PO tidak ditemukan.'
                        );
                    }

                    $stock = null;

                    /*
                     * Pengecekan stok hanya berlaku untuk barang
                     * yang menggunakan pelacakan stok.
                     */
                    if ($item->track_stock) {
                        $stock = Stock::where('item_id', $item->id)
                            ->lockForUpdate()
                            ->first();

                        if (!$stock) {
                            throw new \RuntimeException(
                                'Stok untuk barang '
                                . $item->name
                                . ' belum tersedia. Silakan lakukan '
                                . 'Barang Masuk terlebih dahulu.'
                            );
                        }

                        if (
                            (float) $stock->quantity
                            < (float) $detail->quantity
                        ) {
                            throw new \RuntimeException(
                                'Stok barang '
                                . $item->name
                                . ' tidak mencukupi. Stok tersedia: '
                                . number_format(
                                    (float) $stock->quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . ', kebutuhan: '
                                . number_format(
                                    (float) $detail->quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . '. Silakan periksa stok barang.'
                            );
                        }
                    }

                    $sectionName = trim(
                        (string) ($detail->section_name ?? '')
                    );

                    /*
                     * Simpan detail OUT.
                     */
                    $stockTransaction->details()->create([
                        'item_id' => $detail->item_id,
                        'supplier_id' => $detail->supplier_id,
                        'quantity' => $detail->quantity,
                        'unit' => $detail->unit,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,

                        'section_name' => $sectionName !== ''
                            ? $sectionName
                            : null,

                        'section_order' => $sectionName !== ''
                            ? $detail->section_order
                            : null,

                        'notes' => $detail->notes,
                    ]);

                    /*
                     * Kurangi stok setelah pengecekan berhasil.
                     */
                    if ($item->track_stock) {
                        $stock->decrement(
                            'quantity',
                            $detail->quantity
                        );
                    }
                }

                /*
                 * Ubah status PO setelah seluruh detail berhasil diproses.
                 */
                $lockedPurchaseOrder->update([
                    'status' => 'PROCESSED',
                    'processed_at' => now(),
                    'processed_by' => auth()->id() ?? 1,
                ]);

                /*
                 * Buat satu Invoice yang mencakup semua detail PO,
                 * termasuk barang persediaan dan biaya non-stok.
                 *
                 * Tanggal dan nomor Invoice mengikuti invoiceDate
                 * dari input form, bukan tanggal PO.
                 */
                $invoice = Invoice::create([
                    'invoice_number' => $this->generateInvoiceNumber(
                        $invoiceDate
                    ),
                    'invoice_date' => $invoiceDate,
                    'stock_transaction_id' => $stockTransaction->id,
                    'purchase_order_id' => $lockedPurchaseOrder->id,
                    'kitchen_id' => $lockedPurchaseOrder->kitchen_id,
                    'total_amount' => $lockedPurchaseOrder->details->sum(
                        'subtotal'
                    ),
                    'status' => 'DRAFT',
                    'notes' => 'Invoice dibuat otomatis dari PO '
                        . $lockedPurchaseOrder->po_number,
                    'created_by' => auth()->id() ?? 1,
                ]);

                /*
                 * Salin seluruh detail PO ke Invoice.
                 */
                foreach ($lockedPurchaseOrder->details as $detail) {
                    $sectionName = trim(
                        (string) ($detail->section_name ?? '')
                    );

                    $invoice->details()->create([
                        'detail_type' => $detail->detail_type ?? 'stock',

                        'supplier_id' => $detail->supplier_id,
                        'supplier_name' => $detail->supplier_name,

                        'item_id' => $detail->item_id,
                        'expense_type_id' => $detail->expense_type_id,
                        'expense_date' => $detail->expense_date,

                        'quantity' => $detail->quantity,
                        'unit' => $detail->unit,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,
                        'notes' => $detail->notes,

                        'section_name' => $sectionName !== ''
                            ? $sectionName
                            : null,

                        'section_order' => $sectionName !== ''
                            ? $detail->section_order
                            : null,
                    ]);
                }

                $invoiceId = $invoice->id;
            });
        } catch (\RuntimeException $exception) {
            /*
             * Transaksi database dibatalkan jika stok kosong,
             * stok tidak cukup, atau barang tidak ditemukan.
             */
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', $exception->getMessage());
        }

        /*
         * Penanganan PO yang sudah pernah diproses.
         */
        if ($alreadyProcessed) {
            return redirect()
                ->route('invoices.index')
                ->with(
                    'error',
                    'Purchase Order ini sudah diproses menjadi OUT.'
                );
        }

        /*
         * Penanganan PO tanpa detail.
         */
        if ($emptyDetails) {
            return redirect()
                ->route('purchase-orders.index')
                ->with(
                    'error',
                    'Purchase Order tidak memiliki detail.'
                );
        }

        /*
         * Jika berhasil, buka Invoice yang baru dibuat.
         */
        return redirect()
            ->route('invoices.show', $invoiceId)
            ->with(
                'success',
                'PO berhasil diproses menjadi OUT dan Invoice berhasil dibuat.'
            );
    }

    public function daftarPesanan(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'kitchen',
            'details.supplier',
            'details.item.category',
            'details.expenseType',
        ]);

        $supplierOrder = function ($name) {
            $name = strtoupper(trim((string) $name));

            if (
                str_contains($name, 'KOPERASI') ||
                str_contains($name, 'SUMBER REJEKI')
            ) {
                return 1;
            }

            if (
                str_contains($name, 'ZENZI') ||
                str_contains($name, 'ZENZIE')
            ) {
                return 2;
            }

            if (str_contains($name, 'GEMILANG')) {
                return 3;
            }

            if (
                str_contains($name, 'TOP FAST') ||
                str_contains($name, 'TOPFAST')
            ) {
                return 4;
            }

            return 5;
        };

        $supplierGroups = $purchaseOrder->details
            ->sortBy(function ($detail) use ($supplierOrder) {
                if (($detail->detail_type ?? 'stock') === 'expense') {
                    return '99-' . str_pad(
                        (string) $detail->id,
                        8,
                        '0',
                        STR_PAD_LEFT
                    );
                }

                return sprintf(
                    '%02d-%06d-%06d',
                    $supplierOrder($detail->supplier?->name),
                    (int) ($detail->section_order ?? 0),
                    (int) ($detail->supplier_id ?? 0)
                );
            })
            ->groupBy(function ($detail) {
                return ($detail->detail_type ?? 'stock') === 'expense'
                    ? 'expense'
                    : ($detail->supplier_id ?? 'tanpa-supplier');
            });

        return view(
            'purchase_orders.daftar-pesanan',
            compact('purchaseOrder', 'supplierGroups')
        );
    }

    private function generatePoNumber($poDate): string
    {
        return $this->generateUniqueNumber(
            PurchaseOrder::class,
            'po_number',
            'PO-' . Carbon::parse($poDate)->format('Ymd') . '-'
        );
    }

    private function generateOutNumber($date): string
    {
        return $this->generateUniqueNumber(
            StockTransaction::class,
            'transaction_number',
            'OUT-' . Carbon::parse($date)->format('Ymd') . '-'
        );
    }

    private function generateInvoiceNumber($invoiceDate): string
    {
        return $this->generateUniqueNumber(
            Invoice::class,
            'invoice_number',
            'INV-' . Carbon::parse($invoiceDate)->format('Ymd') . '-'
        );
    }

    private function generateUniqueNumber(
        string $modelClass,
        string $column,
        string $prefix
    ): string {
        $number = 1;

        do {
            $candidate = $prefix . str_pad(
                (string) $number,
                3,
                '0',
                STR_PAD_LEFT
            );

            if (!$modelClass::where($column, $candidate)->exists()) {
                return $candidate;
            }

            $number++;
        } while (true);
    }

    public function deliveryNote(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'kitchen',
            'details.supplier',
            'details.item',
            'details.expenseType',
        ]);

        return view(
            'purchase_orders.delivery-note',
            compact('purchaseOrder')
        );
    }

    
    public function weeklyRecap(Request $request)
    {
        /*
        * Validasi filter tanggal.
        */
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        /*
        * Tanggal mulai default:
        * Senin pada minggu berjalan.
        *
        * Tanggal sampai default:
        * Kamis pada minggu berjalan.
        */
        $defaultStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $defaultEnd = $defaultStart->copy()->addDays(3);

        $startDate = !empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $defaultStart->copy()->startOfDay();

        $endDate = !empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : (
                !empty($validated['start_date'])
                    ? $startDate->copy()->endOfDay()
                    : $defaultEnd->copy()->endOfDay()
            );

        /*
        * Buat daftar tanggal sesuai rentang yang dipilih.
        * Contoh:
        * 19 Oktober sampai 22 Oktober.
        */
        $days = collect();

        $currentDate = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();

        while ($currentDate->lte($lastDate)) {
            $days->push($currentDate->copy());

            $currentDate->addDay();
        }

        /*
        * Ambil PO pada rentang tanggal yang dipilih.
        * PO yang ditampilkan tetap berstatus RECEIVED.
        */
        $purchaseOrders = PurchaseOrder::with([
            'kitchen',
            'details.supplier',
            'details.item',
        ])
            ->where('status', 'RECEIVED')
            ->whereDate('po_date', '>=', $startDate->toDateString())
            ->whereDate('po_date', '<=', $endDate->toDateString())
            ->get();

       // Ambil dapur berdasarkan PO yang ada dalam periode rekap.
        // Jangan membatasi berdasarkan nama pendek karena nama asli
        // dapur di database bisa panjang.
        $kitchenIds = $purchaseOrders
            ->pluck('kitchen_id')
            ->filter()
            ->unique()
            ->values();

        $kitchenShortNames = [
            'tempuran'       => 'Tempuran',
            'sidowayah'      => 'Sidowayah',
            'ngrundul'       => 'Ngrundul',
            'kedungsari 2'   => 'Potrobangsan',
            'adikarto'       => 'Adikarto',
            'petanjuangan 2' => 'Petanjuangan 2',
            'sawitan'        => 'Sawitan',
            'kalidoni 2'    => 'Kalidoni 2',
        ];

        $kitchens = Kitchen::query()
            ->whereIn('id', $kitchenIds)
            ->get()
            ->map(function ($kitchen) use ($kitchenShortNames) {

                // Gunakan nama asli dari data Kitchen.
                $originalName = $kitchen->display_name
                    ?: $kitchen->name;

                $normalizedName = mb_strtolower($originalName);

                // Cocokkan nama pendek berdasarkan nama dapur asli.
                $shortName = $originalName;

                foreach ($kitchenShortNames as $keyword => $label) {
                    if (str_contains($normalizedName, $keyword)) {
                        $shortName = $label;
                        break;
                    }
                }

                // Nama pendek hanya untuk tampilan rekap.
                $kitchen->recap_name = $shortName;

                return $kitchen;
            })
            ->sortBy('recap_name')
            ->values();

        /*
        * Susun rekap berdasarkan vendor, bahan, dan satuan.
        */
        $recap = [];

        foreach ($purchaseOrders as $po) {
            $dateKey = Carbon::parse($po->po_date)->format('Y-m-d');
            $kitchenId = $po->kitchen_id;

            foreach ($po->details as $detail) {

                /*
                * Abaikan detail biaya non-stok.
                */
                if (($detail->detail_type ?? 'stock') !== 'stock') {
                    continue;
                }

                /*
                * Abaikan detail tanpa relasi barang.
                */
                if (!$detail->item) {
                    continue;
                }

                $quantity = (float) $detail->quantity;

                /*
                * Hanya jumlah positif.
                */
                if ($quantity <= 0) {
                    continue;
                }

                /*
                * Nama vendor mengikuti supplier pada PO.
                */
                $vendorName = $detail->supplier_name
                    ?: $detail->supplier?->name
                    ?: '-';

                $itemName = $detail->item->name;

                $unit = $detail->unit
                    ?: $detail->item->unit
                    ?: '-';

                /*
                * Satu baris untuk kombinasi vendor,
                * nama bahan, dan satuan.
                */
                $rowKey = implode('|', [
                    $vendorName,
                    $itemName,
                    $unit,
                ]);

                if (!isset($recap[$rowKey])) {
                    $recap[$rowKey] = [
                        'vendor' => $vendorName,
                        'item' => $itemName,
                        'unit' => $unit,
                        'quantities' => [],
                    ];
                }

                /*
                * Kolom kuantitas menggunakan kombinasi tanggal
                * dan ID dapur.
                */
                $cellKey = $dateKey . '|' . $kitchenId;

                $recap[$rowKey]['quantities'][$cellKey] =
                    ($recap[$rowKey]['quantities'][$cellKey] ?? 0)
                    + $quantity;
            }
        }

        /*
        * Urutkan berdasarkan nama bahan A-Z.
        */
        $recap = collect($recap)
            ->sortBy(
                fn ($row) => $row['item'] ?? '',
                SORT_NATURAL | SORT_FLAG_CASE
            )
            ->values();

        /*
        * Kirim semua variabel yang dibutuhkan Blade,
        * termasuk startDate dan endDate.
        */
        return view('purchase_orders.weekly-recap', compact(
            'startDate',
            'endDate',
            'days',
            'kitchens',
            'recap'
        ));
    }
}
