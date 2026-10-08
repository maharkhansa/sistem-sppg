<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

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
     * Menampilkan detail Invoice
     */
    public function show(Invoice $invoice)
    {
        /*
         * Load seluruh relasi yang diperlukan
         * untuk tampilan Invoice.
         */
        $invoice->load([
            'kitchen',
            'stockTransaction',
            'purchaseOrder',
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
        /*
         * Pastikan transaksi adalah OUT
         */
        if ($stockTransaction->type !== 'OUT') {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Invoice hanya dapat dibuat dari transaksi OUT.'
                );
        }

        /*
         * Pastikan OUT mempunyai PO
         */
        if (!$stockTransaction->purchase_order_id) {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi OUT ini tidak memiliki Purchase Order.'
                );
        }

        /*
         * Cek apakah Invoice sudah dibuat
         */
        $existingInvoice = Invoice::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->first();

        if ($existingInvoice) {

            return redirect()
                ->route(
                    'invoices.show',
                    $existingInvoice->id
                )
                ->with(
                    'error',
                    'Invoice untuk transaksi OUT ini sudah dibuat.'
                );
        }

        /*
         * Load seluruh data yang dibutuhkan
         */
        $stockTransaction->load([
            'kitchen',
            'details.item',
            'purchaseOrder.details.item',
            'purchaseOrder.details.supplier',
        ]);

        /*
         * Pastikan OUT mempunyai detail
         */
        if ($stockTransaction->details->isEmpty()) {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi OUT tidak memiliki detail barang.'
                );
        }

        try {

            $invoiceId = DB::transaction(
                function () use ($stockTransaction) {

                    /*
                     * Hitung total berdasarkan detail OUT
                     */
                    $totalAmount =
                        $stockTransaction
                            ->details
                            ->sum('subtotal');

                    /*
                     * Buat Invoice
                     */
                    $invoice = Invoice::create([
                        'invoice_number' =>
                            $this->generateInvoiceNumber(
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
                            $totalAmount,

                        'status' =>
                            'DRAFT',

                        'notes' =>
                            'Invoice dibuat otomatis dari OUT ' .
                            $stockTransaction->transaction_number,

                        'created_by' =>
                            auth()->id() ?? 1,
                    ]);

                    /*
                     * Ambil detail PO
                     */
                    $poDetails =
                        $stockTransaction
                            ->purchaseOrder
                            ->details;

                    /*
                     * Salin setiap detail OUT
                     * ke Invoice Detail
                     */
                    foreach (
                        $stockTransaction->details
                        as $outDetail
                    ) {

                        /*
                         * Cari supplier berdasarkan
                         * item yang sama pada PO.
                         */
                        $poDetail = $poDetails
                            ->firstWhere(
                                'item_id',
                                $outDetail->item_id
                            );

                        /*
                         * Buat detail Invoice
                         */
                        $invoice->details()->create([

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

                    /*
                     * Pastikan detail Invoice
                     * benar-benar berhasil dibuat.
                     */
                    if ($invoice->details()->count() === 0) {

                        throw new Exception(
                            'Detail Invoice gagal dibuat.'
                        );
                    }

                    return $invoice->id;
                }
            );

            return redirect()
                ->route(
                    'invoices.show',
                    $invoiceId
                )
                ->with(
                    'success',
                    'Invoice berhasil dibuat otomatis dari OUT.'
                );

        } catch (Exception $e) {

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Gagal membuat Invoice: ' .
                    $e->getMessage()
                );
        }
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