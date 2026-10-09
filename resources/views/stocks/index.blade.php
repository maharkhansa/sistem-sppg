<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            min-height: 100vh;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .pagination {
            margin-bottom: 0;
        }

        .summary-card {
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-light">

    <div class="container-fluid container-xl my-4 my-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 p-md-4">

                <!-- Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1">
                            <i class="bi bi-boxes me-2 text-primary"></i>
                            Stok Barang
                        </h2>
                        <p class="text-muted small mb-0">
                            Informasi persediaan dan status stok barang.
                        </p>
                    </div>

                    <a href="{{ route('stock-transactions.index') }}"
                        class="btn btn-outline-primary">
                        <i class="bi bi-arrow-down-left-square me-1"></i>
                        Barang Masuk
                    </a>
                </div>

                <!-- Filter Supplier -->
                <form action="{{ route('stocks.index') }}" method="GET"
                    class="card border-0 bg-light mb-4">

                    <div class="card-body">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-6 col-lg-5">
                                <label for="supplier_id" class="form-label fw-semibold">
                                    <i class="bi bi-funnel me-1"></i>
                                    Filter Supplier
                                </label>

                                <select name="supplier_id" id="supplier_id"
                                    class="form-select">

                                    <option value="">Semua Supplier</option>

                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ (string) request('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Tampilkan
                                </button>

                                <a href="{{ route('stocks.index') }}"
                                    class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Reset
                                </a>
                            </div>

                        </div>
                    </div>
                </form>

                <!-- Informasi Filter -->
                @if (request()->filled('supplier_id'))
                    @php
                        $selectedSupplier = $suppliers->firstWhere(
                            'id',
                            (int) request('supplier_id')
                        );
                    @endphp

                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            Menampilkan stok supplier:
                            <strong>{{ $selectedSupplier?->name ?? '-' }}</strong>
                        </div>
                    </div>
                @endif

                <!-- Ringkasan Stok -->
                @php
                    $currentPageItems = $items->getCollection();

                    $lowStockCount = $currentPageItems->filter(function ($item) {
                        $quantity = $item->stock
                            ? (float) $item->stock->quantity
                            : 0;

                        return $quantity <= (float) $item->minimum_stock;
                    })->count();

                    $safeStockCount = $currentPageItems->count() - $lowStockCount;
                @endphp

                <div class="row g-3 mb-4">

                    <!-- Jumlah Jenis Barang -->
                    <div class="col-md-4">
                        <div class="card summary-card border-0 bg-primary-subtle h-100">
                            <div class="card-body">
                                <div class="text-primary mb-2">
                                    <i class="bi bi-box-seam fs-4"></i>
                                </div>

                                <div class="text-muted small">
                                    Jenis Barang di Halaman Ini
                                </div>

                                <div class="fs-4 fw-bold text-primary">
                                    {{ $items->count() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stok Minimum -->
                    <div class="col-md-4">
                        <div class="card summary-card border-0 bg-warning-subtle h-100">
                            <div class="card-body">
                                <div class="text-warning-emphasis mb-2">
                                    <i class="bi bi-exclamation-triangle fs-4"></i>
                                </div>

                                <div class="text-muted small">
                                    Barang Stok Minimum di Halaman Ini
                                </div>

                                <div class="fs-4 fw-bold text-warning-emphasis">
                                    {{ $lowStockCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stok Aman -->
                    <div class="col-md-4">
                        <div class="card summary-card border-0 bg-success-subtle h-100">
                            <div class="card-body">
                                <div class="text-success mb-2">
                                    <i class="bi bi-check-circle fs-4"></i>
                                </div>

                                <div class="text-muted small">
                                    Barang Stok Aman di Halaman Ini
                                </div>

                                <div class="fs-4 fw-bold text-success">
                                    {{ $safeStockCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Informasi Jumlah Data -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="text-muted small">
                        Menampilkan
                        <strong>{{ $items->firstItem() ?? 0 }}</strong>
                        sampai
                        <strong>{{ $items->lastItem() ?? 0 }}</strong>
                        dari
                        <strong>{{ $items->total() }}</strong>
                        data barang
                    </div>

                    <span class="badge bg-secondary-subtle text-secondary border">
                        Maksimal 20 data per halaman
                    </span>
                </div>

                <!-- Tabel Stok -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">
                                    No
                                </th>

                                <th style="min-width: 120px;">
                                    Kode Barang
                                </th>

                                <th style="min-width: 180px;">
                                    Nama Barang
                                </th>

                                <th style="min-width: 160px;">
                                    Supplier
                                </th>

                                <th style="min-width: 130px;">
                                    Kategori
                                </th>

                                <th class="text-center">
                                    Satuan
                                </th>

                                <th class="text-center">
                                    Stok
                                </th>

                                <th class="text-center">
                                    Min. Stok
                                </th>

                                <th class="text-center" style="min-width: 140px;">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $stockQuantity = $item->stock
                                        ? (float) $item->stock->quantity
                                        : 0;

                                    $minimumStock = (float) $item->minimum_stock;

                                    $isLowStock = $stockQuantity <= $minimumStock;
                                @endphp

                                <tr>
                                    <!-- Nomor Urut -->
                                    <td class="text-center text-muted fw-semibold">
                                        {{ $items->firstItem() + $loop->index }}
                                    </td>

                                    <!-- Kode Barang -->
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            {{ $item->code }}
                                        </span>
                                    </td>

                                    <!-- Nama Barang -->
                                    <td class="fw-medium text-dark">
                                        {{ $item->name }}
                                    </td>

                                    <!-- Supplier -->
                                    <td>
                                        @if ($item->supplier)
                                            <span class="badge bg-primary-subtle text-primary-emphasis px-2 py-1">
                                                <i class="bi bi-truck me-1"></i>
                                                {{ $item->supplier->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <!-- Kategori -->
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">
                                            <i class="bi bi-tag me-1"></i>
                                            {{ $item->category->name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Satuan -->
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            {{ $item->unit }}
                                        </span>
                                    </td>

                                    <!-- Jumlah Stok -->
                                    <td class="text-center fw-bold fs-6 {{ $isLowStock ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($stockQuantity, 2, ',', '.') }}
                                    </td>

                                    <!-- Minimum Stok -->
                                    <td class="text-center text-muted">
                                        {{ number_format($minimumStock, 2, ',', '.') }}
                                    </td>

                                    <!-- Status Stok -->
                                    <td class="text-center">
                                        @if ($isLowStock)
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill border border-warning-subtle">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                Stok Minimum
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                        @if (request()->filled('supplier_id'))
                                            Tidak ada barang aktif untuk supplier yang dipilih.
                                        @else
                                            Belum ada data barang.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

<!-- Pagination -->
@if ($items->hasPages())
    <div class="d-flex flex-column align-items-center mt-4">

        <!-- Informasi halaman dan jumlah data -->
        <div class="text-muted small text-center mb-4">
            Halaman <strong>{{ $items->currentPage() }}</strong>
            dari <strong>{{ $items->lastPage() }}</strong>

            <span class="mx-2">|</span>

            Showing <strong>{{ $items->firstItem() }}</strong>
            to <strong>{{ $items->lastItem() }}</strong>
            of <strong>{{ $items->total() }}</strong> results
        </div>

        <!-- Navigasi halaman -->
        <nav aria-label="Navigasi halaman stok">
            <ul class="pagination pagination-sm mb-0">

                <!-- Sebelumnya -->
                <li class="page-item {{ $items->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link"
                        href="{{ $items->previousPageUrl() ?? '#' }}"
                        aria-label="Previous">
                        &laquo;
                    </a>
                </li>

                @php
                    $current = $items->currentPage();
                    $last = $items->lastPage();

                    // Maksimal 6 nomor halaman
                    $start = max(1, min($current - 2, $last - 5));
                    $end = min($last, $start + 5);
                @endphp

                @if ($start > 1)
                    <li class="page-item">
                        <a class="page-link"
                            href="{{ $items->url(1) }}">1</a>
                    </li>

                    @if ($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    <li class="page-item {{ $page == $current ? 'active' : '' }}">
                        <a class="page-link"
                            href="{{ $items->url($page) }}">
                            {{ $page }}
                        </a>
                    </li>
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif

                    <li class="page-item">
                        <a class="page-link"
                            href="{{ $items->url($last) }}">{{ $last }}</a>
                    </li>
                @endif

                <!-- Berikutnya -->
                <li class="page-item {{ !$items->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link"
                        href="{{ $items->nextPageUrl() ?? '#' }}"
                        aria-label="Next">
                        &raquo;
                    </a>
                </li>

            </ul>
        </nav>

    </div>
@endif

                <!-- Keterangan -->
                <div class="text-muted small mt-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Status stok minimum ditentukan berdasarkan jumlah stok saat ini
                    dibandingkan dengan minimum stok barang.
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>