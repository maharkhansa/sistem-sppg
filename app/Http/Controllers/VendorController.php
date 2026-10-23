<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseResponsible;

class VendorController extends Controller
{
    public function index(Request $request)
{
    $query = Vendor::with([
            'purchaseResponsibles:id,name',
        ])
        ->withCount('items');

    // Fitur pencarian
    if ($request->filled('search')) {
        $search = trim($request->input('search'));

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        });
    }

    // Filter status vendor
    if ($request->input('status') === 'aktif') {
        $query->where('is_active', true);
    } elseif ($request->input('status') === 'nonaktif') {
        $query->where('is_active', false);
    }

    // Urutkan vendor berdasarkan abjad A-Z
    $vendors = $query
        ->orderBy('name', 'asc')
        ->get();

    return view('vendors.index', compact('vendors'));
}

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:vendors,name',
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
        ]);

        Vendor::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load([
            'items:id,name,code,unit',
            'purchaseResponsibles',
        ]);

        $items = Item::orderBy('name')
            ->get(['id', 'name', 'code', 'unit']);

        // Tampilkan PJ aktif maupun tidak aktif,
        // agar relasi PJ lama tetap terlihat saat Vendor diedit.
        $purchaseResponsibles = PurchaseResponsible::orderBy('name')
            ->get();

        return view('vendors.edit', compact(
            'vendor',
            'items',
            'purchaseResponsibles'
        ));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('vendors', 'name')->ignore($vendor->id),
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
            'items' => [
                'nullable',
                'array',
            ],
            'items.*' => [
                'integer',
                'distinct',
                'exists:items,id',
            ],
            'purchase_responsibles' => [
                'nullable',
                'array',
            ],
            'purchase_responsibles.*' => [
                'integer',
                'distinct',
                'exists:purchase_responsibles,id',
            ],
        ]);

        DB::transaction(function () use ($vendor, $validated) {
            $vendor->update([
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
            ]);

            // Pertahankan penyimpanan relasi Vendor–Barang.
            $vendor->items()->sync($validated['items'] ?? []);

            // Simpan relasi Vendor–PJ.
            $vendor->purchaseResponsibles()->sync(
                $validated['purchase_responsibles'] ?? []
            );
        });

        return redirect()
            ->route('vendors.index', $vendor)
            ->with('success', 'Vendor dan daftar barang berhasil diperbarui.');
    }

    public function toggleStatus(Vendor $vendor)
    {
        $vendor->update([
            'is_active' => !$vendor->is_active,
        ]);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Status Vendor berhasil diperbarui.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load([
            'items:id,name,code,unit',
            'purchaseResponsibles',
        ]);

        return view('vendors.show', compact('vendor'));
    }
}