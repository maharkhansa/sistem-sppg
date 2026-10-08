<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Daftar Barang Masuk</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet">

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f8f9fa;
        font-family: Arial, Helvetica, sans-serif;
    }

    .page-container {
        padding: 25px;
    }

    .card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background-color: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .table td {
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

</head>

<body>

<div class="page-container">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="mb-1">
            <i class="bi bi-box-arrow-in-down me-2"></i>
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

@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        <i class="bi bi-check-circle me-1"></i>

        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- ERROR --}}

@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="bi bi-exclamation-triangle me-1"></i>

        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- FILTER --}}

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <form
            method="GET"
            action="{{ route('stock-transactions.index') }}">

            <div class="row g-3">

                {{-- SEARCH --}}

                <div class="col-md-5">

                    <label class="form-label small fw-semibold">
                        Cari Transaksi
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Nomor transaksi atau supplier">

                    </div>

                </div>


                {{-- TANGGAL --}}

                <div class="col-md-3">

                    <label class="form-label small fw-semibold">
                        Tanggal
                    </label>

                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="{{ request('date') }}">

                </div>


                {{-- SUPPLIER --}}

                <div class="col-md-3">

                    <label class="form-label small fw-semibold">
                        Supplier
                    </label>

                    <select
                        name="supplier_id"
                        class="form-select">

                        <option value="">
                            Semua Supplier
                        </option>

                        @foreach($suppliers as $supplier)

                            <option
                                value="{{ $supplier->id }}"
                                {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>

                                {{ $supplier->name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- SEARCH BUTTON --}}

                <div class="col-md-1 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        title="Cari">

                        <i class="bi bi-search"></i>

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


{{-- INFO --}}

<div class="d-flex justify-content-between align-items-center mb-2">

    <small class="text-muted">

        Menampilkan

        <strong>
            {{ $transactions->firstItem() ?? 0 }}
        </strong>

        -

        <strong>
            {{ $transactions->lastItem() ?? 0 }}
        </strong>

        dari

        <strong>
            {{ $transactions->total() }}
        </strong>

        transaksi

    </small>

</div>


{{-- TABLE --}}

<div class="card shadow-sm">

    <div class="card-body p-0">

        @if($transactions->count() > 0)

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th
                                width="50"
                                class="text-center">
                                No
                            </th>

                            <th>
                                No. Transaksi
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Supplier
                            </th>

                            <th class="text-center">
                                Jenis Barang
                            </th>

                            <th class="text-end">
                                Total Qty
                            </th>

                            <th class="text-end">
                                Total Nilai
                            </th>

                            <th
                                width="80"
                                class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($transactions as $transaction)

                            @php

                                $totalQty =
                                    $transaction->details->sum('quantity');

                                $totalAmount =
                                    $transaction->details->sum('subtotal');

                            @endphp

                            <tr>

                                {{-- NO --}}

                                <td class="text-center">

                                    {{
                                        $transactions->firstItem()
                                        + $loop->index
                                    }}

                                </td>


                                {{-- NOMOR --}}

                                <td>

                                    <span class="transaction-number">

                                        {{
                                            $transaction->transaction_number
                                        }}

                                    </span>

                                </td>


                                {{-- TANGGAL --}}

                                <td>

                                    {{
                                        $transaction->transaction_date
                                            ? $transaction->transaction_date->format('d-m-Y')
                                            : '-'
                                    }}

                                </td>


                                {{-- SUPPLIER --}}

                                <td>

                                    {{
                                        $transaction->supplier->name
                                        ?? '-'
                                    }}

                                </td>


                                {{-- JUMLAH JENIS BARANG --}}

                                <td class="text-center">

                                    <span class="badge rounded-pill badge-in">

                                        {{
                                            $transaction->details->count()
                                        }}

                                        item

                                    </span>

                                </td>


                                {{-- TOTAL QTY --}}

                                <td class="text-end">

                                    {{
                                        number_format(
                                            (float) $totalQty,
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}

                                </td>


                                {{-- TOTAL NILAI --}}

                                <td class="text-end">

                                    Rp
                                    {{
                                        number_format(
                                            (float) $totalAmount,
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}

                                </td>


                                {{-- DETAIL --}}

                                <td class="text-center">

                                    <a
                                        href="{{
                                            route(
                                                'stock-transactions.show',
                                                $transaction
                                            )
                                        }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Lihat Detail">

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

                <h6 class="mt-3">
                    Belum Ada Transaksi
                </h6>

                <p class="mb-0">
                    Belum terdapat data transaksi barang masuk.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- PAGINATION --}}

@if($transactions->hasPages())

    <div class="d-flex justify-content-center mt-4">

        <nav aria-label="Pagination">

            <ul class="pagination mb-0">

                {{-- PREVIOUS --}}

                @if($transactions->onFirstPage())

                    <li class="page-item disabled">

                        <span class="page-link">
                            &lsaquo;
                        </span>

                    </li>

                @else

                    <li class="page-item">

                        <a
                            class="page-link"
                            href="{{
                                $transactions->previousPageUrl()
                            }}">

                            &lsaquo;

                        </a>

                    </li>

                @endif


                {{-- NUMBER --}}

                @foreach(
                    $transactions->getUrlRange(
                        1,
                        $transactions->lastPage()
                    ) as $page => $url
                )

                    @if(
                        $page ==
                        $transactions->currentPage()
                    )

                        <li class="page-item active">

                            <span class="page-link">
                                {{ $page }}
                            </span>

                        </li>

                    @else

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $url }}">

                                {{ $page }}

                            </a>

                        </li>

                    @endif

                @endforeach


                {{-- NEXT --}}

                @if($transactions->hasMorePages())

                    <li class="page-item">

                        <a
                            class="page-link"
                            href="{{
                                $transactions->nextPageUrl()
                            }}">

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

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
