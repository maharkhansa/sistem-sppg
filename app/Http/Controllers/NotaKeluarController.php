<?php

namespace App\Http\Controllers;

use App\Models\NotaKeluar;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class NotaKeluarController extends Controller
{
    public function index()
    {
        $notaKeluars = NotaKeluar::with([
            'stockTransaction',
            'purchaseOrder',
            'kitchen',
            'details.item',
            'details.supplier',
        ])
            ->latest()
            ->get();

        return view(
            'nota_keluars.index',
            compact('notaKeluars')
        );
    }

    public function show(NotaKeluar $notaKeluar)
    {
    $notaKeluar->load([
        'stockTransaction',
        'purchaseOrder',
        'kitchen',
        'supplier',
        'details.item',
        'details.supplier',
    ]);

    return view(
        'nota_keluars.show',
        compact('notaKeluar')
    );
    }

    public function createFromOut(
    StockTransaction $stockTransaction
    ) {
    if ($stockTransaction->type !== 'OUT') {
        return redirect()
            ->route('stock-transactions.out')
            ->with(
                'error',
                'Nota Keluar hanya dapat dibuat dari transaksi OUT.'
            );
    }

    if (!$stockTransaction->purchase_order_id) {
        return redirect()
            ->route('stock-transactions.out')
            ->with(
                'error',
                'Transaksi OUT ini tidak memiliki Purchase Order.'
            );
    }

    $stockTransaction->load([
        'purchaseOrder.details.supplier',
        'purchaseOrder.details.item',
        'kitchen',
        'details.item',
    ]);

    if ($stockTransaction->details->isEmpty()) {
        return redirect()
            ->route('stock-transactions.out')
            ->with(
                'error',
                'Transaksi OUT tidak memiliki detail barang.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cek apakah Nota Keluar sudah pernah dibuat
    |--------------------------------------------------------------------------
    */

    $existingNota = NotaKeluar::where(
        'stock_transaction_id',
        $stockTransaction->id
    )->first();

    if ($existingNota) {
        return redirect()
            ->route(
                'nota-keluars.show',
                $existingNota->id
            )
            ->with(
                'error',
                'Nota Keluar untuk transaksi OUT ini sudah dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Kelompokkan detail OUT berdasarkan supplier
    |--------------------------------------------------------------------------
    */

    $detailsBySupplier = collect();

    foreach ($stockTransaction->details as $detail) {

        $poDetail = $stockTransaction
            ->purchaseOrder
            ->details
            ->firstWhere(
                'item_id',
                $detail->item_id
            );

        if (!$poDetail || !$poDetail->supplier_id) {
            continue;
        }

        $detailsBySupplier
            ->put(
                $poDetail->supplier_id,
                collect(
                    $detailsBySupplier->get(
                        $poDetail->supplier_id,
                        []
                    )
                )->push([
                    'detail' => $detail,
                    'poDetail' => $poDetail,
                ])
            );
    }

    if ($detailsBySupplier->isEmpty()) {
        return redirect()
            ->route('stock-transactions.out')
            ->with(
                'error',
                'Supplier untuk detail barang pada OUT tidak ditemukan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Buat Nota Keluar berdasarkan supplier
    |--------------------------------------------------------------------------
    */

    $notaKeluarIds = [];

    DB::transaction(function () use (
        $stockTransaction,
        $detailsBySupplier,
        &$notaKeluarIds
    ) {

        foreach (
            $detailsBySupplier as $supplierId => $supplierDetails
        ) {

            $totalAmount = collect(
                $supplierDetails
            )->sum(
                fn ($row) =>
                    $row['detail']->subtotal
            );

            $notaKeluar = NotaKeluar::create([
                'nota_number' =>
                    $this->generateNotaNumber(
                        $stockTransaction->transaction_date
                    ),

                'nota_date' =>
                    $stockTransaction->transaction_date,

                'stock_transaction_id' =>
                    $stockTransaction->id,

                'purchase_order_id' =>
                    $stockTransaction->purchase_order_id,

                'kitchen_id' =>
                    $stockTransaction->kitchen_id,

                'supplier_id' =>
                    $supplierId,

                'total_amount' =>
                    $totalAmount,

                'status' =>
                    'DRAFT',

                'notes' =>
                    'Nota Keluar dibuat otomatis dari OUT ' .
                    $stockTransaction->transaction_number,

                'created_by' =>
                    auth()->id() ?? 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Masukkan detail barang sesuai supplier
            |--------------------------------------------------------------------------
            */

            foreach (
                $supplierDetails as $row
            ) {

                $detail =
                    $row['detail'];

                $notaKeluar->details()->create([
                    'supplier_id' =>
                        $supplierId,

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

                    'notes' =>
                        null,
                ]);
            }

            $notaKeluarIds[] =
                $notaKeluar->id;
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Redirect ke nota pertama
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'nota-keluars.show',
            $notaKeluarIds[0]
        )
        ->with(
            'success',
            'Nota Keluar berhasil dibuat berdasarkan supplier.'
        );
    }

    private function generateNotaNumber(
        $notaDate
    ) {
        $date = date(
            'Ymd',
            strtotime($notaDate)
        );

        $lastNota =
            NotaKeluar::whereDate(
                'nota_date',
                $notaDate
            )
            ->latest('id')
            ->first();

        $number = $lastNota
            ? (
                (int) substr(
                    $lastNota->nota_number,
                    -3
                )
            ) + 1
            : 1;

        return 'NOTA-' .
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