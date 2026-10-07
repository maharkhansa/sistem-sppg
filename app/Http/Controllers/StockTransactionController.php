<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller
{
    public function index()
    {
        $transactions = StockTransaction::with('supplier')
            ->where('type', 'IN')
            ->latest()
            ->get();

        return view('stock_transactions.index', compact('transactions'));
    }

    public function outIndex()
    {
        $transactions = StockTransaction::with([
            'kitchen',
            'purchaseOrder',
            'details.item',
            'invoice',
            'notaKeluars',
        ])
            ->where('type', 'OUT')
            ->latest()
            ->get();

        return view(
            'stock_transactions.out_index',
            compact('transactions')
        );
    }

    public function create()
    {
        $suppliers = \App\Models\Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        $items = Item::with('category')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('stock_transactions.create', compact(
            'suppliers',
            'items'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {

            $transaction = StockTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_date' => $data['transaction_date'],
                'type' => 'IN',
                'supplier_id' => $data['supplier_id'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($data['items'] as $detail) {

                $item = Item::findOrFail($detail['item_id']);

                $quantity = $detail['quantity'];
                $unitPrice = $detail['unit_price'];

                $transaction->details()->create([
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $quantity * $unitPrice,
                ]);

                $stock = \App\Models\Stock::firstOrCreate(
                [
                        'item_id' => $item->id,
                ],
                [
                         'quantity' => 0,
                ]);

$stock->increment('quantity', $quantity);
            }
        });

        return redirect()
            ->route('stock-transactions.index')
            ->with('success', 'Barang masuk berhasil disimpan.');
    }

    private function generateTransactionNumber()
    {
        $date = now()->format('Ymd');

        $lastTransaction = StockTransaction::where('type', 'IN')
            ->whereDate('transaction_date', now()->toDateString())
            ->latest('id')
            ->first();

        $number = $lastTransaction
            ? ((int) substr($lastTransaction->transaction_number, -3)) + 1
            : 1;

        return 'IN-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}