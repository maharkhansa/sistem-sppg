<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;

class StockReconciliationController extends Controller
{
    /**
     * Halaman audit rekonsiliasi stok.
     *
     * Controller ini hanya membaca data.
     * Tidak mengubah saldo, transaksi, atau database.
     */
    public function index(Request $request)
    {
        // LOG PEMERIKSAAN SEMENTARA
        logger()->info('Pemeriksaan filter Rekonsiliasi Stok', [
            'search' => $request->query('search'),
            'type' => $request->query('type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        // Pertahankan parameter lama agar tautan yang sudah ada tetap berfungsi.
        $excludeDummy = $request->boolean('exclude_dummy');
        $dummyIds = [];

        // Validasi filter detail transaksi.
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:IN,OUT'],
            'date_from' => ['nullable', 'date'],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $search = trim($filters['search'] ?? '');
        $typeFilter = $filters['type'] ?? '';
        $dateFrom = $filters['date_from'] ?? '';
        $dateTo = $filters['date_to'] ?? '';

        /*
         * Ambil seluruh barang beserta stoknya.
         * Semua barang diperlukan untuk menampilkan detail transaksi,
         * termasuk barang yang tidak dilacak stoknya.
         */
        $items = Item::query()
            ->with('stock')
            ->orderBy('name')
            ->get();

        $itemById = $items->keyBy('id');

        /*
         * Hanya barang dengan track_stock = true yang masuk
         * perhitungan rekonsiliasi stok.
         */
        $trackedItems = $items
            ->filter(fn ($item) => (bool) $item->track_stock)
            ->values();

        $trackedItemIds = $trackedItems
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Ambil seluruh transaksi IN dan OUT.
         *
         * Filter tanggal, jenis, dan pencarian TIDAK diterapkan
         * pada perhitungan saldo. Saldo menggunakan seluruh
         * histori transaksi yang tersedia.
         */
        $transactions = StockTransaction::query()
            ->with('details')
            ->whereIn('type', ['IN', 'OUT'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // LOG DIAGNOSTIK SEMENTARA
        logger()->info('Jumlah data Rekonsiliasi Stok', [
            'jumlah_transaksi' => $transactions->count(),
            'jumlah_detail_transaksi' => $transactions->sum(
                fn ($transaction) => $transaction->details->count()
            ),
        ]);

        /*
         * Rekap histori per barang yang dilacak stoknya.
         */
        $history = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                $itemId = (int) $detail->item_id;

                // Abaikan barang yang tidak menggunakan pelacakan stok.
                if (!in_array($itemId, $trackedItemIds, true)) {
                    continue;
                }

                if (!isset($history[$itemId])) {
                    $history[$itemId] = [
                        'total_in' => 0,
                        'total_out' => 0,
                        'dummy_in' => 0,
                        'dummy_out' => 0,
                    ];
                }

                $quantity = (float) $detail->quantity;

                if ($transaction->type === 'IN') {
                    $history[$itemId]['total_in'] += $quantity;
                } elseif ($transaction->type === 'OUT') {
                    $history[$itemId]['total_out'] += $quantity;
                }
            }
        }

        /*
         * Susun baris rekonsiliasi.
         * Barang dengan track_stock = false tidak dimasukkan.
         */
        $rows = $trackedItems->map(function ($item) use ($history) {
            $itemId = (int) $item->id;
            $stock = $item->stock;

            $currentStock = $stock
                ? (float) $stock->quantity
                : 0.0;

            $itemHistory = $history[$itemId] ?? [
                'total_in' => 0,
                'total_out' => 0,
                'dummy_in' => 0,
                'dummy_out' => 0,
            ];

            $totalIn = (float) $itemHistory['total_in'];
            $totalOut = (float) $itemHistory['total_out'];

            // Saldo histori = seluruh IN dikurangi seluruh OUT.
            $expectedAll = $totalIn - $totalOut;

            // Tidak ada transaksi dummy yang disimulasikan.
            $dummyIn = 0;
            $dummyOut = 0;
            $simulatedBalance = $expectedAll;

            $difference = $currentStock - $expectedAll;

            $comparisonBalance = $expectedAll;
            $comparisonDifference = $difference;

            if (!$stock) {
                $status = 'Record Stok Tidak Ada';
            } elseif (abs($difference) < 0.00001) {
                $status = 'Sesuai Histori';
            } else {
                $status = 'Saldo Berbeda';
            }

            return [
                'item_id' => $item->id,
                'code' => $item->code ?? '-',
                'name' => $item->name ?? '-',
                'unit' => $item->unit ?? '-',
                'current_stock' => $currentStock,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'dummy_in' => $dummyIn,
                'dummy_out' => $dummyOut,
                'expected_all' => $expectedAll,
                'simulated_balance' => $simulatedBalance,
                'difference' => $difference,
                'comparison_balance' => $comparisonBalance,
                'comparison_difference' => $comparisonDifference,
                'status' => $status,
            ];
        });

        /*
         * Ringkasan rekonsiliasi.
         */
        $summary = [
            'total_items' => $trackedItems->count(),

            'stock_items' => $trackedItems->filter(
                fn ($item) => $item->stock !== null
            )->count(),

            'mismatches' => $rows->filter(
                fn ($row) => abs($row['difference']) >= 0.00001
            )->count(),

            'negative_simulation' => $rows->filter(
                fn ($row) => $row['simulated_balance'] < -0.00001
            )->count(),

            'missing_stock_record' => $trackedItems->filter(
                fn ($item) => $item->stock === null
            )->count(),

            'dummy_transactions' => 0,
            'confirmed_dummy_count' => 0,

            'different_balance' => $rows->filter(
                fn ($row) => abs($row['difference']) >= 0.00001
            )->count(),
        ];

        // Tidak ada transaksi dummy yang dikecualikan secara otomatis.
        $dummyTransactions = collect();

        /*
         * Tampilkan selisih terbesar terlebih dahulu.
         */
        $rows = $rows
            ->sortByDesc(fn ($row) => abs($row['difference']))
            ->values();

        /*
         * Pertahankan struktur object yang digunakan Blade.
         */
        $stockRows = $rows->map(function ($row) {
            return (object) [
                'item_id' => $row['item_id'],
                'item_code' => $row['code'],
                'item_name' => $row['name'],
                'unit' => $row['unit'],
                'total_in' => $row['total_in'],
                'total_out' => $row['total_out'],
                'current_stock' => $row['current_stock'],
                'difference' => $row['difference'],
                'expected_stock' => $row['expected_all'],
                'expected_all' => $row['expected_all'],
                'simulated_balance' => $row['simulated_balance'],
                'dummy_in' => $row['dummy_in'],
                'dummy_out' => $row['dummy_out'],
                'comparison_balance' => $row['comparison_balance'],
                'comparison_difference' => $row['comparison_difference'],
                'status' => $row['status'],
            ];
        });

        $simulation = $stockRows->map(function ($row) {
            return (object) [
                'item_id' => $row->item_id,
                'item_code' => $row->item_code,
                'item_name' => $row->item_name,
                'unit' => $row->unit,
                'current_stock' => $row->current_stock,
                'simulated_stock' => $row->simulated_balance,
                'difference' => $row->difference,
            ];
        });

        /*
         * Detail transaksi.
         *
         * Filter pencarian, jenis, dan tanggal diterapkan hanya
         * pada tabel detail, bukan pada saldo rekonsiliasi.
         */
        $details = collect();

        // Normalisasi kata kunci sekali saja.
        $normalizedSearch = mb_strtolower(
            preg_replace('/\s+/u', ' ', trim($search)),
            'UTF-8'
        );

        foreach ($transactions as $transaction) {
            $transactionDate = substr(
                (string) $transaction->transaction_date,
                0,
                10
            );

            // Filter jenis transaksi.
            if (
                $typeFilter !== ''
                && $transaction->type !== $typeFilter
            ) {
                continue;
            }

            // Filter tanggal awal.
            if (
                $dateFrom !== ''
                && $transactionDate < $dateFrom
            ) {
                continue;
            }

            // Filter tanggal akhir.
            if (
                $dateTo !== ''
                && $transactionDate > $dateTo
            ) {
                continue;
            }

            foreach ($transaction->details as $detail) {
                $itemId = (int) $detail->item_id;
                $item = $itemById->get($itemId);

                $transactionNumber = $transaction->transaction_number
                    ?? $transaction->reference_number
                    ?? null;

                $vendorName = $transaction->vendor_name ?? null;

                $purchaseNoteNumber =
                    $transaction->purchase_note_number ?? null;

                $notes = $transaction->notes
                    ?? $transaction->note
                    ?? '-';

                /*
                 * Cari berdasarkan nomor transaksi, ID transaksi,
                 * kode/nama barang, vendor, nomor nota pembelian,
                 * dan catatan.
                 *
                 * Pencarian tidak membedakan huruf besar/kecil
                 * dan merapikan spasi berlebih.
                 */
                if ($normalizedSearch !== '') {
                    $searchableText = implode(' ', [
                        (string) ($transactionNumber ?? ''),
                        (string) $transaction->id,
                        (string) ($item->code ?? ''),
                        (string) ($item->name ?? ''),
                        (string) ($vendorName ?? ''),
                        (string) ($purchaseNoteNumber ?? ''),
                        (string) ($notes ?? ''),
                    ]);

                    $normalizedText = mb_strtolower(
                        preg_replace(
                            '/\s+/u',
                            ' ',
                            trim($searchableText)
                        ),
                        'UTF-8'
                    );

                    if (
                        mb_strpos(
                            $normalizedText,
                            $normalizedSearch,
                            0,
                            'UTF-8'
                        ) === false
                    ) {
                        continue;
                    }
                }

                $details->push((object) [
                    'transaction_date' => $transaction->transaction_date,
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transactionNumber,
                    'type' => $transaction->type,
                    'item_code' => $item->code ?? '-',
                    'item_name' => $item->name ?? '-',
                    'item_unit' => $item->unit ?? '-',
                    'quantity' => $detail->quantity ?? 0,
                    'vendor_name' => $vendorName,
                    'purchase_note_number' => $purchaseNoteNumber,
                    'notes' => $notes,
                    'track_stock' => (bool) ($item->track_stock ?? false),
                ]);
            }
        }

        return view('stock_reconciliation.index', [
            'items' => $items,
            'rows' => $rows,
            'stockRows' => $stockRows,
            'simulation' => $simulation,
            'details' => $details,
            'summary' => $summary,
            'dummyTransactions' => $dummyTransactions,
            'dummyIds' => $dummyIds,
            'excludeDummy' => $excludeDummy,
        ]);
    }
}