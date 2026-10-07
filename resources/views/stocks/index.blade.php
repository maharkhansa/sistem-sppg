<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang</title>
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
                        <i class="bi bi-boxes me-2 text-primary"></i>Stok Barang
                    </h2>
                    <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-down-left-square me-1"></i> Barang Masuk
                    </a>
                </div>

                <!-- Table Data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 50px;">No</th>
                                <th scope="col" style="width: 130px;">Kode Barang</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col">Kategori</th>
                                <th scope="col" class="text-center">Satuan</th>
                                <th scope="col" class="text-center">Stok</th>
                                <th scope="col" class="text-center">Min. Stok</th>
                                <th scope="col" class="text-center" style="width: 140px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $stockQuantity = $item->stock
                                        ? $item->stock->quantity
                                        : 0;
                                @endphp

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

                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            {{ $item->unit }}
                                        </span>
                                    </td>

                                    <td class="text-center fw-bold fs-6 {{ $stockQuantity <= $item->minimum_stock ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($stockQuantity) }}
                                    </td>

                                    <td class="text-center text-muted">
                                        {{ number_format($item->minimum_stock) }}
                                    </td>

                                    <td class="text-center">
                                        @if ($stockQuantity <= $item->minimum_stock)
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill border border-warning-subtle">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Minimum
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Aman
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>