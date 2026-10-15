@extends('layouts.app')

@section('title', 'Detail Barang Masuk | Sistem SPPG')

@push('styles')
<style>
    .page-container {
        padding: 8px 0;
    }

    .transaction-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }

    .transaction-number {
        font-size: 18px;
        font-weight: 700;
        color: #0d6efd;
        overflow-wrap: anywhere;
    }

    .badge-in {
        background-color: #d1e7dd;
        color: #0f5132;
        font-weight: 600;
    }

    .info-label {
        display: block;
        margin-bottom: 5px;
        color: #6c757d;
        font-size: 12px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .transaction-table {
        margin-bottom: 0;
        min-width: 1100px;
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

    .total-box {
        background-color: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
    }

    .total-final {
        font-size: 18px;
        color: #0d6efd;
    }

    .cost-highlight {
        color: #b45309;
        font-weight: 600;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 36px;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .total-box {
            background-color: #fff;
        }

        .transaction-card {
            box-shadow: none !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        body {
            font-size: 12px;
        }
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
                    Detail Transaksi Barang Masuk
                </h4>

                <small class="text-muted">
                    Informasi penerimaan barang dari vendor ke stok persediaan
                </small>
            </div>

            <div class="d-flex gap-2 no-print">
                <button type="button"
                        class="btn btn-outline-primary"
                        onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>
                    Cetak
                </button>

                <a href="{{ route('stock-transactions.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>

        {{-- PESAN --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

        {{-- INFORMASI TRANSAKSI --}}
        <div class="card transaction-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    Informasi Transaksi
                </h6>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="row g-4">

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Nomor Transaksi</span>
                        <div class="transaction-number">
                            {{ $stockTransaction->transaction_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Tanggal Barang Masuk</span>
                        <div class="info-value">
                            {{ $stockTransaction->transaction_date
                                ? \Illuminate\Support\Carbon::parse($stockTransaction->transaction_date)->format('d-m-Y')
                                : '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Vendor Pengirim</span>
                        <div class="info-value">
                            {{ $stockTransaction->vendor_name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Nomor Nota Pembelian</span>
                        <div class="info-value">
                            {{ $stockTransaction->purchase_note_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Jenis Transaksi</span>
                        <div class="mt-1">
                            @if ($stockTransaction->type === 'IN')
                                <span class="badge rounded-pill badge-in px-3 py-2">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>
                                    Barang Masuk
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    {{ $stockTransaction->type ?? '-' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Dibuat Oleh</span>
                        <div class="info-value">
                            {{ $stockTransaction->creator->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6">
                        <span class="info-label">Catatan</span>
                        <div style="white-space: pre-line;">
                            {{ $stockTransaction->notes ?: '-' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- HITUNG TOTAL --}}
        @php
            $totalQty = 0;
            $totalAmount = 0;
            $totalAllocatedPorterCost = 0;

            $porterCost = (float) ($stockTransaction->porter_cost ?? 0);

            foreach ($stockTransaction->details as $detail) {
                $totalQty += (float) ($detail->quantity ?? 0);
                $totalAmount += (float) ($detail->subtotal ?? 0);
                $totalAllocatedPorterCost +=
                    (float) ($detail->allocated_porter_cost ?? 0);
            }

            $totalAmount = round($totalAmount, 2);
            $porterCost = round($porterCost, 2);
            $totalAllocatedPorterCost = round($totalAllocatedPorterCost, 2);

            // Total keseluruhan = nilai barang + biaya kuli.
            $grandTotal = round($totalAmount + $porterCost, 2);
        @endphp

        {{-- DETAIL BARANG --}}
        <div class="card transaction-card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-1 fw-bold">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    Daftar Barang Masuk
                </h6>

                <small class="text-muted">
                    Supplier dan kategori mengikuti data master barang.
                    Alokasi biaya kuli ditampilkan terpisah dari subtotal barang.
                </small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle transaction-table">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Supplier Master</th>
                                <th class="text-end">Jumlah</th>
                                <th>Satuan</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal Barang</th>
                                <th class="text-end">Alokasi Biaya Kuli</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($stockTransaction->details as $detail)
                                <tr>
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $detail->item->code ?? '-' }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $detail->item->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $detail->item->category->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $detail->supplier->name
                                            ?? $detail->item->supplier->name
                                            ?? '-' }}
                                    </td>

                                    <td class="text-end text-nowrap">
                                        {{ number_format((float) $detail->quantity, 2, ',', '.') }}
                                    </td>

                                    <td>
                                        {{ $detail->unit ?? $detail->item->unit ?? '-' }}
                                    </td>

                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap fw-semibold">
                                        Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap cost-highlight">
                                        Rp {{ number_format((float) ($detail->allocated_porter_cost ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>

                                            <div class="mt-2 fw-semibold">
                                                Tidak ada detail barang.
                                            </div>

                                            <small>
                                                Belum ada barang yang tercatat pada transaksi ini.
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- TOTAL TABEL --}}
                        @if ($stockTransaction->details->count() > 0)
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">
                                        TOTAL
                                    </th>

                                    <th class="text-end text-nowrap">
                                        {{ number_format($totalQty, 2, ',', '.') }}
                                    </th>

                                    <th></th>
                                    <th></th>

                                    <th class="text-end text-nowrap">
                                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                    </th>

                                    <th class="text-end text-nowrap cost-highlight">
                                        Rp {{ number_format($totalAllocatedPorterCost, 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- RINGKASAN TRANSAKSI --}}
        <div class="row justify-content-end mt-4">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="total-box shadow-sm">

                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-calculator me-2 text-primary"></i>
                        Ringkasan Transaksi
                    </h6>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Jenis Barang</span>
                        <strong>
                            {{ $stockTransaction->details->count() }} item
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Quantity</span>
                        <strong>
                            {{ number_format($totalQty, 2, ',', '.') }}
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Nilai Barang</span>
                        <strong class="text-end">
                            Rp {{ number_format($totalAmount, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Biaya Kuli</span>
                        <strong class="cost-highlight text-end">
                            Rp {{ number_format($porterCost, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Alokasi Biaya Kuli</span>
                        <strong class="text-end">
                            Rp {{ number_format($totalAllocatedPorterCost, 0, ',', '.') }}
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <strong>Total Keseluruhan</strong>

                        <strong class="total-final text-end">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </strong>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Total keseluruhan dihitung dari total nilai barang
                        ditambah biaya kuli.
                    </small>

                    @if (abs($totalAllocatedPorterCost - $porterCost) >= 0.01)
                        <div class="alert alert-warning mt-3 mb-0 py-2">
                            <small>
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Total alokasi biaya kuli berbeda dari biaya kuli transaksi.
                                Periksa kembali alokasi biaya kuli pada transaksi ini.
                            </small>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- TOMBOL KEMBALI --}}
        <div class="d-flex justify-content-end mt-4 no-print">
            <a href="{{ route('stock-transactions.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke Daftar Barang Masuk
            </a>
        </div>

    </div>
</div>
@endsection