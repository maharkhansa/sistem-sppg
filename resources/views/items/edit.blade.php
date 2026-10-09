<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex align-items-center mb-4">
                            <a href="{{ route('items.index', request()->only([
                                'search',
                                'category_id',
                                'supplier_id',
                                'page'
                            ])) }}" class="btn btn-outline-secondary btn-sm me-3" title="Kembali">
                                <i class="bi bi-arrow-left"></i>
                            </a>

                            <h2 class="h4 font-weight-bold text-dark m-0">
                                <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Barang
                            </h2>
                        </div>

                        <!-- Global Alert Errors -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Terjadi kesalahan input:</strong>
                                </div>

                                <ul class="mb-0 ps-4">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Form Edit -->
                        <form action="{{ route('items.update', array_merge(
                            ['item' => $item->id],
                            request()->only([
                                'search',
                                'category_id',
                                'supplier_id',
                                'page'
                            ])
                        )) }}" method="POST">

                            @csrf
                            @method('PUT')

                            <div class="row g-3">

                                <!-- Kategori -->
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label fw-semibold text-dark">
                                        Kategori <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id"
                                        name="category_id"
                                        required
                                    >
                                        <option value="">-- Pilih Kategori --</option>

                                        @foreach($categories as $category)
                                            <option
                                                value="{{ $category->id }}"
                                                {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}
                                            >
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Supplier -->
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label fw-semibold text-dark">
                                        Supplier <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select @error('supplier_id') is-invalid @enderror"
                                        id="supplier_id"
                                        name="supplier_id"
                                        required
                                    >
                                        <option value="">-- Pilih Supplier --</option>

                                        @foreach($suppliers as $supplier)
                                            <option
                                                value="{{ $supplier->id }}"
                                                {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}
                                            >
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Kode Barang -->
                                <div class="col-md-4">
                                    <label for="code" class="form-label fw-semibold text-dark">
                                        Kode Barang <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control font-monospace @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code', $item->code) }}"
                                        placeholder="Contoh: BRG-001"
                                        required
                                        autofocus
                                    >

                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nama Barang -->
                                <div class="col-md-8">
                                    <label for="name" class="form-label fw-semibold text-dark">
                                        Nama Barang <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $item->name) }}"
                                        placeholder="Masukkan nama barang..."
                                        required
                                    >

                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Satuan -->
                                <div class="col-md-6">
                                    <label for="unit" class="form-label fw-semibold text-dark">
                                        Satuan <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select @error('unit') is-invalid @enderror"
                                        id="unit"
                                        name="unit"
                                        required
                                    >
                                        <option value="">-- Pilih Satuan --</option>

                                        @foreach([
                                            'Kg',
                                            'Gram',
                                            'Liter',
                                            'Karton',
                                            'Pcs',
                                            'Bks',
                                            'Dus',
                                            'Karung',
                                            'Pack',
                                            'Botol',
                                            'Jerigen',
                                            'Pouch',
                                            'Sak',
                                            'Lembar',
                                            'Galon',
                                            'Rim',
                                            'Tabung',
                                        ] as $satuan)
                                            <option
                                                value="{{ $satuan }}"
                                                {{ old('unit', $item->unit) == $satuan ? 'selected' : '' }}
                                            >
                                                {{ $satuan }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Minimum Stok -->
                                <div class="col-md-6">
                                    <label for="minimum_stock" class="form-label fw-semibold text-dark">
                                        Minimum Stok <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control @error('minimum_stock') is-invalid @enderror"
                                        id="minimum_stock"
                                        name="minimum_stock"
                                        value="{{ old('minimum_stock', $item->minimum_stock) }}"
                                        min="0"
                                        step="0.01"
                                        required
                                    >

                                    @error('minimum_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">

                                <a href="{{ route('items.index', request()->only([
                                    'search',
                                    'category_id',
                                    'supplier_id',
                                    'page'
                                ])) }}" class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>

                                <button type="submit" class="btn btn-warning px-4 text-white">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                                </button>

                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>