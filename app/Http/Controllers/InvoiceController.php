<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\StockTransaction;
use App\Models\NotaKeluar;
use App\Models\InvoiceNotaAllocation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class InvoiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR INVOICE
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | DETAIL INVOICE
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | MEMBUAT INVOICE DARI BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function createFromOut(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type !== 'OUT') {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Invoice hanya dapat dibuat dari transaksi Barang Keluar.'
                );
        }

        if (!$stockTransaction->purchase_order_id) {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi Barang Keluar tidak memiliki Purchase Order.'
                );
        }

        $existingInvoice = Invoice::where(
            'stock_transaction_id',
            $stockTransaction->id
        )->first();

        if ($existingInvoice) {
            return redirect()
                ->route('invoices.show', $existingInvoice->id)
                ->with(
                    'error',
                    'Invoice untuk transaksi Barang Keluar ini sudah tersedia.'
                );
        }

        $stockTransaction->load([
            'kitchen',
            'details.item.supplier',
            'details.supplier',
            'purchaseOrder.details',
        ]);

        if ($stockTransaction->details->isEmpty()) {
            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Transaksi Barang Keluar belum memiliki detail barang.'
                );
        }

        try {
            $invoiceId = DB::transaction(function () use ($stockTransaction) {
                $existing = Invoice::where(
                    'stock_transaction_id',
                    $stockTransaction->id
                )
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing->id;
                }

                $invoice = Invoice::create([
                    'invoice_number' => $this->generateInvoiceNumber(
                        $stockTransaction->transaction_date
                    ),
                    'invoice_date' => $stockTransaction->transaction_date,
                    'stock_transaction_id' => $stockTransaction->id,
                    'purchase_order_id' => $stockTransaction->purchase_order_id,
                    'kitchen_id' => $stockTransaction->kitchen_id,
                    'total_amount' => 0,
                    'status' => 'DRAFT',
                    'notes' => 'Invoice dibuat otomatis dari OUT '
                        . $stockTransaction->transaction_number,
                    'created_by' => auth()->id() ?? 1,
                ]);

                $totalAmount = 0;
                $purchaseOrderDetails = $stockTransaction
                    ->purchaseOrder?->details ?? collect();

                foreach ($stockTransaction->details as $outDetail) {
                    $supplierId = $this->resolveDetailSupplierId(
                        $outDetail,
                        $purchaseOrderDetails
                    );

                    $subtotal = round(
                        (float) $outDetail->quantity
                        * (float) $outDetail->unit_price,
                        2
                    );

                    $invoice->details()->create([
                        'supplier_id' => $supplierId,
                        'item_id' => $outDetail->item_id,
                        'quantity' => $outDetail->quantity,
                        'unit' => $outDetail->unit,
                        'unit_price' => $outDetail->unit_price,
                        'subtotal' => $subtotal,

                        // Pembagian bagian diwariskan dari Barang Keluar.
                        'section_name' => $outDetail->section_name ?: null,
                        'section_order' => $outDetail->section_name
                            ? $outDetail->section_order
                            : null,

                        'notes' => null,
                    ]);

                    $totalAmount += $subtotal;
                }

                $invoice->update([
                    'total_amount' => $totalAmount,
                ]);

                if (!$invoice->details()->exists()) {
                    throw new Exception(
                        'Detail Invoice gagal dibuat.'
                    );
                }

                return $invoice->id;
            });

            return redirect()
                ->route('invoices.show', $invoiceId)
                ->with(
                    'success',
                    'Invoice berhasil dibuat dari Barang Keluar.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('stock-transactions.out')
                ->with(
                    'error',
                    'Gagal membuat Invoice. Periksa log Laravel untuk detail kesalahan.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SINKRONISASI INVOICE DARI BARANG KELUAR
    |--------------------------------------------------------------------------
    |
    | Mempertahankan ID InvoiceDetail yang masih cocok.
    | Detail yang baru ditambahkan akan dibuat.
    | Detail yang benar-benar dihapus dari OUT akan dihapus dari Invoice.
    |--------------------------------------------------------------------------
    */

    public function syncFromOut(StockTransaction $stockTransaction): void
    {
        if ($stockTransaction->type !== 'OUT') {
            return;
        }

        DB::transaction(function () use ($stockTransaction) {
            $invoice = Invoice::where(
                'stock_transaction_id',
                $stockTransaction->id
            )
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                return;
            }

            $stockTransaction->load([
                'details.item.supplier',
                'details.supplier',
                'purchaseOrder.details',
            ]);

            $existingDetails = $invoice->details()
                ->lockForUpdate()
                ->get();

            $unusedDetails = $existingDetails->keyBy('id');
            $purchaseOrderDetails = $stockTransaction
                ->purchaseOrder?->details ?? collect();

            $totalAmount = 0;

            foreach ($stockTransaction->details as $outDetail) {
                $supplierId = $this->resolveDetailSupplierId(
                    $outDetail,
                    $purchaseOrderDetails
                );

                $subtotal = round(
                    (float) $outDetail->quantity
                    * (float) $outDetail->unit_price,
                    2
                );

                /*
                 * Cocokkan berdasarkan barang, supplier, dan satuan.
                 * Harga dan jumlah boleh berubah tanpa mengganti ID.
                 */
                $matched = $unusedDetails->first(
                    function ($detail) use ($outDetail, $supplierId) {
                        return (int) $detail->item_id
                                === (int) $outDetail->item_id
                            && (int) ($detail->supplier_id ?? 0)
                                === (int) ($supplierId ?? 0)
                            && (string) $detail->unit
                                === (string) $outDetail->unit
                            && trim((string) ($detail->section_name ?? ''))
                                === trim((string) ($outDetail->section_name ?? ''))
                            && (int) ($detail->section_order ?? 0)
                                === (int) ($outDetail->section_order ?? 0);
                    }
                );

                $attributes = [
                    'supplier_id' => $supplierId,
                    'item_id' => $outDetail->item_id,
                    'quantity' => $outDetail->quantity,
                    'unit' => $outDetail->unit,
                    'unit_price' => $outDetail->unit_price,
                    'subtotal' => $subtotal,

                    'section_name' => $outDetail->section_name ?: null,
                    'section_order' => $outDetail->section_name
                        ? $outDetail->section_order
                        : null,
                ];

                if ($matched) {
                    $matched->update($attributes);
                    $unusedDetails->forget($matched->id);
                } else {
                    $invoice->details()->create($attributes);
                }

                $totalAmount += $subtotal;
            }

            /*
             * Hapus hanya detail yang tidak lagi ditemukan pada OUT.
             * Foreign key alokasi harus menggunakan ON DELETE CASCADE
             * atau alokasi terkait dihapus secara eksplisit.
             */
            foreach ($unusedDetails as $unusedDetail) {
                InvoiceNotaAllocation::where(
                    'invoice_detail_id',
                    $unusedDetail->id
                )->delete();

                $unusedDetail->delete();
            }

            $invoice->update([
                'invoice_date' => $stockTransaction->transaction_date,
                'purchase_order_id' => $stockTransaction->purchase_order_id,
                'kitchen_id' => $stockTransaction->kitchen_id,
                'total_amount' => $totalAmount,
            ]);

            /*
             * Hitung ulang total Nota berdasarkan alokasi detail Invoice.
             * Nota lama tidak dihapus otomatis.
             */
            $notes = NotaKeluar::where(
                'stock_transaction_id',
                $stockTransaction->id
            )->get();

            foreach ($notes as $nota) {
                $notaTotal = InvoiceNotaAllocation::where(
                    'nota_keluar_id',
                    $nota->id
                )
                    ->join(
                        'invoice_details',
                        'invoice_details.id',
                        '=',
                        'invoice_nota_allocations.invoice_detail_id'
                    )
                    ->sum('invoice_details.subtotal');

                $nota->update([
                    'total_amount' => $notaTotal,
                    'nota_date' => $invoice->invoice_date,
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PEMBAGIAN NOTA
    |--------------------------------------------------------------------------
    */

    public function notaAllocation(Invoice $invoice)
    {
        $invoice->load([
            'kitchen',
            'stockTransaction',
            'purchaseOrder.details',
            'details.item.supplier',
            'details.supplier',
        ]);

        DB::transaction(function () use ($invoice) {
            $purchaseOrderDetails = $invoice
                ->purchaseOrder?->details ?? collect();

            $supplierIds = $invoice->details
                ->map(function ($detail) use ($purchaseOrderDetails) {
                    return $this->resolveDetailSupplierId(
                        $detail,
                        $purchaseOrderDetails
                    );
                })
                ->filter()
                ->unique()
                ->values();

            $noteDate = $invoice->invoice_date
                ?? $invoice->stockTransaction?->transaction_date
                ?? now()->toDateString();

            foreach ($supplierIds as $supplierId) {
                $supplier = Supplier::find($supplierId);

                if (!$supplier) {
                    continue;
                }

                $existingNotes = NotaKeluar::where(
                    'stock_transaction_id',
                    $invoice->stock_transaction_id
                )
                    ->where('supplier_id', $supplierId)
                    ->orderBy('section_order')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                /*
                 * Buat satu Nota awal hanya jika supplier belum memiliki Nota.
                 * Tidak ada aturan minimal tiga Nota.
                 */
                if ($existingNotes->isEmpty()) {
                    $this->createNotaForSupplier(
                        $invoice,
                        $supplier,
                        $noteDate
                    );

                    continue;
                }

                foreach ($existingNotes as $index => $nota) {
                    if (!$nota->section_name) {
                        $nota->update([
                            'section_name' => 'Bagian ' . ($index + 1),
                            'section_order' => $index + 1,
                        ]);
                    }
                }
            }
        });

        $notes = NotaKeluar::with([
            'supplier',
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
        ])
            ->where(
                'stock_transaction_id',
                $invoice->stock_transaction_id
            )
            ->orderBy('supplier_id')
            ->orderBy('section_order')
            ->orderBy('id')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();

        /*
         * Data $notes dan $suppliers tetap kompatibel dengan
         * halaman invoices.nota-allocation yang sudah ada.
         */
        return view('invoices.nota-allocation', compact(
            'invoice',
            'notes',
            'suppliers'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN ALOKASI DETAIL INVOICE KE NOTA
    |--------------------------------------------------------------------------
    */

    public function saveNotaAllocation(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'notes' => ['required', 'array', 'min:1'],

            'notes.*.nota_keluar_id' => [
                'nullable',
                'integer',
            ],

            'notes.*.supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'notes.*.section_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes.*.section_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'notes.*.detail_ids' => [
                'nullable',
                'array',
            ],

            'notes.*.detail_ids.*' => [
                'integer',
            ],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $invoice->load([
            'details.item.supplier',
            'details.supplier',
            'stockTransaction.purchaseOrder.details',
        ]);

        $details = $invoice->details->keyBy('id');

        if ($details->isEmpty()) {
            return back()->with(
                'error',
                'Invoice belum memiliki detail barang.'
            );
        }

        $submittedNotes = $request->input('notes', []);
        $assignedDetailIds = [];
        $seenNoteIds = [];
        $resolvedNotes = [];

        $purchaseOrderDetails = $invoice
            ->stockTransaction?->purchaseOrder?->details ?? collect();

        /*
         * Validasi sebelum mengubah alokasi di database.
         */
        foreach ($submittedNotes as $index => $noteData) {
            $supplierId = (int) ($noteData['supplier_id'] ?? 0);

            $noteId = !empty($noteData['nota_keluar_id'])
                ? (int) $noteData['nota_keluar_id']
                : null;

            $detailIds = array_values(array_unique(array_map(
                'intval',
                $noteData['detail_ids'] ?? []
            )));

            if ($noteId) {
                if (isset($seenNoteIds[$noteId])) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'notes' =>
                                'Nota yang sama tidak boleh dikirim dua kali.',
                        ]);
                }

                $existingNote = NotaKeluar::whereKey($noteId)
                    ->where(
                        'stock_transaction_id',
                        $invoice->stock_transaction_id
                    )
                    ->first();

                if (!$existingNote) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            "notes.$index.nota_keluar_id" =>
                                'Nota tidak ditemukan pada transaksi ini.',
                        ]);
                }

                if ((int) $existingNote->supplier_id !== $supplierId) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            "notes.$index.supplier_id" =>
                                'Supplier Nota yang tersimpan tidak boleh diganti.',
                        ]);
                }

                $seenNoteIds[$noteId] = true;
            }

            foreach ($detailIds as $detailId) {
                if (!$details->has($detailId)) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'notes' =>
                                'Terdapat detail barang yang bukan milik Invoice ini.',
                        ]);
                }

                if (isset($assignedDetailIds[$detailId])) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'notes' =>
                                'Satu detail barang tidak boleh dialokasikan ke lebih dari satu Nota.',
                        ]);
                }

                $detail = $details->get($detailId);

                $detailSupplierId = $this->resolveDetailSupplierId(
                    $detail,
                    $purchaseOrderDetails
                );

                if (!$detailSupplierId) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'notes' =>
                                'Supplier barang '
                                . ($detail->item?->name ?? '-')
                                . ' tidak ditemukan.',
                        ]);
                }

                if ((int) $detailSupplierId !== $supplierId) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'notes' =>
                                'Supplier Nota harus sesuai dengan supplier barang.',
                        ]);
                }

                $assignedDetailIds[$detailId] = true;
            }

            $resolvedNotes[] = [
                'nota_keluar_id' => $noteId,
                'supplier_id' => $supplierId,
                'section_name' => trim(
                    (string) ($noteData['section_name'] ?? '')
                ),
                'section_order' => (int) (
                    $noteData['section_order'] ?? ($index + 1)
                ),
                'detail_ids' => $detailIds,
            ];
        }

        /*
         * Setiap detail Invoice wajib masuk tepat ke satu Nota.
         */
        $missingDetailIds = $details->keys()
            ->map(fn ($id) => (int) $id)
            ->diff(array_keys($assignedDetailIds));

        if ($missingDetailIds->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'notes' =>
                        'Semua barang Invoice harus dialokasikan tepat ke satu Nota. '
                        . 'Masih ada '
                        . $missingDetailIds->count()
                        . ' detail barang yang belum dialokasikan.',
                ]);
        }

        try {
            DB::transaction(function () use (
                $invoice,
                $resolvedNotes,
                $details
            ) {
                $transactionId = $invoice->stock_transaction_id;
                $transaction = $invoice->stockTransaction;

                $noteDate = $invoice->invoice_date
                    ?? $transaction?->transaction_date
                    ?? now()->toDateString();

                $existingNotes = NotaKeluar::where(
                    'stock_transaction_id',
                    $transactionId
                )
                    ->lockForUpdate()
                    ->get();

                /*
                 * Hapus alokasi lama, bukan Nota-nya.
                 */
                if ($existingNotes->isNotEmpty()) {
                    InvoiceNotaAllocation::whereIn(
                        'nota_keluar_id',
                        $existingNotes->pluck('id')
                    )->delete();
                }

                foreach ($existingNotes as $existingNote) {
                    $existingNote->update([
                        'total_amount' => 0,
                        'nota_date' => $noteDate,
                    ]);
                }

                foreach ($resolvedNotes as $row) {
                    $noteId = $row['nota_keluar_id'];
                    $supplierId = $row['supplier_id'];
                    $detailIds = $row['detail_ids'];

                    /*
                     * Baris Nota baru yang tidak berisi barang dilewati.
                     * Nota lama yang kosong tetap dipertahankan.
                     */
                    if (!$noteId && empty($detailIds)) {
                        continue;
                    }

                    if ($noteId) {
                        $nota = NotaKeluar::whereKey($noteId)
                            ->where(
                                'stock_transaction_id',
                                $transactionId
                            )
                            ->lockForUpdate()
                            ->firstOrFail();
                    } else {
                        $supplier = Supplier::findOrFail($supplierId);

                        $nota = $this->createNotaForSupplier(
                            $invoice,
                            $supplier,
                            $noteDate
                        );
                    }

                    $sectionName = $row['section_name'];

                    if ($sectionName === '') {
                        $sectionName = $nota->section_name
                            ?: 'Bagian ' . $row['section_order'];
                    }

                    $totalAmount = 0;

                    foreach ($detailIds as $detailId) {
                        $detail = $details->get($detailId);

                        if (!$detail) {
                            continue;
                        }

                        InvoiceNotaAllocation::create([
                            'nota_keluar_id' => $nota->id,
                            'invoice_detail_id' => $detail->id,
                            'quantity' => $detail->quantity,
                        ]);

                        $totalAmount += (float) (
                            $detail->subtotal
                            ?? (
                                (float) $detail->quantity
                                * (float) $detail->unit_price
                            )
                        );
                    }

                    $nota->update([
                        'supplier_id' => $supplierId,
                        'section_name' => $sectionName,
                        'section_order' => $row['section_order'],
                        'nota_date' => $noteDate,
                        'total_amount' => $totalAmount,
                    ]);
                }
            });

            return redirect()
                ->route('invoices.nota-allocation', $invoice)
                ->with(
                    'success',
                    'Pembagian detail Invoice ke Nota berhasil disimpan.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan pembagian Nota. Periksa log Laravel.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBUAT NOTA BARU UNTUK BAGIAN INVOICE
    |--------------------------------------------------------------------------
    */

    private function createNotaForSupplier(
        Invoice $invoice,
        Supplier $supplier,
        $noteDate
    ): NotaKeluar {
        $sectionOrder = (int) NotaKeluar::where(
            'stock_transaction_id',
            $invoice->stock_transaction_id
        )
            ->where('supplier_id', $supplier->id)
            ->max('section_order') + 1;

        return NotaKeluar::create([
            'stock_transaction_id' => $invoice->stock_transaction_id,
            'purchase_order_id' => $invoice->purchase_order_id,
            'kitchen_id' => $invoice->kitchen_id,
            'supplier_id' => $supplier->id,
            'section_name' => 'Bagian ' . $sectionOrder,
            'section_order' => $sectionOrder,
            'nota_number' => $this->generateNotaNumber($noteDate),
            'barcode_number' => $this->generateBarcodeNumber(
                $supplier,
                $noteDate
            ),
            'customer_order_number' => null,
            'nota_date' => $noteDate,
            'total_amount' => 0,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR INVOICE
    |--------------------------------------------------------------------------
    */

    private function generateInvoiceNumber($invoiceDate): string
    {
        $date = \Carbon\Carbon::parse($invoiceDate)->format('Ymd');
        $prefix = 'INV-' . $date . '-';

        $number = (int) Invoice::where(
            'invoice_number',
            'like',
            $prefix . '%'
        )->count() + 1;

        do {
            $invoiceNumber = $prefix . str_pad(
                (string) $number,
                3,
                '0',
                STR_PAD_LEFT
            );

            $number++;
        } while (
            Invoice::where('invoice_number', $invoiceNumber)->exists()
        );

        return $invoiceNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE NOMOR NOTA
    |--------------------------------------------------------------------------
    */

    private function generateNotaNumber($noteDate): string
    {
        $dateCode = \Carbon\Carbon::parse($noteDate)->format('Ymd');
        $prefix = 'NK-' . $dateCode . '-';

        $number = (int) NotaKeluar::where(
            'nota_number',
            'like',
            $prefix . '%'
        )->count() + 1;

        do {
            $notaNumber = $prefix . str_pad(
                (string) $number,
                3,
                '0',
                STR_PAD_LEFT
            );

            $number++;
        } while (
            NotaKeluar::where('nota_number', $notaNumber)->exists()
        );

        return $notaNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE BARCODE NOTA
    |--------------------------------------------------------------------------
    */

    private function generateBarcodeNumber(
        Supplier $supplier,
        $noteDate
    ): string {
        $dateCode = \Carbon\Carbon::parse($noteDate)->format('Ymd');

        $prefix = $this->getBarcodePrefix($supplier)
            . '-'
            . $dateCode
            . '-';

        $number = 1;

        do {
            $barcodeNumber = $prefix . str_pad(
                (string) $number,
                4,
                '0',
                STR_PAD_LEFT
            );

            $number++;
        } while (
            NotaKeluar::where(
                'barcode_number',
                $barcodeNumber
            )->exists()
        );

        return $barcodeNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | MENENTUKAN SUPPLIER DETAIL
    |--------------------------------------------------------------------------
    */

    private function resolveDetailSupplierId(
        $detail,
        $purchaseOrderDetails = null
    ): ?int {
        $supplierId = $detail->supplier_id
            ?? $detail->supplier?->id
            ?? $detail->item?->supplier_id
            ?? $detail->item?->supplier?->id;

        if ($supplierId) {
            return (int) $supplierId;
        }

        $purchaseOrderDetails = $purchaseOrderDetails ?? collect();

        $poDetail = $purchaseOrderDetails->first(
            fn ($row) =>
                (int) $row->item_id === (int) $detail->item_id
                && !empty($row->supplier_id)
        );

        return $poDetail?->supplier_id
            ? (int) $poDetail->supplier_id
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | PREFIX BARCODE BERDASARKAN SUPPLIER
    |--------------------------------------------------------------------------
    */

    private function getBarcodePrefix(Supplier $supplier): string
    {
        $template = strtolower((string) $supplier->nota_template);
        $supplierName = strtolower((string) $supplier->name);
        $source = $template . ' ' . $supplierName;

        return match (true) {
            str_contains($source, 'gemilang') => 'GM',
            str_contains($source, 'zenzi') => 'ZN',
            str_contains($source, 'sumber') => 'SR',
            str_contains($source, 'top fast'),
            str_contains($source, 'topfast') => 'TF',
            default => 'OT',
        };
    }
}