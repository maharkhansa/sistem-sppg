<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name',
            ],
        ]);

        Category::create([
            'name' => $validated['name'],
            'status' => true,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update([
            'status' => !$category->status,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Status kategori berhasil diperbarui.');
    }
}