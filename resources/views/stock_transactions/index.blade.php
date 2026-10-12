@extends('layouts.app')

@section('title', 'Daftar Barang Masuk | Sistem SPPG')

@push('styles')
<style>
    .page-container {
        padding: 8px 0;
    }

    .transaction-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .transaction-table {
        margin-bottom: 0;
        min-width: 850px;
    }

    .transaction-table th {
        background-color: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .transaction-table td {
        font-size: 13px;
        vertical-align: middle;
    }

    .transaction-number {
        font-weight: 600;
        color: #0d6efd;
    }

    .badge-in {
        background-color: #d1e7dd;
        color: #0f5132;
        font-weight: 500;
    }

    .pagination {
        margin-bottom: 0;
        gap: 4px;
        flex-wrap: wrap;
    }

    .pagination .page-item {
        margin: 0;
    }

    .pagination .page-link {
        border: 1px solid #dee2e6;
        border-radius: 6px !important;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #495057;
        background-color: #fff;
    }

    .pagination .page-link:hover {
        background-color: #f1f3f5;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        background-color: #f8f9fa;
        color: #adb5bd;
    }

    .empty-state {
        padding: 50px 20px;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 40px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid container-xl py-4 py-lg-5">
    <div class="page-container">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">
                    <i class="bi bi-box-arrow-in-down me-2 text-primary"></i>
                    Daftar Barang Masuk
                </h4>
                <small class="text-muted">
                    Daftar transaksi penerimaan barang dari supplier
                </small>
            </div>

            <a href="{{ route('stock-transactions.create') }}"
                class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Barang Masuk
            </a>
        </div>

        {{-- SUCCESS --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}

                <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        {{-- ERROR --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ session('error') }}

                <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        {{-- FILTER --}}
        <div class="card transaction-card shadow-sm mb-4">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('stock-transactions.index') }}">
                    <div class="row g-3 align-items-end">

                        {{-- SEARCH --}}
                        <div class="col-12 col-md-5">
                            <label for="search" class="form-label small fw-semibold">
                                Cari Transaksi
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Nomor transaksi atau supplier">
                            </div>
                        </div>

                        {{-- TANGGAL --}}
                        <div class="col-12 col-md-3">
                            <label for="date" class="form-label small fw-semibold">
                                Tanggal
                            </label>

                            <input
                                type="date"
                                id="date"
                                name="date"
                                class="form-control"
                                value="{{ request('date') }}">
                        </div>

                        {{-- SUPPLIER --}}
                        <div class="col-12 col-md-3">
                            <label for="supplier_id" class="form-label small fw-semibold">
                                Supplier
                            </label>

                            <select id="supplier_id" name="supplier_id"
                                class="form-select">
                                <option value="">Semua Supplier</option>

                                @foreach ($suppliers as $supplier)
                                    <option
                                        value="{{ $supplier->id }}"
                                        {{ (string) request('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BUTTON --}}
                        <div class="col-12 col-md-1 d-flex gap-2">
                            <button type="submit"
                                class="btn btn-primary flex-grow-1"
                                title="Cari transaksi">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        <div class="col-12">
                            <a href="{{ route('stock-transactions.index') }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset Filter
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- INFORMASI DATA --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <small class="text-muted">
                Menampilkan
                <strong>{{ $transactions->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $transactions->lastItem() ?? 0 }}</strong>
                dari
                <strong>{{ $transactions->total() }}</strong>
                transaksi
            </small>
        </div>

        {{-- TABLE --}}
        <div class="card transaction-card shadow-sm">
            <div class="card-body p-0">

                @if ($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle transaction-table">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Jenis Barang</th>
                                    <th class="text-end">Total Qty</th>
                                    <th class="text-end">Total Nilai</th>
                                    <th width="80" class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($transactions as $transaction)
                                    @php
                                        $details = $transaction->details;

                                        $totalQty = $details->sum('quantity');
                                        $totalAmount = $details->sum('subtotal');
                                    @endphp

                                    <tr>
                                        {{-- NOMOR URUT --}}
                                        <td class="text-center">
                                            {{ $transactions->firstItem() + $loop->index }}
                                        </td>

                                        {{-- NOMOR TRANSAKSI --}}
                                        <td>
                                            <span class="transaction-number">
                                                {{ $transaction->transaction_number }}
                                            </span>
                                        </td>

                                        {{-- TANGGAL --}}
                                        <td>
                                            {{ $transaction->transaction_date
                                                ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d-m-Y')
                                                : '-' }}
                                        </td>

                                        {{-- SUPPLIER --}}
                                        <td>
                                            {{ $transaction->supplier->name ?? '-' }}
                                        </td>

                                        {{-- JUMLAH JENIS BARANG --}}
                                        <td class="text-center">
                                            <span class="badge rounded-pill badge-in px-2 py-2">
                                                {{ $details->count() }} item
                                            </span>
                                        </td>

                                        {{-- TOTAL QTY --}}
                                        <td class="text-end text-nowrap">
                                            {{ number_format((float) $totalQty, 2, ',', '.') }}
                                        </td>

                                        {{-- TOTAL NILAI --}}
                                        <td class="text-end text-nowrap fw-semibold">
                                            Rp {{ number_format((float) $totalAmount, 0, ',', '.') }}
                                        </td>

                                        {{-- DETAIL --}}
                                        <td class="text-center">
                                            <a
                                                href="{{ route('stock-transactions.show', $transaction) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Lihat detail transaksi"
                                                aria-label="Lihat detail transaksi">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>

                        <h6 class="mt-3">Belum Ada Transaksi</h6>

                        <p class="mb-3">
                            @if (request()->filled('search')
                                || request()->filled('date')
                                || request()->filled('supplier_id'))
                                Tidak ada transaksi yang sesuai dengan filter.
                            @else
                                Belum terdapat data transaksi barang masuk.
                            @endif
                        </p>

                        <a href="{{ route('stock-transactions.create') }}"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Barang Masuk
                        </a>
                    </div>
                @endif

            </div>
        </div>

        {{-- PAGINATION --}}
        @if ($transactions->hasPages())
            <div class="d-flex flex-column align-items-center mt-4">

                <div class="text-muted small text-center mb-3">
                    Halaman <strong>{{ $transactions->currentPage() }}</strong>
                    dari <strong>{{ $transactions->lastPage() }}</strong>
                </div>

                {{ $transactions->withQueryString()->links('pagination::bootstrap-5') }}

            </div>
        @endif

    </div>
</div>
@endsection