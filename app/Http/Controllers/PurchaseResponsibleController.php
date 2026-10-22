<?php

namespace App\Http\Controllers;

use App\Models\PurchaseResponsible;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseResponsibleController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseResponsible::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $responsibles = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(
            'purchase-responsibles.index',
            compact('responsibles')
        );
    }

    public function create()
    {
        return view('purchase-responsibles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama penanggung jawab wajib diisi.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['is_active'] = true;

        PurchaseResponsible::create($validated);

        return redirect()
            ->route('purchase-responsibles.index')
            ->with('success', 'Penanggung jawab berhasil ditambahkan.');
    }

    public function edit(PurchaseResponsible $purchaseResponsible)
    {
        return view(
            'purchase-responsibles.edit',
            compact('purchaseResponsible')
        );
    }

    public function update(
        Request $request,
        PurchaseResponsible $purchaseResponsible
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama penanggung jawab wajib diisi.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
        ]);

        $validated['name'] = trim($validated['name']);

        $purchaseResponsible->update($validated);

        return redirect()
            ->route('purchase-responsibles.index')
            ->with('success', 'Data penanggung jawab berhasil diperbarui.');
    }

    public function toggleStatus(
        PurchaseResponsible $purchaseResponsible
    ) {
        $purchaseResponsible->update([
            'is_active' => ! $purchaseResponsible->is_active,
        ]);

        $message = $purchaseResponsible->is_active
            ? 'Penanggung jawab berhasil diaktifkan.'
            : 'Penanggung jawab berhasil dinonaktifkan.';

        return redirect()
            ->route('purchase-responsibles.index')
            ->with('success', $message);
    }
}