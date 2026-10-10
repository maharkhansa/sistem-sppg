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
    | DAFTAR NOTA KELUAR
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
        ])
            ->latest()
            ->get();

        return view('nota_keluars.index', compact('notaKeluars'));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL NOTA KELUAR
    |--------------------------------------------------------------------------
    */

    public function show(NotaKeluar $notaKeluar)
    {
        $notaId = $notaKeluar->id;

        $invoice = $this->findInvoiceForNota($notaKeluar);

        // Sinkronkan Nota dengan Invoice terbaru.
        if ($invoice && $notaKeluar->stockTransaction) {
            $this->syncNotasFromInvoice(
                $invoice,
                $notaKeluar->stockTransaction
            );
        }

        // Nota mungkin sudah tidak diperlukan setelah pembagian Invoice berubah.
        $notaKeluar = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'supplier',
            'details.item',
            'details.supplier',
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
        ])->find($notaId);

        if (!$notaKeluar) {
            return redirect()
                ->route('nota-keluars.index')
                ->with(
                    'success',
                    'Daftar Nota telah disesuaikan dengan Invoice terbaru.'
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

        $notaKeluar->update([
            'total_amount' => $notaTotalAmount,
        ]);

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
            $customerOrderNumber = $this->generateCustomerOrderNumber(
                $notaKeluar->kitchen_id,
                $notaKeluar->stock_transaction_id
            );

            $notaKeluar->update([
                'customer_order_number' => $customerOrderNumber,
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
    | CETAK NOTA KELUAR
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
            'invoiceNotaAllocations.invoiceDetail.item',
            'invoiceNotaAllocations.invoiceDetail.supplier',
        ])->find($notaId);

        if (!$notaKeluar) {
            return redirect()
                ->route('nota-keluars.index')
                ->with(
                    'success',
                    'Nota telah disesuaikan dengan Invoice terbaru.'
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

        $notaKeluar->update([
            'total_amount' => $total,
        ]);

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
    | MEMBUAT / MENYINKRONKAN NOTA DARI BARANG KELUAR
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
        ])
            ->where(
                'stock_transaction_id',
                $stockTransaction->id
            )
            ->first();

        if (!$invoice) {
            return back()->with(
                'error',
                'Invoice untuk Barang Keluar ini belum tersedia. '
                . 'Buat Invoice terlebih dahulu.'
            );
        }

        $this->syncNotasFromInvoice(
            $invoice,
            $stockTransaction
        );

        $firstNota = NotaKeluar::where(
            'stock_transaction_id',
            $stockTransaction->id
        )
            ->orderBy('section_order')
            ->orderBy('id')
            ->first();

        if (!$firstNota) {
            return back()->with(
                'error',
                'Tidak ada detail Invoice yang memiliki supplier. '
                . 'Periksa kembali detail Invoice.'
            );
        }

        return redirect()
            ->route('nota-keluars.show', $firstNota)
            ->with(
                'success',
                'Nota berhasil dibuat dan disinkronkan dengan Invoice.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SINKRONISASI NOTA DENGAN INVOICE
    |--------------------------------------------------------------------------
    |
    | Satu Nota untuk setiap kombinasi supplier + section_name.
    | Jika section_name kosong, Nota dibuat tanpa pembagian bagian.
    |
    | Isi dan alokasi Nota bersumber dari invoice_details.
    |--------------------------------------------------------------------------
    */

    private function syncNotasFromInvoice(
        Invoice $invoice,
        StockTransaction $stockTransaction
    ): void {
        $invoice->loadMissing([
            'details.item',
            'details.supplier',
        ]);

        DB::transaction(function () use (
            $invoice,
            $stockTransaction
        ) {
            $invoiceDetails = $invoice->details
                ->filter(function ($detail) {
                    return !empty($detail->supplier_id);
                })
                ->values();

            // Kelompokkan berdasarkan supplier dan nama bagian.
            $groups = $invoiceDetails->groupBy(function ($detail) {
                $sectionName = trim(
                    (string) ($detail->section_name ?? '')
                );

                return json_encode([
                    (int) $detail->supplier_id,
                    $sectionName,
                ]);
            });

            $existingNotas = NotaKeluar::where(
                'stock_transaction_id',
                $stockTransaction->id
            )
                ->lockForUpdate()
                ->get();

            $usedNotaIds = [];

            $transactionDate = $invoice->invoice_date
                ?? $stockTransaction->transaction_date
                ?? now()->toDateString();

            $customerOrderNumber = $this->generateCustomerOrderNumber(
                $stockTransaction->kitchen_id,
                $stockTransaction->id
            );

            foreach ($groups as $groupDetails) {
                $firstDetail = $groupDetails->first();

                $supplierId = (int) $firstDetail->supplier_id;

                $sectionName = trim(
                    (string) ($firstDetail->section_name ?? '')
                );

                $sectionName = $sectionName !== ''
                    ? $sectionName
                    : null;

                $sectionOrder = $firstDetail->section_order;

                // Cari Nota lama yang cocok dengan supplier + bagian.
                $notaKeluar = $existingNotas->first(
                    function ($nota) use (
                        $supplierId,
                        $sectionName,
                        $usedNotaIds
                    ) {
                        if (in_array(
                            $nota->id,
                            $usedNotaIds,
                            true
                        )) {
                            return false;
                        }

                        $notaSection = trim(
                            (string) ($nota->section_name ?? '')
                        );

                        $wantedSection = trim(
                            (string) ($sectionName ?? '')
                        );

                        return (int) $nota->supplier_id === $supplierId
                            && $notaSection === $wantedSection;
                    }
                );

                $totalAmount = $groupDetails->sum(
                    fn ($detail) => (float) (
                        $detail->subtotal
                        ?? (
                            (float) $detail->quantity
                            * (float) $detail->unit_price
                        )
                    )
                );

                if (!$notaKeluar) {
                    $supplier = Supplier::find($supplierId);

                    if (!$supplier) {
                        continue;
                    }

                    $notaKeluar = NotaKeluar::create([
                        'stock_transaction_id' =>
                            $stockTransaction->id,
                        'purchase_order_id' =>
                            $stockTransaction->purchase_order_id,
                        'kitchen_id' =>
                            $stockTransaction->kitchen_id,
                        'supplier_id' =>
                            $supplierId,
                        'section_name' =>
                            $sectionName,
                        'section_order' =>
                            $sectionOrder,
                        'nota_number' =>
                            $this->generateNotaNumber($transactionDate),
                        'barcode_number' =>
                            null,
                        'customer_order_number' =>
                            $customerOrderNumber,
                        'nota_date' =>
                            $transactionDate,
                        'total_amount' =>
                            $totalAmount,
                    ]);

                    $notaKeluar->update([
                        'barcode_number' => $this->generateBarcodeNumber(
                            $supplier,
                            $transactionDate,
                            $notaKeluar->id
                        ),
                    ]);
                } else {
                    $notaKeluar->update([
                        'purchase_order_id' =>
                            $stockTransaction->purchase_order_id,
                        'kitchen_id' =>
                            $stockTransaction->kitchen_id,
                        'supplier_id' =>
                            $supplierId,
                        'section_name' =>
                            $sectionName,
                        'section_order' =>
                            $sectionOrder,
                        'nota_date' =>
                            $transactionDate,
                        'total_amount' =>
                            $totalAmount,
                    ]);
                }

                $usedNotaIds[] = $notaKeluar->id;

                // Hapus alokasi lama, kemudian buat berdasarkan Invoice terbaru.
                $notaKeluar->invoiceNotaAllocations()->delete();

                foreach ($groupDetails as $detail) {
                    InvoiceNotaAllocation::create([
                        'nota_keluar_id' =>
                            $notaKeluar->id,
                        'invoice_detail_id' =>
                            $detail->id,
                        'quantity' =>
                            $detail->quantity,
                    ]);
                }

                // Perbarui detail Nota lama agar kode lama tidak tertinggal.
                $notaKeluar->details()->delete();

                foreach ($groupDetails as $detail) {
                    $notaKeluar->details()->create([
                        'item_id' =>
                            $detail->item_id,
                        'supplier_id' =>
                            $supplierId,
                        'quantity' =>
                            $detail->quantity,
                        'unit' =>
                            $detail->unit ?? $detail->item?->unit,
                        'unit_price' =>
                            $detail->unit_price,
                        'subtotal' =>
                            $detail->subtotal
                            ?? (
                                (float) $detail->quantity
                                * (float) $detail->unit_price
                            ),
                    ]);
                }
            }

            // Hapus Nota yang sudah tidak ada dalam Invoice terbaru.
            $staleNotas = $existingNotas->filter(
                fn ($nota) => !in_array(
                    $nota->id,
                    $usedNotaIds,
                    true
                )
            );

            foreach ($staleNotas as $staleNota) {
                $staleNota->invoiceNotaAllocations()->delete();
                $staleNota->details()->delete();
                $staleNota->delete();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL INVOICE UNTUK NOTA
    |--------------------------------------------------------------------------
    */

    private function findInvoiceForNota(
        NotaKeluar $notaKeluar
    ): ?Invoice {
        return Invoice::with([
            'details.item',
            'details.supplier',
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
        | AMBIL DETAIL INVOICE TERBARU SESUAI NOTA
        |--------------------------------------------------------------------------
        |
        | Invoice menjadi sumber data utama.
        | Barang yang baru ditambahkan atau diubah pada Barang Keluar
        | akan ikut tampil pada Nota sesuai supplier dan bagian.
        |
        */

        private function getInvoiceDetailsForNota(NotaKeluar $notaKeluar)
        {
            $invoice = $this->findInvoiceForNota($notaKeluar);

            if (!$invoice) {
                return collect();
            }

            $sectionName = trim(
                (string) ($notaKeluar->section_name ?? '')
            );

            return $invoice->details
                ->filter(function ($detail) use ($notaKeluar, $sectionName) {
                    // Hanya tampilkan barang milik supplier Nota ini.
                    if (
                        (int) ($detail->supplier_id ?? 0)
                        !== (int) $notaKeluar->supplier_id
                    ) {
                        return false;
                    }

                    // Bagian Nota harus sama dengan bagian pada Invoice.
                    $detailSection = trim(
                        (string) ($detail->section_name ?? '')
                    );

                    return $detailSection === $sectionName;
                })
                ->map(function ($detail) {
                    // Salin agar tidak mengubah data relasi Invoice di memori.
                    $item = clone $detail;

                    $item->quantity = (float) $detail->quantity;

                    $item->subtotal = round(
                        (float) $detail->quantity
                        * (float) $detail->unit_price,
                        2
                    );

                    return $item;
                })
                ->values();
        }

    /*
    |--------------------------------------------------------------------------
    | EDIT INFORMASI NOTA
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
    | GENERATE NOMOR PELANGGAN / ORDER
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
    | GENERATE BARCODE
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

        $prefix = match (true) {
            str_contains($template, 'gemilang') => 'GM',
            str_contains($template, 'zenzi'),
            str_contains($template, 'zenzie') => 'ZN',
            str_contains($template, 'sumber') => 'SR',
            str_contains($template, 'top') => 'TF',
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