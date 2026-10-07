<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseTypeController extends Controller
{
    public function index()
    {
        $expenseTypes = ExpenseType::latest()->get();

        return view('expense-types.index', compact('expenseTypes'));
    }

    public function create()
    {
        return view('expense-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        ExpenseType::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'status' => true,
        ]);

        return redirect()->route('expense-types.index')
            ->with('success', 'Jenis biaya berhasil ditambahkan.');
    }

    public function edit(ExpenseType $expenseType)
    {
        return view('expense-types.edit', compact('expenseType'));
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('expense_types', 'name')
                    ->ignore($expenseType->id),
            ],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $expenseType->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
        ]);

        return redirect()->route('expense-types.index')
            ->with('success', 'Jenis biaya berhasil diperbarui.');
    }

    public function toggleStatus(ExpenseType $expenseType)
    {
        $expenseType->update([
            'status' => !$expenseType->status,
        ]);

        return redirect()->route('expense-types.index')
            ->with('success', 'Status jenis biaya berhasil diperbarui.');
    }
}