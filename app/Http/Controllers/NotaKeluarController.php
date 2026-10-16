<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceNotaAllocation;
use App\Models\NotaKeluar;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Kitchen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotaKeluarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR NOTA
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $notaKeluars = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
            'invoiceNotaAllocations.invoiceDetail.expenseType',
        ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('nota_keluars.index', compact('notaKeluars'));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL NOTA
    |--------------------------------------------------------------------------
    */

    public function show(NotaKeluar $notaKeluar)
    {
        $notaId = $notaKeluar->id;

        $invoice = $this->findInvoiceForNota($notaKeluar);

        /*
         * Method ini tidak menghapus atau membuat ulang Nota.
         * Detail akan dibaca berdasarkan alokasi yang tersimpan.
         */
        if ($invoice && $notaKeluar->stockTransaction) {
            $this->syncNotasFromInvoice(
                $invoice,
                $notaKeluar->stockTransaction
            );
        }

        $notaKeluar = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
            'invoiceNotaAllocations.invoiceDetail.expenseType',
        ])->find($notaId);

        if (!$notaKeluar) {
            return redirect()
                ->route('nota-keluars.index')
                ->with(
                    'error',
                    'Nota tidak ditemukan.'
                );
        }

        if ($notaKeluar->stockTransaction?->kitchen_id) {
            $notaKeluar->setRelation(
                'kitchen',
                Kitchen::find($notaKeluar->stockTransaction->kitchen_id)
            );
        }

        $invoice = $this->findInvoiceForNota($notaKeluar);
        $invoiceDetails = $this->getInvoiceDetailsForNota($notaKeluar);

        $notaTotalAmount = $invoiceDetails->sum(
            fn ($detail) => (float) ($detail->subtotal ?? 0)
        );

        /*
         * Total hanya disinkronkan jika Nota memiliki alokasi Invoice.
         * Nota lama tanpa alokasi mempertahankan nominal historisnya.
         */
        $hasInvoiceAllocation = InvoiceNotaAllocation::where(
            'nota_keluar_id',
            $notaKeluar->id
        )->exists();

        if ($hasInvoiceAllocation) {
            $notaKeluar->update([
                'total_amount' => $notaTotalAmount,
            ]);
        } else {
            $notaTotalAmount = (float) $notaKeluar->total_amount;
        }

        $barcodeRaw = $notaKeluar->barcode_number;

        if (empty($barcodeRaw)) {
            $barcodeRaw = $this->generateBarcodeNumber(
                $notaKeluar->supplier,
                $notaKeluar->nota_date,
                $notaKeluar->id
            );

            $notaKeluar->update([
                'barcode_number' => $barcodeRaw,
            ]);
        }

        if (empty($notaKeluar->customer_order_number)) {
            $notaKeluar->update([
                'customer_order_number' =>
                    $this->generateCustomerOrderNumber(
                        $notaKeluar->kitchen_id,
                        $notaKeluar->stock_transaction_id
                    ),
            ]);

            $notaKeluar->refresh();
        }

        $deliveryDate = $invoice?->invoice_date
            ?? $notaKeluar->nota_date
            ?? $notaKeluar->created_at;

        return view('nota_keluars.show', compact(
            'notaKeluar',
            'invoice',
            'invoiceDetails',
            'notaTotalAmount',
            'barcodeRaw',
            'deliveryDate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK NOTA
    |--------------------------------------------------------------------------
    */

    public function print(NotaKeluar $notaKeluar)
    {
        $notaId = $notaKeluar->id;

        $invoice = $this->findInvoiceForNota($notaKeluar);

        if ($invoice && $notaKeluar->stockTransaction) {
            $this->syncNotasFromInvoice(
                $invoice,
                $notaKeluar->stockTransaction
            );
        }

        $notaKeluar = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
            'invoiceNotaAllocations.invoiceDetail.expenseType',
        ])->find($notaId);

        if (!$notaKeluar) {
            return redirect()
                ->route('nota-keluars.index')
                ->with(
                    'error',
                    'Nota tidak ditemukan.'
                );
        }

        if ($notaKeluar->stockTransaction?->kitchen_id) {
            $notaKeluar->setRelation(
                'kitchen',
                Kitchen::find($notaKeluar->stockTransaction->kitchen_id)
            );
        }

        $invoice = $this->findInvoiceForNota($notaKeluar);
        $details = $this->getInvoiceDetailsForNota($notaKeluar);

        $total = $details->sum(
            fn ($detail) => (float) ($detail->subtotal ?? 0)
        );

        $hasInvoiceAllocation = InvoiceNotaAllocation::where(
            'nota_keluar_id',
            $notaKeluar->id
        )->exists();

        if ($hasInvoiceAllocation) {
            $notaKeluar->update([
                'total_amount' => $total,
            ]);
        } else {
            $total = (float) $notaKeluar->total_amount;
        }

        $barcodeRaw = $notaKeluar->barcode_number;

        if (empty($barcodeRaw)) {
            $barcodeRaw = $this->generateBarcodeNumber(
                $notaKeluar->supplier,
                $notaKeluar->nota_date,
                $notaKeluar->id
            );

            $notaKeluar->update([
                'barcode_number' => $barcodeRaw,
            ]);
        }

        $deliveryDate = $invoice?->invoice_date
            ?? $notaKeluar->nota_date
            ?? $notaKeluar->created_at;

        return view('nota_keluars.print', compact(
            'notaKeluar',
            'details',
            'total',
            'barcodeRaw',
            'deliveryDate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBUAT / MEMBUKA NOTA DARI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function createFromOut(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type !== 'OUT') {
            return back()->with(
                'error',
                'Transaksi bukan merupakan Barang Keluar.'
            );
        }

        $stockTransaction->load([
            'details.item',
            'purchaseOrder',
            'kitchen',
        ]);

        if (!$stockTransaction->purchaseOrder) {
            return back()->with(
                'error',
                'Barang Keluar belum memiliki Purchase Order.'
            );
        }

        if ($stockTransaction->details->isEmpty()) {
            return back()->with(
                'error',
                'Barang Keluar belum memiliki detail barang.'
            );
        }

        $invoice = Invoice::with([
            'details.item',
            'details.supplier',
            'details.expenseType',
        ])
            ->where('stock_transaction_id', $stockTransaction->id)
            ->first();

        if (!$invoice) {
            return back()->with(
                'error',
                'Invoice untuk Barang Keluar ini belum tersedia. '
                . 'Buat Invoice terlebih dahulu.'
            );
        }

        /*
         * Tidak membangun ulang Nota secara otomatis.
         * Pembagian Nota harus dilakukan melalui halaman alokasi Invoice.
         */
        $this->syncNotasFromInvoice($invoice, $stockTransaction);

        $firstNota = NotaKeluar::where(
            'stock_transaction_id',
            $stockTransaction->id
        )
            ->orderBy('supplier_id')
            ->orderBy('section_order')
            ->orderBy('id')
            ->first();

        if (!$firstNota) {
            return back()->with(
                'error',
                'Nota belum tersedia. Buka halaman pembagian Nota pada Invoice, '
                . 'kemudian simpan alokasinya terlebih dahulu.'
            );
        }

        return redirect()
            ->route('nota-keluars.show', $firstNota)
            ->with(
                'success',
                'Nota dibuka. Detail mengikuti alokasi Invoice yang tersimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SINKRONISASI NOTA DARI INVOICE
    |--------------------------------------------------------------------------
    |
    | Sinkronisasi otomatis tidak membuat, menghapus, atau
    | mengalokasikan ulang Nota.
    |
    | Alokasi Nota dikelola melalui InvoiceController.
    | Method ini dipertahankan agar pemanggilan dari method lama
    | tetap berjalan tanpa mengubah data Nota historis.
    |--------------------------------------------------------------------------
    */

    private function syncNotasFromInvoice(
        Invoice $invoice,
        StockTransaction $stockTransaction
    ): void {
        /*
         * Sengaja tidak ada perubahan database di sini.
         *
         * Hindari menghubungkan detail Invoice ke Nota hanya
         * berdasarkan supplier atau section_name. Pencocokan
         * seperti itu dapat mengubah Nota historis yang detailnya
         * berbeda dari Invoice saat ini.
         */
    }

    /*
    |--------------------------------------------------------------------------
    | MENENTUKAN JENIS DETAIL INVOICE
    |--------------------------------------------------------------------------
    */

    private function getNotaTypeForDetail($detail): string
    {
        $detailType = strtolower(
            trim((string) ($detail->detail_type ?? ''))
        );

        if (
            str_contains($detailType, 'expense')
            || str_contains($detailType, 'biaya')
            || str_contains($detailType, 'non_stock')
            || str_contains($detailType, 'non-stock')
            || str_contains($detailType, 'operasional')
        ) {
            return 'expense';
        }

        if (!empty($detail->expense_type_id)) {
            return 'expense';
        }

        if (empty($detail->item_id) && !empty($detail->expense_date)) {
            return 'expense';
        }

        return 'stock';
    }

    /*
    |--------------------------------------------------------------------------
    | MENCARI INVOICE UNTUK NOTA
    |--------------------------------------------------------------------------
    */

    private function findInvoiceForNota(
        NotaKeluar $notaKeluar
    ): ?Invoice {
        return Invoice::with([
            'details.item',
            'details.supplier',
            'details.expenseType',
            'kitchen',
        ])
            ->where(
                'stock_transaction_id',
                $notaKeluar->stock_transaction_id
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL DETAIL NOTA BERDASARKAN ALOKASI INVOICE
    |--------------------------------------------------------------------------
    */

    private function getInvoiceDetailsForNota(NotaKeluar $notaKeluar)
    {
        /*
         * Sumber utama: alokasi Invoice yang tersimpan untuk Nota ini.
         */
        $allocations = InvoiceNotaAllocation::with([
            'invoiceDetail.item',
            'invoiceDetail.supplier',
            'invoiceDetail.expenseType',
        ])
            ->where('nota_keluar_id', $notaKeluar->id)
            ->get();

        if ($allocations->isNotEmpty()) {
            return $allocations
                ->map(function ($allocation) {
                    $detail = $allocation->invoiceDetail;

                    if (!$detail) {
                        return null;
                    }

                    $copy = clone $detail;

                    $invoiceQuantity = (float) ($detail->quantity ?? 0);
                    $allocatedQuantity = (float) ($allocation->quantity ?? 0);
                    $invoiceSubtotal = (float) (
                        $detail->subtotal
                        ?? (
                            $invoiceQuantity
                            * (float) $detail->unit_price
                        )
                    );

                    $copy->quantity = $allocatedQuantity;

                    /*
                     * Jika seluruh kuantitas dialokasikan, gunakan
                     * subtotal Invoice agar nilai tetap sama.
                     *
                     * Jika alokasi hanya sebagian kuantitas, subtotal
                     * dihitung secara proporsional terhadap kuantitas.
                     */
                    if (
                        $invoiceQuantity > 0
                        && abs($allocatedQuantity - $invoiceQuantity) < 0.00001
                    ) {
                        $copy->subtotal = $invoiceSubtotal;
                    } elseif ($invoiceQuantity > 0) {
                        $copy->subtotal = round(
                            ($allocatedQuantity / $invoiceQuantity)
                            * $invoiceSubtotal,
                            2
                        );
                    } else {
                        $copy->subtotal = round(
                            $allocatedQuantity
                            * (float) $detail->unit_price,
                            2
                        );
                    }

                    return $copy;
                })
                ->filter()
                ->values();
        }

        /*
         * Kompatibilitas untuk Nota historis tanpa alokasi.
         *
         * Jangan mencari detail Invoice berdasarkan supplier dan
         * section_name karena hasilnya belum tentu sama dengan isi
         * Nota lama.
         */
        return $notaKeluar->details()
            ->with([
                'item',
                'supplier',
            ])
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE INFORMASI NOTA
    |--------------------------------------------------------------------------
    */

    public function updateInformasi(
        Request $request,
        NotaKeluar $notaKeluar
    ) {
        $validated = $request->validate([
            'delivery_address' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'delivery_address.required' =>
                'Alamat pengiriman wajib diisi.',
            'delivery_address.max' =>
                'Alamat pengiriman maksimal 2000 karakter.',
        ]);

        $notaKeluar->update([
            'delivery_address' => $validated['delivery_address'],
        ]);

        return back()->with(
            'success',
            'Alamat pengiriman nota berhasil diperbarui.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR PESANAN PELANGGAN
    |--------------------------------------------------------------------------
    */

    private function generateCustomerOrderNumber(
        $kitchenId,
        $currentStockTransactionId = null
    ) {
        $kitchen = Kitchen::find($kitchenId);
        $kitchenName = $kitchen?->name ?? 'SPPG';

        $query = NotaKeluar::where(
            'kitchen_id',
            $kitchenId
        )->whereNotNull('customer_order_number');

        if ($currentStockTransactionId) {
            $query->where(
                'stock_transaction_id',
                '!=',
                $currentStockTransactionId
            );
        }

        $lastNota = $query->orderByDesc('id')->first();
        $nextNumber = 1;

        if ($lastNota && $lastNota->customer_order_number) {
            $parts = explode(
                ' - ',
                $lastNota->customer_order_number,
                2
            );

            if (isset($parts[0]) && is_numeric(trim($parts[0]))) {
                $nextNumber = (int) trim($parts[0]) + 1;
            }
        }

        return sprintf('%03d - %s', $nextNumber, $kitchenName);
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
        $template = strtolower(
            (string) ($supplier?->nota_template ?? '')
        );

        $supplierName = strtolower(
            (string) ($supplier?->name ?? '')
        );

        $source = $template . ' ' . $supplierName;

        $prefix = match (true) {
            str_contains($source, 'gemilang') => 'GM',
            str_contains($source, 'zenzi'),
            str_contains($source, 'zenzie') => 'ZN',
            str_contains($source, 'sumber') => 'SR',
            str_contains($source, 'top fast'),
            str_contains($source, 'topfast') => 'TF',
            default => 'OT',
        };

        $date = Carbon::parse($notaDate ?? now());

        $query = NotaKeluar::where(
            'supplier_id',
            $supplier?->id
        )->whereDate(
            'nota_date',
            $date->format('Y-m-d')
        );

        if ($notaId) {
            $query->where('id', '<=', $notaId);
        }

        $nextNumber = max(1, $query->count());

        return sprintf(
            '%s-%s-%03d',
            $prefix,
            $date->format('Ymd'),
            $nextNumber
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR NOTA
    |--------------------------------------------------------------------------
    */

    private function generateNotaNumber($date)
    {
        $date = Carbon::parse($date);
        $prefix = 'NK-' . $date->format('Ymd');

        $lastNota = NotaKeluar::where(
            'nota_number',
            'like',
            $prefix . '-%'
        )
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastNota) {
            $parts = explode('-', $lastNota->nota_number);

            if (count($parts) >= 3) {
                $nextNumber = (int) end($parts) + 1;
            }
        }

        return sprintf('%s-%03d', $prefix, $nextNumber);
    }
}