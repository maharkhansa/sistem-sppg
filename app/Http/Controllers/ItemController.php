<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Imports\ItemImport;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['category', 'supplier'])
            ->latest()
            ->get();

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        return view('items.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:items,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'unit' => [
                'required',
                'string',
                'max:30',
            ],
            'minimum_stock' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        Item::create([
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
            'status' => true,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        return view('items.edit', compact(
            'item',
            'categories',
            'suppliers'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('items', 'code')->ignore($item->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'unit' => [
                'required',
                'string',
                'max:30',
            ],
            'minimum_stock' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        $item->update([
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function toggleStatus(Item $item)
    {
        $item->update([
            'status' => !$item->status,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Status barang berhasil diperbarui.');
    }

    public function import(Request $request)
    {
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:2048',
    ]);

    Excel::import(new ItemImport, $request->file('file'));

    return redirect()->back()->with('success', 'Data barang berhasil diimport!');
    }
}