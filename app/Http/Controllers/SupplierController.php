<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:suppliers,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:100',
            ],
            'nota_template' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        Supplier::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'status' => true,
            'nota_template' => $validated['nota_template'] ?? null,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('suppliers', 'code')->ignore($supplier->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:100',
            ],
            'nota_template' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $supplier->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'nota_template' => $validated['nota_template'] ?? null,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update([
            'status' => !$supplier->status,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Status supplier berhasil diperbarui.');
    }
}