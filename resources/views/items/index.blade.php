@extends('layouts.app')

@section('title', 'Data Barang')

@push('styles')
<style>
    .items-page .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .items-page .pagination .page-link {
        border-radius: 6px !important;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #495057;
    }

    .items-page .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .items-page .table th {
        white-space: nowrap;
        font-size: 13px;
    }

    .items-page .table td {
        font-size: 13px;
    }

    .items-page .item-code {
        font-family: monospace;
        white-space: nowrap;
    }

    .items-page .filter-card {
        background: #fff;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid items-page py-3">

    <div class="card shadow-sm border-0">
        <div class="card-body p-3 p-lg-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-box-seam text-primary me-2"></i>
                        Data Barang
                    </h4>
                    <div class="text-muted small">
                        Kelola master barang, kategori, supplier, dan minimum stok.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button"
                        class="btn btn-outline-success"
                        data-bs-toggle="modal"
                        data-bs-target="#importExcelModal">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Import Excel
                    </button>

                    <a href="{{ route('items.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Barang
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            <div class="card filter-card border mb-4">
                <div class="card-body">
                    <form action="{{ route('items.index') }}" method="GET">
                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-md-5">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bi bi-search me-1"></i>Cari Barang
                                </label>
                                <input type="text"
                                    name="search"
                                    id="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari kode atau nama barang...">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="category_id" class="form-label fw-semibold">
                                    <i class="bi bi-tags me-1"></i>Kategori
                                </label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories->reject(function ($category) {
                                        return mb_strtolower(trim($category->name)) === 'insentif';
                                    }) as $category)
                                        <option value="{{ $category->id }}"
                                            @selected((string) request('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="supplier_id" class="form-label fw-semibold">
                                    <i class="bi bi-truck me-1"></i>Supplier
                                </label>
                                <select name="supplier_id" id="supplier_id" class="form-select">
                                    <option value="">Semua Supplier</option>
                                    @foreach($suppliers->whereIn('id', [1, 2, 3, 4]) as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            @selected((string) request('supplier_id') === (string) $supplier->id)>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-1 d-grid">
                                <button type="submit" class="btn btn-primary" title="Terapkan filter">
                                    <i class="bi bi-funnel-fill"></i>
                                    <span class="d-md-none ms-1">Filter</span>
                                </button>
                            </div>
                        </div>

                        @if(request()->filled('search') || request()->filled('category_id') || request()->filled('supplier_id'))
                            <div class="mt-3">
                                <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="text-muted small">
                    Menampilkan
                    <strong>{{ $items->firstItem() ?? 0 }}</strong>–<strong>{{ $items->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $items->total() }}</strong> barang
                </div>

                @if(request()->filled('search') || request()->filled('category_id') || request()->filled('supplier_id'))
                    <span class="badge bg-primary-subtle text-primary-emphasis">
                        <i class="bi bi-funnel-fill me-1"></i>Filter aktif
                    </span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 55px;">No</th>
                            <th style="width: 125px;">Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Min. Stok</th>
                            <th class="text-center" style="width: 75px;">Hapus</th>
                            <th class="text-center" style="width: 75px;">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="text-center text-muted">
                                    {{ ($items->firstItem() ?? 1) + $loop->index }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border item-code">
                                        {{ $item->code }}
                                    </span>
                                </td>

                                <td class="fw-semibold">{{ $item->name }}</td>

                                <td>
                                    @if($item->category)
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            {{ $item->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->supplier)
                                        <span class="text-secondary">
                                            <i class="bi bi-truck me-1"></i>{{ $item->supplier->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">{{ $item->unit }}</td>

                                <td class="text-center fw-semibold">
                                    {{ number_format((float) $item->minimum_stock, 2, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    <form action="{{ route('items.destroy', $item) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus barang {{ addslashes($item->name) }}? Data yang sudah dihapus tidak dapat dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Barang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('items.edit', array_merge(
                                        ['item' => $item->id],
                                        request()->only(['search', 'category_id', 'supplier_id', 'page'])
                                    )) }}"
                                        class="btn btn-sm btn-outline-warning"
                                        title="Edit Barang">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    @if(request()->filled('search') || request()->filled('category_id') || request()->filled('supplier_id'))
                                        Data barang tidak ditemukan sesuai filter.
                                    @else
                                        Belum ada data barang.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                @php
                    $currentPage = $items->currentPage();
                    $lastPage = $items->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);

                    if ($currentPage <= 3) {
                        $endPage = min($lastPage, 5);
                    }

                    if ($currentPage >= $lastPage - 2) {
                        $startPage = max(1, $lastPage - 4);
                    }
                @endphp

                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Navigasi halaman barang">
                        <ul class="pagination">
                            <li class="page-item {{ $items->onFirstPage() ? 'disabled' : '' }}">
                                @if($items->onFirstPage())
                                    <span class="page-link">&lsaquo;</span>
                                @else
                                    <a class="page-link" href="{{ $items->previousPageUrl() }}" aria-label="Sebelumnya">&lsaquo;</a>
                                @endif
                            </li>

                            @if($startPage > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $items->url(1) }}">1</a>
                                </li>
                                @if($startPage > 2)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                            @endif

                            @for($page = $startPage; $page <= $endPage; $page++)
                                <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                    @if($page === $currentPage)
                                        <span class="page-link">{{ $page }}</span>
                                    @else
                                        <a class="page-link" href="{{ $items->url($page) }}">{{ $page }}</a>
                                    @endif
                                </li>
                            @endfor

                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $items->url($lastPage) }}">{{ $lastPage }}</a>
                                </li>
                            @endif

                            <li class="page-item {{ $items->hasMorePages() ? '' : 'disabled' }}">
                                @if($items->hasMorePages())
                                    <a class="page-link" href="{{ $items->nextPageUrl() }}" aria-label="Berikutnya">&rsaquo;</a>
                                @else
                                    <span class="page-link">&rsaquo;</span>
                                @endif
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif

        </div>
    </div>
</div>

<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">
                    <i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Barang via Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <label for="file" class="form-label">Pilih File Excel (.xlsx / .xls / .csv)</label>
                    <input class="form-control"
                        type="file"
                        id="file"
                        name="file"
                        accept=".xlsx,.xls,.csv"
                        required>

                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Pastikan file mengikuti format template import yang digunakan aplikasi.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i>Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection