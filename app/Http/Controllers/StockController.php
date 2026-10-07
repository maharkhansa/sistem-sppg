<?php

namespace App\Http\Controllers;

use App\Models\Item;

class StockController extends Controller
{
    public function index()
    {
        $items = Item::with(['category', 'stock'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('stocks.index', compact('items'));
    }
}