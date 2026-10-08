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

    <style>
        /* =========================================
           CLEAN PAGINATION
           ========================================= */

        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }

        .pagination .page-item {
            margin: 0;
        }

        .pagination .page-link {
            border: 1px solid #dee2e6;
            border-radius: 6px !important;

            width: 30px;
            height: 30px;

            padding: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
            font-weight: 500;

            color: #495057;
            background-color: #fff;

            line-height: 1;

            transition: all 0.15s ease;
        }

        .pagination .page-link:hover {
            background-color: #f1f3f5;
            color: #0d6efd;
            border-color: #ced4da;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #f8f9fa;
            color: #adb5bd;
            border-color: #e9ecef;
        }

        /* Panah kiri dan kanan */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 15px;
            font-weight: 400;
        }
    </style>

</head>

<body class="bg-light">

    <div class="container my-5">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <!-- =========================================
                     HEADER
                     ========================================= -->

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h2 class="h4 fw-bold text-dark m-0">

                        <i class="bi bi-box-seam me-2 text-primary"></i>

                        Data Barang

                    </h2>

                    <div class="d-flex gap-2">

                        <!-- Import Excel -->
                        <button type="button"
                            class="btn btn-outline-success"
                            data-bs-toggle="modal"
                            data-bs-target="#importExcelModal">

                            <i class="bi bi-file-earmark-excel me-1"></i>

                            Import Excel

                        </button>

                        <!-- Tambah Barang -->
                        <a href="{{ route('items.create') }}"
                            class="btn btn-primary">

                            <i class="bi bi-plus-lg me-1"></i>

                            Tambah Barang

                        </a>

                    </div>

                </div>


                <!-- =========================================
                     ALERT SUCCESS
                     ========================================= -->

                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show"
                        role="alert">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        {{ session('success') }}

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close">

                        </button>

                    </div>

                @endif


                <!-- =========================================
                     ALERT ERRORS
                     ========================================= -->

                @if($errors->any())

                    <div class="alert alert-danger alert-dismissible fade show"
                        role="alert">

                        <div class="d-flex align-items-center mb-1">

                            <i class="bi bi-exclamation-triangle-fill me-2"></i>

                            <strong>Terjadi kesalahan:</strong>

                        </div>

                        <ul class="mb-0 ps-4">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close">

                        </button>

                    </div>

                @endif


                <!-- =========================================
                     SEARCH & FILTER
                     ========================================= -->

                <div class="card border mb-4">

                    <div class="card-body">

                        <form action="{{ route('items.index') }}"
                            method="GET">

                            <div class="row g-3 align-items-end">

                                <!-- Search -->
                                <div class="col-md-5">

                                    <label for="search"
                                        class="form-label fw-semibold">

                                        <i class="bi bi-search me-1"></i>

                                        Cari Barang

                                    </label>

                                    <input type="text"
                                        name="search"
                                        id="search"
                                        class="form-control"
                                        placeholder="Cari kode atau nama barang..."
                                        value="{{ request('search') }}">

                                </div>


                                <!-- Kategori -->
                                <div class="col-md-3">

                                    <label for="category_id"
                                        class="form-label fw-semibold">

                                        <i class="bi bi-tags me-1"></i>

                                        Kategori

                                    </label>

                                    <select name="category_id"
                                        id="category_id"
                                        class="form-select">

                                        <option value="">
                                            Semua Kategori
                                        </option>

                                        @foreach($categories as $category)

                                            <option value="{{ $category->id }}"
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>

                                                {{ $category->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <!-- Supplier -->
                                <div class="col-md-3">

                                    <label for="supplier_id"
                                        class="form-label fw-semibold">

                                        <i class="bi bi-truck me-1"></i>

                                        Supplier

                                    </label>

                                    <select name="supplier_id"
                                        id="supplier_id"
                                        class="form-select">

                                        <option value="">
                                            Semua Supplier
                                        </option>

                                        @foreach($suppliers as $supplier)

                                            <option value="{{ $supplier->id }}"
                                                {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>

                                                {{ $supplier->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <!-- Tombol Filter -->
                                <div class="col-md-1 d-grid">

                                    <button type="submit"
                                        class="btn btn-primary"
                                        title="Filter">

                                        <i class="bi bi-funnel-fill"></i>

                                    </button>

                                </div>

                            </div>


                            <!-- Reset Filter -->
                            @if(
                                request()->filled('search') ||
                                request()->filled('category_id') ||
                                request()->filled('supplier_id')
                            )

                                <div class="mt-3">

                                    <a href="{{ route('items.index') }}"
                                        class="btn btn-sm btn-outline-secondary">

                                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                                        Reset Filter

                                    </a>

                                </div>

                            @endif

                        </form>

                    </div>

                </div>


                <!-- =========================================
                     INFO HASIL
                     ========================================= -->

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div class="text-muted small">

                        Menampilkan

                        <strong>
                            {{ $items->firstItem() ?? 0 }}
                        </strong>

                        -

                        <strong>
                            {{ $items->lastItem() ?? 0 }}
                        </strong>

                        dari

                        <strong>
                            {{ $items->total() }}
                        </strong>

                        barang

                    </div>


                    @if(
                        request()->filled('search') ||
                        request()->filled('category_id') ||
                        request()->filled('supplier_id')
                    )

                        <span class="badge bg-primary-subtle text-primary-emphasis">

                            <i class="bi bi-funnel-fill me-1"></i>

                            Filter aktif

                        </span>

                    @endif

                </div>


                <!-- =========================================
                     TABLE DATA
                     ========================================= -->

                <div class="table-responsive">

                    <table class="table table-hover align-middle border">

                        <thead class="table-light">

                            <tr>

                                <th scope="col"
                                    class="text-center"
                                    style="width: 50px;">

                                    No

                                </th>

                                <th scope="col"
                                    style="width: 120px;">

                                    Kode

                                </th>

                                <th scope="col">

                                    Nama Barang

                                </th>

                                <th scope="col">

                                    Kategori

                                </th>

                                <th scope="col">

                                    Supplier

                                </th>

                                <th scope="col"
                                    class="text-center">

                                    Satuan

                                </th>

                                <th scope="col"
                                    class="text-center">

                                    Min. Stok

                                </th>

                                <th scope="col"
                                    class="text-center"
                                    style="width: 120px;">

                                    Status

                                </th>

                                <th scope="col"
                                    class="text-center"
                                    style="width: 200px;">

                                    Aksi

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($items as $item)

                                <tr>

                                    <!-- Nomor -->
                                    <td class="text-center text-muted fw-semibold">

                                        {{ $items->firstItem() + $loop->index }}

                                    </td>


                                    <!-- Kode -->
                                    <td>

                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">

                                            {{ $item->code }}

                                        </span>

                                    </td>


                                    <!-- Nama -->
                                    <td class="fw-medium text-dark">

                                        {{ $item->name }}

                                    </td>


                                    <!-- Kategori -->
                                    <td>

                                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">

                                            <i class="bi bi-tag me-1"></i>

                                            {{ $item->category->name }}

                                        </span>

                                    </td>


                                    <!-- Supplier -->
                                    <td>

                                        <small class="text-secondary">

                                            <i class="bi bi-truck me-1"></i>

                                            {{ $item->supplier->name }}

                                        </small>

                                    </td>


                                    <!-- Satuan -->
                                    <td class="text-center">

                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">

                                            {{ $item->unit }}

                                        </span>

                                    </td>


                                    <!-- Minimum Stock -->
                                    <td class="text-center fw-semibold text-dark">

                                        {{ number_format($item->minimum_stock) }}

                                    </td>


                                    <!-- Status -->
                                    <td class="text-center">

                                        @if($item->status)

                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">

                                                <i class="bi bi-check-circle me-1"></i>

                                                Aktif

                                            </span>

                                        @else

                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">

                                                <i class="bi bi-x-circle me-1"></i>

                                                Nonaktif

                                            </span>

                                        @endif

                                    </td>


                                    <!-- Aksi -->
                                    <td class="text-center">

                                        <div class="d-flex justify-content-center gap-2">

                                            <!-- Edit -->
                                            <a href="{{ route('items.edit', $item) }}"
                                                class="btn btn-sm btn-outline-warning">

                                                <i class="bi bi-pencil me-1"></i>

                                                Edit

                                            </a>


                                            <!-- Toggle Status -->
                                            <form action="{{ route('items.toggle-status', $item) }}"
                                                method="POST"
                                                class="d-inline">

                                                @csrf

                                                @method('PATCH')

                                                @if($item->status)

                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Yakin ingin menonaktifkan barang ini?')">

                                                        <i class="bi bi-power me-1"></i>

                                                        Nonaktifkan

                                                    </button>

                                                @else

                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-success">

                                                        <i class="bi bi-power me-1"></i>

                                                        Aktifkan

                                                    </button>

                                                @endif

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="9"
                                        class="text-center py-5 text-muted">

                                        <i class="bi bi-search fs-2 d-block mb-2"></i>

                                        @if(
                                            request()->filled('search') ||
                                            request()->filled('category_id') ||
                                            request()->filled('supplier_id')
                                        )

                                            Data barang tidak ditemukan
                                            sesuai filter.

                                        @else

                                            Belum ada data barang.

                                        @endif

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                <!-- =========================================
                     PAGINATION CLEAN
                     ========================================= -->

                @if($items->hasPages())

                    <div class="d-flex justify-content-center mt-4">

                        <nav aria-label="Pagination">

                            <ul class="pagination mb-0">

                                <!-- Previous -->

                                @if($items->onFirstPage())

                                    <li class="page-item disabled">

                                        <span class="page-link">
                                            &lsaquo;
                                        </span>

                                    </li>

                                @else

                                    <li class="page-item">

                                        <a class="page-link"
                                            href="{{ $items->previousPageUrl() }}"
                                            aria-label="Previous">

                                            &lsaquo;

                                        </a>

                                    </li>

                                @endif


                                <!-- Nomor Halaman -->

                                @foreach(
                                    $items->getUrlRange(
                                        1,
                                        $items->lastPage()
                                    ) as $page => $url
                                )

                                    @if($page == $items->currentPage())

                                        <li class="page-item active">

                                            <span class="page-link">

                                                {{ $page }}

                                            </span>

                                        </li>

                                    @else

                                        <li class="page-item">

                                            <a class="page-link"
                                                href="{{ $url }}">

                                                {{ $page }}

                                            </a>

                                        </li>

                                    @endif

                                @endforeach


                                <!-- Next -->

                                @if($items->hasMorePages())

                                    <li class="page-item">

                                        <a class="page-link"
                                            href="{{ $items->nextPageUrl() }}"
                                            aria-label="Next">

                                            &rsaquo;

                                        </a>

                                    </li>

                                @else

                                    <li class="page-item disabled">

                                        <span class="page-link">

                                            &rsaquo;

                                        </span>

                                    </li>

                                @endif

                            </ul>

                        </nav>

                    </div>

                @endif

            </div>

        </div>

    </div>


    <!-- =========================================
         MODAL IMPORT EXCEL
         ========================================= -->

    <div class="modal fade"
        id="importExcelModal"
        tabindex="-1"
        aria-labelledby="importExcelModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title"
                        id="importExcelModalLabel">

                        <i class="bi bi-file-earmark-excel text-success me-2"></i>

                        Import Data Barang via Excel

                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">

                    </button>

                </div>


                <form action="{{ route('items.import') }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <div class="modal-body">

                        <div class="mb-3">

                            <label for="file"
                                class="form-label">

                                Pilih File Excel
                                (.xlsx / .xls / .csv)

                            </label>

                            <input class="form-control"
                                type="file"
                                id="file"
                                name="file"
                                accept=".xlsx, .xls, .csv"
                                required>

                        </div>


                        <div class="alert alert-info py-2 small mb-0">

                            <i class="bi bi-info-circle me-1"></i>

                            Pastikan format file sesuai dengan template.

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Batal

                        </button>

                        <button type="submit"
                            class="btn btn-success">

                            <i class="bi bi-upload me-1"></i>

                            Upload & Import

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

**Catatan:** controller `ItemController.php` yang sebelumnya saya berikan tetap digunakan. Pagination custom di atas juga tetap membawa parameter **search, kategori, dan supplier**, jadi ketika pindah halaman filter tidak hilang.

Kalau setelah ditempel panahnya masih terlihat besar, kemungkinan bukan dari pagination ini lagi, dan kita bisa kecilkan lagi sampai benar-benar seperti desain tabel yang kamu inginkan.
