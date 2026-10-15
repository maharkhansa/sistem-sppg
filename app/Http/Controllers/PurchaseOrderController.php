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
         * PENTING:
         * Gunakan input items dari Request setelah validasi.
         *
         * Validasi awal hanya memvalidasi array items secara umum.
         * Karena itu, jangan mengandalkan $data['items'] untuk
         * mengambil seluruh field detail yang dikirim dari form.
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

            /*
             * Validasi field detail yang sedang diproses.
             * Jika quantity atau harga tidak dikirim form,
             * Laravel akan mengembalikan error validasi,
             * bukan Undefined array key.
             */
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

                /*
                 * Field berikut sudah divalidasi di atas.
                 * Aksesnya tetap dibuat aman agar tidak menimbulkan
                 * Undefined array key apabila format input berubah.
                 */
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
                 *
                 * Tidak menggunakan item gudang.
                 * Supplier/penerima diketik manual.
                 * Tidak membuat atau mengurangi stok.
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
                 *
                 * Supplier tetap berasal dari master barang.
                 * Supplier manual untuk biaya non-stok tidak digunakan
                 * pada baris persediaan.
                 */
                $item = Item::with('supplier')->findOrFail(
                    $detail['item_id']
                );

                /*
                 * Ambil supplier dari master item sebagai sumber utama.
                 * Field supplier_id dari form tetap divalidasi di atas,
                 * tetapi tidak dijadikan sumber utama saat menyimpan.
                 */
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

    public function process(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'PROCESSED') {
            return redirect()
                ->route('invoices.index')
                ->with(
                    'error',
                    'Purchase Order ini sudah diproses menjadi OUT.'
                );
        }

        $purchaseOrder->load([
            'details.item',
            'details.supplier',
            'details.expenseType',
        ]);

        if ($purchaseOrder->details->isEmpty()) {
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Purchase Order tidak memiliki detail.');
        }

        $invoiceId = null;

        DB::transaction(function () use ($purchaseOrder, &$invoiceId) {
            /*
             * OUT tetap dibuat untuk menjaga hubungan PO, OUT, dan Invoice.
             * Hanya detail barang persediaan yang dimasukkan ke OUT.
             */
            $stockTransaction = StockTransaction::create([
                'transaction_number' => $this->generateOutNumber(
                    $purchaseOrder->po_date
                ),
                'transaction_date' => $purchaseOrder->po_date,
                'type' => 'OUT',
                'supplier_id' => null,
                'kitchen_id' => $purchaseOrder->kitchen_id,
                'purchase_order_id' => $purchaseOrder->id,
                'notes' => 'OUT dari PO ' . $purchaseOrder->po_number,
                'created_by' => auth()->id() ?? 1,
            ]);

            $stockDetails = $purchaseOrder->details->filter(
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

                if ($item->track_stock) {
                    $stock = Stock::where('item_id', $item->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \RuntimeException(
                            'Stok untuk barang ' . $item->name
                            . ' belum tersedia.'
                        );
                    }

                    if (
                        (float) $stock->quantity
                        < (float) $detail->quantity
                    ) {
                        throw new \RuntimeException(
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
                                (float) $detail->quantity,
                                2,
                                ',',
                                '.'
                            )
                        );
                    }
                }

                $sectionName = trim(
                    (string) ($detail->section_name ?? '')
                );

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

                if ($item->track_stock) {
                    $stock->decrement(
                        'quantity',
                        $detail->quantity
                    );
                }
            }

            $purchaseOrder->update([
                'status' => 'PROCESSED',
                'processed_at' => now(),
                'processed_by' => auth()->id() ?? 1,
            ]);

            /*
             * Invoice mencakup semua detail PO:
             * barang persediaan dan biaya non-stok.
             */
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(
                    $purchaseOrder->po_date
                ),
                'invoice_date' => $purchaseOrder->po_date,
                'stock_transaction_id' => $stockTransaction->id,
                'purchase_order_id' => $purchaseOrder->id,
                'kitchen_id' => $purchaseOrder->kitchen_id,
                'total_amount' => $purchaseOrder->details->sum(
                    'subtotal'
                ),
                'status' => 'DRAFT',
                'notes' => 'Invoice dibuat otomatis dari PO '
                    . $purchaseOrder->po_number,
                'created_by' => auth()->id() ?? 1,
            ]);

            /*
             * Salin seluruh detail PO ke Invoice.
             * Supplier manual biaya non-stok tetap diteruskan.
             */
            foreach ($purchaseOrder->details as $detail) {
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
}