<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            'bank_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'bank_account_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'bank_account_holder' => [
                'nullable',
                'string',
                'max:150',
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('vendor-notas', 'public');
        }

        Vendor::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,

            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,

            'receipt_path' => $receiptPath,
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
            'bank_name' => [
                'nullable', 
                'string', 
                'max:100'
            ],
            'bank_account_number' => [
                'nullable', 
                'string', 
                'max:50'
            ],
            'bank_account_holder' => [
                'nullable', 
                'string', 
                'max:150'
            ],
            'receipt' => [
                'nullable', 
                'file', 
                'mimes:pdf,jpg,jpeg,png', 
                'max:5120'
            ],
            'remove_receipt' => [
                'nullable', 
                'boolean
            '],
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

    public function destroy(Vendor $vendor)
    {
        try {
            DB::transaction(function () use ($vendor) {

                // Hapus relasi vendor dengan barang,
                // bukan menghapus data barangnya.
                $vendor->items()->detach();

                // Hapus relasi vendor dengan PJ,
                // bukan menghapus data PJ-nya.
                $vendor->purchaseResponsibles()->detach();

                // Hapus hanya vendor yang dipilih.
                $vendor->delete();
            });

            return redirect()
                ->route('vendors.index')
                ->with('success', 'Vendor berhasil dihapus.');

        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('vendors.index')
                ->with(
                    'error',
                    'Vendor tidak dapat dihapus karena masih digunakan pada transaksi atau data lain.'
                );
        }
    }
}