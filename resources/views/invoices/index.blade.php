@extends('layouts.app')

@section('title', 'Daftar Invoice | Sistem SPPG')

@push('styles')
<style>
    .invoice-list-page {
        padding: 24px;
    }

    .invoice-list-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .invoice-list-page .page-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .invoice-list-page .page-description {
        color: #6b7280;
        margin: 0;
        font-size: 13px;
    }

    .invoice-list-page .filter-card,
    .invoice-list-page .table-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .invoice-list-page .table-responsive {
        overflow-x: auto;
    }

    .invoice-list-page table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .invoice-list-page th {
        background: #f3f4f6;
        color: #374151;
        padding: 12px 10px;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoice-list-page td {
        padding: 12px 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        vertical-align: middle;
    }

    .invoice-list-page tbody tr:hover {
        background: #f9fafb;
    }

    .invoice-list-page .invoice-number {
        font-weight: 700;
        color: #1d4ed8;
    }

    .invoice-list-page .amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .invoice-list-page .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    .invoice-list-page .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 10px;
        border-radius: 5px;
        font-size: 12px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .invoice-list-page .btn-detail {
        background: #2563eb;
        color: #fff;
    }

    .invoice-list-page .btn-print {
        background: #059669;
        color: #fff;
    }

    .invoice-list-page .btn-filter {
        background: #2563eb;
        color: #fff;
        padding: 9px 14px;
        border: none;
        border-radius: 5px;
    }

    .invoice-list-page .btn-reset {
        background: #e5e7eb;
        color: #374151;
        padding: 9px 14px;
        border-radius: 5px;
        text-decoration: none;
    }

    .invoice-list-page .empty-state {
        text-align: center;
        padding: 45px 15px;
        color: #6b7280;
    }

    .invoice-list-page .pagination-wrapper {
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .invoice-list-page {
            padding: 12px;
        }

        .invoice-list-page .page-title {
            font-size: 20px;
        }

        .invoice-list-page .filter-card,
        .invoice-list-page .table-card {
            padding: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="invoice-list-page">

    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar Invoice</h1>
            <p class="page-description">
                Daftar Invoice Belanja Program MBG berdasarkan transaksi Barang Keluar.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTER --}}
    <div class="filter-card">
        <form action="{{ route('invoices.index') }}" method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label for="search" class="form-label">
                        Cari Invoice
                    </label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Nomor Invoice atau nama dapur">
                </div>

                <div class="col-md-3">
                    <label for="date_from" class="form-label">
                        Dari Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        class="form-control"
                        value="{{ request('date_from') }}">
                </div>

                <div class="col-md-3">
                    <label for="date_to" class="form-label">
                        Sampai Tanggal
                    </label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        class="form-control"
                        value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn-filter">
                        Cari
                    </button>

                    <a href="{{ route('invoices.index') }}" class="btn-reset">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- TABEL DAFTAR INVOICE --}}
    <div class="table-card">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Riwayat Invoice</h5>
            <span class="text-muted small">
                Total: {{ $invoices->total() }} Invoice
            </span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 55px;">No.</th>
                        <th>Nomor Invoice</th>
                        <th>Tanggal</th>
                        <th>Dapur SPPG</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>
                                {{ $invoices->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <span class="invoice-number">
                                    {{ $invoice->invoice_number ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $invoice->invoice_date
                                    ? \Carbon\Carbon::parse($invoice->invoice_date)->translatedFormat('d M Y')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $invoice->kitchen?->name ?? '-' }}
                            </td>

                            <td class="text-end amount">
                                Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        href="{{ route('invoices.show', $invoice->id) }}"
                                        class="btn-action btn-detail"
                                        title="Lihat detail Invoice">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>

                                    <a
                                        href="{{ route('invoices.show', $invoice->id) }}?print=1"
                                        class="btn-action btn-print"
                                        title="Lihat dan cetak Invoice">
                                        <i class="bi bi-printer"></i>
                                        Cetak
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-receipt fs-1"></i>
                                    <h5 class="mt-3">Belum Ada Invoice</h5>
                                    <p class="mb-0">
                                        Invoice akan muncul setelah transaksi OUT diproses.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $invoices->withQueryString()->links() }}
        </div>

    </div>
</div>
@endsection