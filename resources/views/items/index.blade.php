<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                
                <!-- Header & Action Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 font-weight-bold text-dark m-0">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Data Barang
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Tombol Trigger Modal Import Excel -->
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                        </button>
                        <a href="{{ route('items.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                        </a>
                    </div>
                </div>

                <!-- Alert Success -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Alert Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Terjadi kesalahan:</strong>
                        </div>
                        <ul class="mb-0 ps-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Table Data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 50px;">No</th>
                                <th scope="col" style="width: 120px;">Kode</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Supplier</th>
                                <th scope="col" class="text-center">Satuan</th>
                                <th scope="col" class="text-center">Min. Stok</th>
                                <th scope="col" class="text-center" style="width: 120px;">Status</th>
                                <th scope="col" class="text-center" style="width: 200px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>

                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            {{ $item->code }}
                                        </span>
                                    </td>

                                    <td class="fw-medium text-dark">{{ $item->name }}</td>

                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">
                                            <i class="bi bi-tag me-1"></i>{{ $item->category->name }}
                                        </span>
                                    </td>

                                    <td>
                                        <small class="text-secondary">
                                            <i class="bi bi-truck me-1"></i>{{ $item->supplier->name }}
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            {{ $item->unit }}
                                        </span>
                                    </td>

                                    <td class="text-center fw-semibold text-dark">
                                        {{ number_format($item->minimum_stock) }}
                                    </td>

                                    <td class="text-center">
                                        @if($item->status)
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle me-1"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Edit Button -->
                                            <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>

                                            <!-- Toggle Status Button -->
                                            <form action="{{ route('items.toggle-status', $item) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                @if($item->status)
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menonaktifkan barang ini?')">
                                                        <i class="bi bi-power me-1"></i> Nonaktifkan
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-power me-1"></i> Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Barang via Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xlsx / .xls / .csv)</label>
                            <input class="form-control" type="file" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Pastikan format file sesuai dengan template.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>