<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Kitchen;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransactionDetail;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Daftar Purchase Order
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with([
            'kitchen',
            'details.supplier',
            'details.item',
        ])
            ->latest()
            ->get();

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    /*
    |--------------------------------------------------------------------------
    | Form Tambah Purchase Order
    |--------------------------------------------------------------------------
    */

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

        return view('purchase_orders.create', compact('kitchens', 'items'));
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Purchase Order
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'po_date' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.supplier_id' => 'required|exists:suppliers,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',

            // Bagian bersifat opsional
            'items.*.section_name' => 'nullable|string|max:100',
            'items.*.section_order' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($data) {
            $purchaseOrder = PurchaseOrder::create([
                'kitchen_id' => $data['kitchen_id'],
                'po_number' => $this->generatePoNumber($data['po_date']),
                'po_date' => $data['po_date'],
                'status' => 'RECEIVED',
                'approved_at' => null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($data['items'] as $detail) {
                $item = Item::findOrFail($detail['item_id']);

                $quantity = (float) $detail['quantity'];
                $unitPrice = (float) $detail['unit_price'];

                $sectionName = trim(
                    (string) ($detail['section_name'] ?? '')
                );

                // Jika tidak memilih bagian, simpan NULL.
                if ($sectionName === '') {
                    $sectionName = null;
                    $sectionOrder = null;
                } else {
                    $sectionOrder = isset($detail['section_order'])
                        ? (int) $detail['section_order']
                        : null;
                }

                $purchaseOrder->details()->create([
                    'supplier_id' => $detail['supplier_id'],
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $quantity * $unitPrice,
                    'notes' => $detail['notes'] ?? null,
                    'section_name' => $sectionName,
                    'section_order' => $sectionOrder,
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Proses PO menjadi OUT dan Invoice
    |--------------------------------------------------------------------------
    */

    public function process(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'PROCESSED') {
            return redirect()
                ->route('invoices.index')
                ->with('error', 'Purchase Order ini sudah diproses menjadi OUT.');
        }

        $purchaseOrder->load([
            'details.item',
            'details.supplier',
        ]);

        if ($purchaseOrder->details->isEmpty()) {
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Purchase Order tidak memiliki detail barang.');
        }

        $invoiceId = null;

        DB::transaction(function () use ($purchaseOrder, &$invoiceId) {
            /*
            |--------------------------------------------------------------------------
            | 1. Buat transaksi OUT
            |--------------------------------------------------------------------------
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

            /*
            |--------------------------------------------------------------------------
            | 2. Kurangi stok dan salin detail PO ke OUT
            |--------------------------------------------------------------------------
            */

            foreach ($purchaseOrder->details as $detail) {
                $item = $detail->item;

                if (!$item) {
                    throw new \RuntimeException(
                        'Barang pada PO tidak ditemukan.'
                    );
                }

                $stock = Stock::where('item_id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new \RuntimeException(
                        'Stok untuk barang ' . $item->name . ' belum tersedia.'
                    );
                }

                if ((float) $stock->quantity < (float) $detail->quantity) {
                    throw new \RuntimeException(
                        'Stok ' . $item->name .
                        ' tidak mencukupi. Stok tersedia: ' .
                        $stock->quantity .
                        ', kebutuhan: ' . $detail->quantity
                    );
                }

                $sectionName = trim(
                    (string) ($detail->section_name ?? '')
                );

                $stockTransaction->details()->create([
                    'item_id' => $detail->item_id,
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
                ]);

                $stock->decrement('quantity', $detail->quantity);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Tandai PO sudah diproses
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([
                'status' => 'PROCESSED',
                'processed_at' => now(),
                'processed_by' => auth()->id() ?? 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Buat Invoice dari PO/OUT
            |--------------------------------------------------------------------------
            */

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(
                    $purchaseOrder->po_date
                ),
                'invoice_date' => $purchaseOrder->po_date,
                'stock_transaction_id' => $stockTransaction->id,
                'purchase_order_id' => $purchaseOrder->id,
                'kitchen_id' => $purchaseOrder->kitchen_id,

                // Hitung dari detail PO agar total tidak bergantung
                // pada relasi OUT yang belum tentu sudah dimuat.
                'total_amount' => $purchaseOrder->details->sum('subtotal'),

                'status' => 'DRAFT',
                'notes' => 'Invoice dibuat otomatis dari PO ' .
                    $purchaseOrder->po_number,
                'created_by' => auth()->id() ?? 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 5. Salin supplier dan bagian ke detail Invoice
            |--------------------------------------------------------------------------
            |
            | Gunakan setiap detail PO secara langsung. Dengan begitu,
            | jika barang yang sama muncul lebih dari sekali, supplier
            | dan bagian setiap baris tetap sesuai dengan baris PO-nya.
            */

            foreach ($purchaseOrder->details as $detail) {
                $sectionName = trim(
                    (string) ($detail->section_name ?? '')
                );

                $invoice->details()->create([
                    'supplier_id' => $detail->supplier_id,
                    'item_id' => $detail->item_id,
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
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PESANAN MBG
    |--------------------------------------------------------------------------
    */

    public function daftarPesanan(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'kitchen',
            'details.supplier',
            'details.item.category',
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
                return sprintf(
                    '%02d-%06d-%06d',
                    $supplierOrder($detail->supplier?->name),
                    (int) ($detail->section_order ?? 0),
                    (int) ($detail->supplier_id ?? 0)
                );
            })
            ->groupBy(function ($detail) {
                return $detail->supplier_id ?? 'tanpa-supplier';
            });

        return view(
            'purchase_orders.daftar-pesanan',
            compact('purchaseOrder', 'supplierGroups')
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Generate Nomor PO
    |--------------------------------------------------------------------------
    */

    private function generatePoNumber($poDate)
    {
        $date = date('Ymd', strtotime($poDate));

        $lastPo = PurchaseOrder::whereDate('po_date', $poDate)
            ->latest('id')
            ->first();

        $number = $lastPo
            ? ((int) substr($lastPo->po_number, -3)) + 1
            : 1;

        return 'PO-' . $date . '-' . str_pad(
            $number,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Nomor OUT
    |--------------------------------------------------------------------------
    */

    private function generateOutNumber($transactionDate)
    {
        $date = date('Ymd', strtotime($transactionDate));

        $lastTransaction = StockTransaction::where('type', 'OUT')
            ->whereDate('transaction_date', $transactionDate)
            ->latest('id')
            ->first();

        $number = $lastTransaction
            ? ((int) substr($lastTransaction->transaction_number, -3)) + 1
            : 1;

        return 'OUT-' . $date . '-' . str_pad(
            $number,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Nomor Invoice
    |--------------------------------------------------------------------------
    */

    private function generateInvoiceNumber($invoiceDate)
    {
        $date = date('Ymd', strtotime($invoiceDate));

        $lastInvoice = Invoice::whereDate('invoice_date', $invoiceDate)
            ->latest('id')
            ->first();

        $number = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_number, -3)) + 1
            : 1;

        return 'INV-' . $date . '-' . str_pad(
            $number,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    public function deliveryNote(\App\Models\PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'kitchen',
            'details.supplier',
            'details.item',
        ]);

        return view('purchase_orders.delivery-note', compact('purchaseOrder'));
    }
}