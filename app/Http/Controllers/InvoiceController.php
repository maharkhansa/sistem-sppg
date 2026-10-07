<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Menampilkan daftar Invoice
     */
    public function index()
    {
        $invoices = Invoice::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'details.item',
            'details.supplier',
        ])
            ->latest()
            ->get();

        return view(
            'invoices.index',
            compact('invoices')
        );
    }

    /**
     * Menampilkan satu Invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'details.item',
            'details.supplier',
        ]);

        return view(
            'invoices.show',
            compact('invoice')
        );
    }

    /**
     * Membuat Invoice dari transaksi OUT
     */
    public function createFromOut(
        StockTransaction $stockTransaction
    ) {
        // Pastikan transaksi adalah OUT
        if ($stockTransaction->type !== 'OUT') {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Invoice hanya dapat dibuat dari transaksi OUT.'
                );
        }

        // Pastikan OUT mempunyai PO
        if (!$stockTransaction->purchase_order_id) {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi OUT ini tidak memiliki Purchase Order.'
                );
        }

        // Cek apakah Invoice sudah pernah dibuat
        $existingInvoice = Invoice::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->first();

        if ($existingInvoice) {
            return redirect()
                ->route('invoices.index')
                ->with(
                    'error',
                    'Invoice untuk transaksi OUT ini sudah dibuat.'
                );
        }

        // Ambil data OUT beserta detail PO dan supplier
        $stockTransaction->load([
            'purchaseOrder.details.supplier',
            'purchaseOrder.details.item',
            'kitchen',
            'details.item',
        ]);

        // Pastikan ada detail barang
        if ($stockTransaction->details->isEmpty()) {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi OUT tidak memiliki detail barang.'
                );
        }

        DB::transaction(function () use (
            $stockTransaction
        ) {

            // Buat Invoice
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(
                    $stockTransaction->transaction_date
                ),

                'invoice_date' =>
                    $stockTransaction->transaction_date,

                'stock_transaction_id' =>
                    $stockTransaction->id,

                'purchase_order_id' =>
                    $stockTransaction->purchase_order_id,

                'kitchen_id' =>
                    $stockTransaction->kitchen_id,

                'total_amount' =>
                    $stockTransaction->details->sum('subtotal'),

                'status' => 'DRAFT',

                'notes' =>
                    'Invoice dibuat otomatis dari OUT ' .
                    $stockTransaction->transaction_number,

                'created_by' =>
                    auth()->id() ?? 1,
            ]);

            /*
             * Salin detail OUT ke Invoice.
             *
             * Supplier diambil dari detail PO
             * berdasarkan item yang sama.
             */
            foreach (
                $stockTransaction->details
                as $detail
            ) {

                $poDetail = $stockTransaction
                    ->purchaseOrder
                    ->details
                    ->firstWhere(
                        'item_id',
                        $detail->item_id
                    );

                $invoice->details()->create([

                    'supplier_id' =>
                        $poDetail?->supplier_id,

                    'item_id' =>
                        $detail->item_id,

                    'quantity' =>
                        $detail->quantity,

                    'unit' =>
                        $detail->unit,

                    'unit_price' =>
                        $detail->unit_price,

                    'subtotal' =>
                        $detail->subtotal,

                    'notes' => null,
                ]);
            }
        });

        return redirect()
            ->route('invoices.index')
            ->with(
                'success',
                'Invoice berhasil dibuat otomatis dari OUT.'
            );
    }

    /**
     * Generate nomor Invoice
     */
    private function generateInvoiceNumber(
        $invoiceDate
    ) {
        $date = date(
            'Ymd',
            strtotime($invoiceDate)
        );

        $lastInvoice = Invoice::whereDate(
            'invoice_date',
            $invoiceDate
        )
            ->latest('id')
            ->first();

        $number = $lastInvoice
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