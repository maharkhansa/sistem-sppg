<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Kitchen;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransactionDetail;
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

        return view(
            'purchase_orders.index',
            compact('purchaseOrders')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Tambah Purchase Order
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        /*
        |--------------------------------------------------------------
        | Ambil SPPG aktif
        |--------------------------------------------------------------
        */

        $kitchens = Kitchen::where('status', true)
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------
        | Ambil barang aktif beserta supplier
        |--------------------------------------------------------------
        */

        $items = Item::with([
            'category',
            'supplier',
        ])
            ->where('status', true)
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------
        | Ambil harga pembelian terakhir dari Barang Masuk (IN)
        |--------------------------------------------------------------
        */

        foreach ($items as $item) {

            $lastIn = StockTransactionDetail::where(
                'item_id',
                $item->id
            )
                ->whereHas(
                    'stockTransaction',
                    function ($query) {

                        $query->where(
                            'type',
                            'IN'
                        );

                    }
                )
                ->latest('id')
                ->first();


            /*
            | Harga terakhir.
            | Jika belum pernah ada transaksi IN,
            | harga otomatis menjadi 0.
            */

            $item->last_purchase_price =
                $lastIn?->unit_price ?? 0;
        }


        return view(
            'purchase_orders.create',
            compact(
                'kitchens',
                'items'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Purchase Order
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([

            'po_date' =>
                'required|date',

            'kitchen_id' =>
                'required|exists:kitchens,id',

            'notes' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.supplier_id' =>
                'required|exists:suppliers,id',

            'items.*.item_id' =>
                'required|exists:items,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.unit_price' =>
                'required|numeric|min:0',

            'items.*.notes' =>
                'nullable|string',

        ]);


        DB::transaction(function () use ($data) {

            /*
            |----------------------------------------------------------
            | Buat Header PO
            |----------------------------------------------------------
            */

            $purchaseOrder =
                PurchaseOrder::create([

                    'kitchen_id' =>
                        $data['kitchen_id'],

                    'po_number' =>
                        $this->generatePoNumber(
                            $data['po_date']
                        ),

                    'po_date' =>
                        $data['po_date'],

                    'status' =>
                        'RECEIVED',

                    'approved_at' =>
                        null,

                    'notes' =>
                        $data['notes'] ?? null,

                    'created_by' =>
                        auth()->id() ?? 1,

                ]);


            /*
            |----------------------------------------------------------
            | Simpan Detail PO
            |----------------------------------------------------------
            */

            foreach (
                $data['items']
                as $detail
            ) {

                $item =
                    Item::findOrFail(
                        $detail['item_id']
                    );


                $quantity =
                    $detail['quantity'];


                $unitPrice =
                    $detail['unit_price'];


                $purchaseOrder
                    ->details()
                    ->create([

                        'supplier_id' =>
                            $detail['supplier_id'],

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

                        'notes' =>
                            $detail['notes'] ?? null,

                    ]);
            }
        });


        return redirect()
            ->route('purchase-orders.index')
            ->with(
                'success',
                'Purchase Order berhasil disimpan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Proses PO menjadi OUT
    |--------------------------------------------------------------------------
    */

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
    ]);

    if ($purchaseOrder->details->isEmpty()) {
        return redirect()
            ->route('purchase-orders.index')
            ->with(
                'error',
                'Purchase Order tidak memiliki detail barang.'
            );
    }

    $invoiceId = null;

    DB::transaction(function () use (
        $purchaseOrder,
        &$invoiceId
    ) {
        /*
        |--------------------------------------------------------------------------
        | 1. BUAT TRANSAKSI OUT
        |--------------------------------------------------------------------------
        */

        $transactionNumber =
            $this->generateOutNumber(
                $purchaseOrder->po_date
            );

        $stockTransaction =
            StockTransaction::create([
                'transaction_number' =>
                    $transactionNumber,

                'transaction_date' =>
                    $purchaseOrder->po_date,

                'type' =>
                    'OUT',

                'supplier_id' =>
                    null,

                'kitchen_id' =>
                    $purchaseOrder->kitchen_id,

                'purchase_order_id' =>
                    $purchaseOrder->id,

                'notes' =>
                    'OUT dari PO ' .
                    $purchaseOrder->po_number,

                'created_by' =>
                    auth()->id() ?? 1,
            ]);


        /*
        |--------------------------------------------------------------------------
        | 2. KURANGI STOK DAN SIMPAN DETAIL OUT
        |--------------------------------------------------------------------------
        */

        foreach (
            $purchaseOrder->details
            as $detail
        ) {
            $item = $detail->item;

            $stock =
                Stock::where(
                    'item_id',
                    $item->id
                )
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new \Exception(
                    'Stok untuk barang ' .
                    $item->name .
                    ' belum tersedia.'
                );
            }

            if (
                $stock->quantity <
                $detail->quantity
            ) {
                throw new \Exception(
                    'Stok ' .
                    $item->name .
                    ' tidak mencukupi. ' .
                    'Stok tersedia: ' .
                    $stock->quantity .
                    ', kebutuhan: ' .
                    $detail->quantity
                );
            }

            $stockTransaction
                ->details()
                ->create([
                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        $detail->quantity,

                    'unit' =>
                        $detail->unit,

                    'unit_price' =>
                        $detail->unit_price,

                    'subtotal' =>
                        $detail->subtotal,
                ]);

            $stock->decrement(
                'quantity',
                $detail->quantity
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 3. UBAH STATUS PO MENJADI PROCESSED
        |--------------------------------------------------------------------------
        */

        $purchaseOrder->update([
            'status' =>
                'PROCESSED',

            'processed_at' =>
                now(),

            'processed_by' =>
                auth()->id() ?? 1,
        ]);


        /*
        |--------------------------------------------------------------------------
        | 4. BUAT INVOICE OTOMATIS DARI OUT
        |--------------------------------------------------------------------------
        */

        $invoice =
            \App\Models\Invoice::create([
                'invoice_number' =>
                    $this->generateInvoiceNumber(
                        $purchaseOrder->po_date
                    ),

                'invoice_date' =>
                    $purchaseOrder->po_date,

                'stock_transaction_id' =>
                    $stockTransaction->id,

                'purchase_order_id' =>
                    $purchaseOrder->id,

                'kitchen_id' =>
                    $purchaseOrder->kitchen_id,

                'total_amount' =>
                    $stockTransaction
                        ->details
                        ->sum('subtotal'),

                'status' =>
                    'DRAFT',

                'notes' =>
                    'Invoice dibuat otomatis dari PO ' .
                    $purchaseOrder->po_number,

                'created_by' =>
                    auth()->id() ?? 1,
            ]);


        /*
        |--------------------------------------------------------------------------
        | 5. SALIN DETAIL PO KE DETAIL INVOICE
        |--------------------------------------------------------------------------
        */

        foreach (
            $stockTransaction->details
            as $outDetail
        ) {
            $poDetail =
                $purchaseOrder
                    ->details
                    ->firstWhere(
                        'item_id',
                        $outDetail->item_id
                    );

            $invoice
                ->details()
                ->create([
                    'supplier_id' =>
                        $poDetail?->supplier_id,

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

        $invoiceId = $invoice->id;
    });


    /*
    |--------------------------------------------------------------------------
    | 6. LANGSUNG KE HALAMAN INVOICE
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'invoices.show',
            $invoiceId
        )
        ->with(
            'success',
            'PO berhasil diproses menjadi OUT dan Invoice berhasil dibuat.'
        );
}


    /*
    |--------------------------------------------------------------------------
    | Generate Nomor PO
    |--------------------------------------------------------------------------
    */

    private function generatePoNumber(
        $poDate
    ) {

        $date =
            date(
                'Ymd',
                strtotime($poDate)
            );


        $lastPo =
            PurchaseOrder::whereDate(
                'po_date',
                $poDate
            )
                ->latest('id')
                ->first();


        $number =
            $lastPo

                ? (
                    (int) substr(
                        $lastPo->po_number,
                        -3
                    )
                ) + 1

                : 1;


        return 'PO-' .
            $date .
            '-' .
            str_pad(
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

    private function generateOutNumber(
        $transactionDate
    ) {

        $date =
            date(
                'Ymd',
                strtotime($transactionDate)
            );


        $lastTransaction =
            StockTransaction::where(
                'type',
                'OUT'
            )
                ->whereDate(
                    'transaction_date',
                    $transactionDate
                )
                ->latest('id')
                ->first();


        $number =
            $lastTransaction

                ? (
                    (int) substr(
                        $lastTransaction
                            ->transaction_number,
                        -3
                    )
                ) + 1

                : 1;


        return 'OUT-' .
            $date .
            '-' .
            str_pad(
                $number,
                3,
                '0',
                STR_PAD_LEFT
            );
    }

    private function generateInvoiceNumber($invoiceDate)
{
    $date = date(
        'Ymd',
        strtotime($invoiceDate)
    );

    $lastInvoice =
        \App\Models\Invoice::whereDate(
            'invoice_date',
            $invoiceDate
        )
        ->latest('id')
        ->first();

    $number =
        $lastInvoice
            ? (
                (int) substr(
                    $lastInvoice->invoice_number,
                    -3
                )
            ) + 1
            : 1;

    return 'INV-' .
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