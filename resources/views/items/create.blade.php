<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
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
                            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm me-3" title="Kembali">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <h2 class="h4 font-weight-bold text-dark m-0">
                                <i class="bi bi-box-seam me-2 text-primary"></i>Tambah Barang
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

                        <!-- Form -->
                        <form action="{{ route('items.store') }}" method="POST">
                            @csrf

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
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}
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
                                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
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
                                        value="{{ old('code') }}" 
                                        placeholder="Contoh: BRG-001"
                                        required
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
                                        value="{{ old('name') }}" 
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
                                    <input 
                                        type="text" 
                                        class="form-control @error('unit') is-invalid @enderror" 
                                        id="unit" 
                                        name="unit" 
                                        value="{{ old('unit') }}" 
                                        placeholder="Contoh: Kg, Liter, Pcs"
                                        required
                                    >
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
                                        value="{{ old('minimum_stock', 0) }}" 
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
                                <a href="{{ route('items.index') }}" class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Simpan
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