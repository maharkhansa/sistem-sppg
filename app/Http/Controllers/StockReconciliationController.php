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
     * Transaksi yang sudah dihapus tidak lagi dihitung.
     * Controller ini hanya membaca data dan tidak mengubah stok.
     */
    public function index(Request $request)
    {
        // Parameter lama dipertahankan agar tautan Blade tetap berfungsi.
        $excludeDummy = $request->boolean('exclude_dummy');

        // Semua ID dummy lama sudah dihapus dari database.
        $dummyIds = [];

        /*
         * Ambil seluruh item beserta saldo stok.
         */
        $items = Item::query()
            ->with('stock')
            ->orderBy('name')
            ->get();

        /*
         * Ambil histori transaksi masuk dan keluar.
         */
        $transactions = StockTransaction::query()
            ->with('details')
            ->whereIn('type', ['IN', 'OUT'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        /*
         * Rekap histori per item.
         */
        $history = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                $itemId = (int) $detail->item_id;

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
         */
        $rows = $items->map(function ($item) use ($history) {
            $itemId = (int) $item->id;
            $stock = $item->stock;

            $currentStock = $stock
                ? (float) $stock->quantity
                : 0;

            $itemHistory = $history[$itemId] ?? [
                'total_in' => 0,
                'total_out' => 0,
                'dummy_in' => 0,
                'dummy_out' => 0,
            ];

            $totalIn = (float) $itemHistory['total_in'];
            $totalOut = (float) $itemHistory['total_out'];

            // Saldo berdasarkan histori transaksi yang masih tersimpan.
            $expectedAll = $totalIn - $totalOut;

            // Tidak ada lagi transaksi dummy yang perlu disimulasikan.
            $dummyIn = 0;
            $dummyOut = 0;
            $simulatedBalance = $expectedAll;

            $difference = $currentStock - $expectedAll;

            $comparisonBalance = $expectedAll;
            $comparisonDifference = $difference;

            $status = abs($difference) < 0.00001
                ? 'Sesuai Histori'
                : 'Saldo Berbeda';

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
         * Ringkasan halaman.
         */
        $summary = [
            'total_items' => $rows->count(),

            'stock_items' => $items->filter(
                fn ($item) => $item->stock !== null
            )->count(),

            'mismatches' => $rows->filter(
                fn ($row) => abs($row['difference']) >= 0.00001
            )->count(),

            'negative_simulation' => $rows->filter(
                fn ($row) => $row['simulated_balance'] < -0.00001
            )->count(),

            'missing_stock_record' => $items->filter(
                fn ($item) => $item->stock === null
            )->count(),

            'dummy_transactions' => 0,
            'confirmed_dummy_count' => 0,

            'different_balance' => $rows->filter(
                fn ($row) => abs($row['difference']) >= 0.00001
            )->count(),
        ];

        /*
         * Tidak ada transaksi dummy tersisa.
         */
        $dummyTransactions = collect();

        /*
         * Urutkan selisih terbesar terlebih dahulu.
         */
        $rows = $rows->sortByDesc(
            fn ($row) => abs($row['difference'])
        )->values();

        /*
         * Siapkan format object untuk Blade.
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
         * Detail seluruh transaksi masuk dan keluar per barang.
         * Hanya membaca data.
         */
        $details = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                $item = $items->firstWhere('id', $detail->item_id);

                $details->push((object) [
                    'transaction_date' => $transaction->transaction_date,
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number
                        ?? $transaction->reference_number
                        ?? null,
                    'type' => $transaction->type,
                    'item_code' => $item->code ?? '-',
                    'item_name' => $item->name ?? '-',
                    'item_unit' => $item->unit ?? '-',
                    'quantity' => $detail->quantity ?? 0,
                    'vendor_name' => $transaction->vendor_name ?? null,
                    'purchase_note_number' => $transaction->purchase_note_number ?? null,
                    'notes' => $transaction->notes
                        ?? $transaction->note
                        ?? '-',
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