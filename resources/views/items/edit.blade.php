@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 p-lg-4">

                    <div class="d-flex align-items-center mb-4">
                        <a href="{{ route('items.index', request()->only(['search', 'category_id', 'supplier_id', 'page'])) }}"
                            class="btn btn-outline-secondary btn-sm me-3"
                            title="Kembali">
                            <i class="bi bi-arrow-left"></i>
                        </a>

                        <div>
                            <h4 class="fw-bold mb-1">
                                <i class="bi bi-pencil-square text-warning me-2"></i>Edit Barang
                            </h4>
                            <div class="text-muted small">Perbarui informasi master barang.</div>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan input:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                        </div>
                    @endif

                    <form action="{{ route('items.update', array_merge(
                        ['item' => $item->id],
                        request()->only(['search', 'category_id', 'supplier_id', 'page'])
                    )) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-semibold">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select id="category_id"
                                    name="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories->reject(function ($category) {
                                        return mb_strtolower(trim($category->name)) === 'insentif';
                                    }) as $category)
                                        <option value="{{ $category->id }}"
                                            @selected((string) request('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label fw-semibold">
                                    Supplier <span class="text-danger">*</span>
                                </label>
                                <select id="supplier_id"
                                    name="supplier_id"
                                    class="form-select @error('supplier_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers->whereIn('id', [1, 2, 3, 4]) as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            @selected((string) request('supplier_id') === (string) $supplier->id)>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label fw-semibold">
                                    Kode Barang <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    id="code"
                                    name="code"
                                    value="{{ old('code', $item->code) }}"
                                    class="form-control font-monospace @error('code') is-invalid @enderror"
                                    placeholder="Contoh: BRG-001"
                                    required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold">
                                    Nama Barang <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $item->name) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Masukkan nama barang"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="unit" class="form-label fw-semibold">
                                    Satuan <span class="text-danger">*</span>
                                </label>
                                @php
                                    $units = [
                                        'Kg', 'Gram', 'Liter', 'Karton', 'Pcs', 'Bks',
                                        'Dus', 'Karung', 'Pack', 'Botol', 'Jerigen',
                                        'Pouch', 'Sak', 'Lembar', 'Galon', 'Rim', 'Tabung'
                                    ];
                                    $currentUnit = old('unit', $item->unit);
                                @endphp

                                <select id="unit"
                                    name="unit"
                                    class="form-select @error('unit') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Satuan --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit }}" @selected($currentUnit === $unit)>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                    @if($currentUnit && !in_array($currentUnit, $units))
                                        <option value="{{ $currentUnit }}" selected>{{ $currentUnit }}</option>
                                    @endif
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="minimum_stock" class="form-label fw-semibold">
                                    Minimum Stok <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    id="minimum_stock"
                                    name="minimum_stock"
                                    value="{{ old('minimum_stock', $item->minimum_stock) }}"
                                    min="0"
                                    step="0.01"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end flex-wrap gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('items.index', request()->only(['search', 'category_id', 'supplier_id', 'page'])) }}"
                                class="btn btn-light border">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-warning text-dark px-4">
                                <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection