<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Supplier</title>

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
                            <a href="{{ route('suppliers.index') }}"
                               class="btn btn-outline-secondary btn-sm me-3"
                               title="Kembali">
                                <i class="bi bi-arrow-left"></i>
                            </a>

                            <h2 class="h4 font-weight-bold text-dark m-0">
                                <i class="bi bi-truck me-2 text-primary"></i>
                                Tambah Supplier
                            </h2>
                        </div>

                        <!-- Global Alert Errors -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4"
                                 role="alert">

                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Terjadi kesalahan input:</strong>
                                </div>

                                <ul class="mb-0 ps-4">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="alert"
                                        aria-label="Close">
                                </button>
                            </div>
                        @endif

                        <!-- Form -->
                        <form action="{{ route('suppliers.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">

                                <!-- Kode Supplier -->
                                <div class="col-md-4">
                                    <label for="code"
                                           class="form-label fw-semibold text-dark">
                                        Kode Supplier
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control font-monospace @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="Contoh: SUP-001"
                                        required
                                        autofocus
                                    >

                                    @error('code')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Nama Supplier -->
                                <div class="col-md-8">
                                    <label for="name"
                                           class="form-label fw-semibold text-dark">
                                        Nama Supplier
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Masukkan nama PT / Toko / Perusahaan..."
                                        required
                                    >

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Nama Kontak -->
                                <div class="col-md-6">
                                    <label for="contact_person"
                                           class="form-label fw-semibold text-dark">
                                        Nama Kontak (Contact Person)
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">
                                            <i class="bi bi-person"></i>
                                        </span>

                                        <input
                                            type="text"
                                            class="form-control @error('contact_person') is-invalid @enderror"
                                            id="contact_person"
                                            name="contact_person"
                                            value="{{ old('contact_person') }}"
                                            placeholder="Nama penanggung jawab..."
                                        >
                                    </div>

                                    @error('contact_person')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- No. Telepon -->
                                <div class="col-md-6">
                                    <label for="phone"
                                           class="form-label fw-semibold text-dark">
                                        No. Telepon
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">
                                            <i class="bi bi-telephone"></i>
                                        </span>

                                        <input
                                            type="text"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            id="phone"
                                            name="phone"
                                            value="{{ old('phone') }}"
                                            placeholder="Contoh: 08123456789"
                                        >
                                    </div>

                                    @error('phone')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Alamat -->
                                <div class="col-12">
                                    <label for="address"
                                           class="form-label fw-semibold text-dark">
                                        Alamat
                                    </label>

                                    <textarea
                                        class="form-control @error('address') is-invalid @enderror"
                                        id="address"
                                        name="address"
                                        rows="3"
                                        placeholder="Masukkan alamat lengkap supplier..."
                                    >{{ old('address') }}</textarea>

                                    @error('address')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Template Nota -->
                                <div class="col-12">
                                    <label for="nota_template"
                                           class="form-label fw-semibold text-dark">
                                        Template Nota
                                    </label>

                                    <select
                                        class="form-select @error('nota_template') is-invalid @enderror"
                                        id="nota_template"
                                        name="nota_template"
                                    >
                                        <option value="">
                                            -- Pilih Template Nota --
                                        </option>

                                        <option value="gemilang"
                                            {{ old('nota_template') == 'gemilang' ? 'selected' : '' }}>
                                            PT Gemilang Mart Nusantara
                                        </option>

                                        <option value="zenzi"
                                            {{ old('nota_template') == 'zenzi' ? 'selected' : '' }}>
                                            Zenzi
                                        </option>

                                        <option value="sumber_rejeki"
                                            {{ old('nota_template') == 'sumber_rejeki' ? 'selected' : '' }}>
                                            KOP Sumber Rejeki
                                        </option>

                                        <option value="top_fast"
                                            {{ old('nota_template') == 'top_fast' ? 'selected' : '' }}>
                                            TOP FAST
                                        </option>
                                    </select>

                                    <div class="form-text">
                                        Template ini menentukan tampilan kop Nota Keluar
                                        berdasarkan supplier.
                                    </div>

                                    @error('nota_template')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">

                                <a href="{{ route('suppliers.index') }}"
                                   class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Batal
                                </a>

                                <button type="submit"
                                        class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>
                                    Simpan
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