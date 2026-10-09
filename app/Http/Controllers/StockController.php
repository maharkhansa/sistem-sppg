<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
        ]);

        $query = Item::with(['category', 'stock', 'supplier'])
            ->where('status', true);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $items = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('stocks.index', compact('items', 'suppliers'));
    }
}