<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Supplier</title>
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
                        <i class="bi bi-truck me-2 text-primary"></i>Data Supplier
                    </h2>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Supplier
                    </a>
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
                                <th scope="col">Nama Supplier</th>
                                <th scope="col">Kontak</th>
                                <th scope="col">No. Telepon</th>
                                <th scope="col" class="text-center" style="width: 130px;">Status</th>
                                <th scope="col" class="text-center" style="width: 200px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>

                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            {{ $supplier->code }}
                                        </span>
                                    </td>

                                    <td class="fw-medium text-dark">{{ $supplier->name }}</td>

                                    <td>
                                        @if($supplier->contact_person)
                                            <i class="bi bi-person me-1 text-secondary"></i>{{ $supplier->contact_person }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($supplier->phone)
                                            <i class="bi bi-telephone me-1 text-secondary"></i>{{ $supplier->phone }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($supplier->status)
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
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>

                                            <!-- Toggle Status Button -->
                                            <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                @if($supplier->status)
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menonaktifkan supplier ini?')">
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
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data supplier.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>