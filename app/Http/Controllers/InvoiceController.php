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
     * Menampilkan daftar Invoice.
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

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Menampilkan detail Invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'kitchen',
            'stockTransaction',
            'purchaseOrder',
            'details.item',
            'details.supplier',
            'expenses',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Membuat Invoice dari transaksi OUT.
     */
    public function createFromOut(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type !== 'OUT') {
            return redirect()
                ->route('stock-transactions.out')
                ->with('error', 'Invoice hanya dapat dibuat dari transaksi OUT.');
        }

        if (!$stockTransaction->purchase_order_id) {
            return redirect()
                ->route('stock-transactions.out')
                ->with('error', 'Transaksi OUT ini tidak memiliki Purchase Order.');
        }

        $existingInvoice = Invoice::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->first();

        if ($existingInvoice) {
            return redirect()
                ->route('invoices.show', $existingInvoice->id)
                ->with('error', 'Invoice untuk transaksi OUT ini sudah dibuat.');
        }

        $stockTransaction->load([
            'kitchen',
            'details.item',
            'details.supplier',
            'purchaseOrder',
        ]);

        if ($stockTransaction->details->isEmpty()) {
            return redirect()
                ->route('stock-transactions.out')
                ->with('error', 'Transaksi OUT tidak memiliki detail barang.');
        }

        try {
            $invoiceId = DB::transaction(function () use ($stockTransaction) {
                $totalAmount = $stockTransaction->details->sum(
                    fn ($detail) =>
                        (float) $detail->quantity *
                        (float) $detail->unit_price
                );

                $invoice = Invoice::create([
                    'invoice_number' => $this->generateInvoiceNumber(
                        $stockTransaction->transaction_date
                    ),
                    'invoice_date' => $stockTransaction->transaction_date,
                    'stock_transaction_id' => $stockTransaction->id,
                    'purchase_order_id' => $stockTransaction->purchase_order_id,
                    'kitchen_id' => $stockTransaction->kitchen_id,
                    'total_amount' => $totalAmount,
                    'status' => 'DRAFT',
                    'notes' => 'Invoice dibuat otomatis dari OUT ' .
                        $stockTransaction->transaction_number,
                    'created_by' => auth()->id() ?? 1,
                ]);

                foreach ($stockTransaction->details as $outDetail) {
                    $invoice->details()->create([
                        'supplier_id' => $outDetail->supplier_id,
                        'item_id' => $outDetail->item_id,
                        'quantity' => $outDetail->quantity,
                        'unit' => $outDetail->unit,
                        'unit_price' => $outDetail->unit_price,
                        'subtotal' => (float) $outDetail->quantity
                            * (float) $outDetail->unit_price,
                        'notes' => null,
                    ]);
                }

                if ($invoice->details()->count() === 0) {
                    throw new Exception('Detail Invoice gagal dibuat.');
                }

                return $invoice->id;
            });

            return redirect()
                ->route('invoices.show', $invoiceId)
                ->with('success', 'Invoice berhasil dibuat otomatis dari OUT.');

        } catch (Exception $e) {
            report($e);

            return redirect()
                ->route('stock-transactions.out')
                ->with('error', 'Gagal membuat Invoice. Periksa log aplikasi.');
        }
    }

    /**
     * Memperbarui detail Invoice berdasarkan transaksi OUT.
     *
     * Method ini dapat dipanggil setelah transaksi OUT diedit.
     */
    public function syncFromOut(StockTransaction $stockTransaction): void
    {
        if ($stockTransaction->type !== 'OUT') {
            return;
        }

        $invoice = Invoice::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->first();

        if (!$invoice) {
            // Belum ada invoice, jadi tidak ada yang perlu disinkronkan.
            return;
        }

        DB::transaction(function () use ($invoice, $stockTransaction) {
            $stockTransaction->load([
                'details.item',
                'details.supplier',
            ]);

            // Ganti detail invoice berdasarkan kondisi OUT terbaru.
            $invoice->details()->delete();

            $totalAmount = 0;

            foreach ($stockTransaction->details as $outDetail) {
                $subtotal =
                    (float) $outDetail->quantity *
                    (float) $outDetail->unit_price;

                $invoice->details()->create([
                    'supplier_id' => $outDetail->supplier_id,
                    'item_id' => $outDetail->item_id,
                    'quantity' => $outDetail->quantity,
                    'unit' => $outDetail->unit,
                    'unit_price' => $outDetail->unit_price,
                    'subtotal' => $subtotal,
                    'notes' => null,
                ]);

                $totalAmount += $subtotal;
            }

            $invoice->update([
                'invoice_date' => $stockTransaction->transaction_date,
                'purchase_order_id' => $stockTransaction->purchase_order_id,
                'kitchen_id' => $stockTransaction->kitchen_id,
                'total_amount' => $totalAmount,
            ]);
        });
    }

    /**
     * Generate nomor Invoice.
     */
    private function generateInvoiceNumber($invoiceDate): string
    {
        $date = date('Ymd', strtotime($invoiceDate));

        $lastInvoice = Invoice::whereDate('invoice_date', $invoiceDate)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $number = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_number, -3)) + 1
            : 1;

        return 'INV-' . $date . '-' . str_pad(
            (string) $number,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}