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
        min-width: 1200px;
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

    .porter-cost {
        color: #b45309;
        font-weight: 600;
    }

    .grand-total {
        color: #0d6efd;
        font-weight: 700;
    }

    .subtotal-amount {
        font-weight: 600;
        color: #198754;
    }

    .discount-amount {
        color: #dc3545;
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

    .action-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
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
                    Daftar penerimaan barang dari vendor ke stok persediaan
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

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

        {{-- ERROR --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
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

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

                {{-- FILTER --}}
        <div class="card transaction-card shadow-sm mb-4">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('stock-transactions.index') }}">
                    <div class="row g-3 align-items-end">

                        {{-- PENCARIAN --}}
                        <div class="col-12 col-md-6">
                            <label for="search" class="form-label filter-label">
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
                                    placeholder="Nomor transaksi atau vendor pengirim">
                            </div>
                        </div>

                        {{-- FILTER PERIODE --}}
                        <div class="col-12 col-md-6">
                            <label for="periode" class="form-label filter-label">
                                Periode Barang Masuk
                            </label>

                            <select
                                id="periode"
                                name="periode"
                                class="form-select"
                                onchange="toggleFilterPeriode()">

                                <option value="">Semua Periode</option>
                                <option value="hari" {{ request('periode') === 'hari' ? 'selected' : '' }}>
                                    Harian
                                </option>
                                <option value="bulan" {{ request('periode') === 'bulan' ? 'selected' : '' }}>
                                    Bulanan
                                </option>
                                <option value="tahun" {{ request('periode') === 'tahun' ? 'selected' : '' }}>
                                    Tahunan
                                </option>
                            </select>
                        </div>

                        {{-- PILIH HARI --}}
                        <div class="col-12 col-md-4" id="filter-hari">
                            <label for="tanggal" class="form-label filter-label">
                                Pilih Tanggal
                            </label>

                            <input
                                type="date"
                                id="tanggal"
                                name="tanggal"
                                class="form-control"
                                value="{{ request('tanggal') }}">
                        </div>

                        {{-- PILIH BULAN --}}
                        <div class="col-12 col-md-4" id="filter-bulan">
                            <label for="bulan" class="form-label filter-label">
                                Pilih Bulan
                            </label>

                            <select id="bulan" name="bulan" class="form-select">
                                <option value="">Pilih Bulan</option>
                                @foreach ([
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember',
                                ] as $nomorBulan => $namaBulan)
                                    <option
                                        value="{{ $nomorBulan }}"
                                        {{ request('bulan') === $nomorBulan ? 'selected' : '' }}>
                                        {{ $namaBulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- PILIH TAHUN --}}
                        <div class="col-12 col-md-4" id="filter-tahun">
                            <label for="tahun" class="form-label filter-label">
                                Pilih Tahun
                            </label>

                            <select id="tahun" name="tahun" class="form-select">
                                <option value="">Pilih Tahun</option>

                                @foreach (range((int) date('Y'), (int) date('Y') - 10) as $tahunPilihan)
                                    <option
                                        value="{{ $tahunPilihan }}"
                                        {{ (string) request('tahun') === (string) $tahunPilihan ? 'selected' : '' }}>
                                        {{ $tahunPilihan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TOMBOL FILTER --}}
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>
                                Terapkan Filter
                            </button>

                            <a
                                href="{{ route('stock-transactions.index') }}"
                                class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset Filter
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
            function toggleFilterPeriode() {
                const periode = document.getElementById('periode').value;

                const filterHari = document.getElementById('filter-hari');
                const filterBulan = document.getElementById('filter-bulan');
                const filterTahun = document.getElementById('filter-tahun');

                filterHari.style.display = periode === 'hari' ? '' : 'none';
                filterBulan.style.display = periode === 'bulan' ? '' : 'none';
                filterTahun.style.display =
                    periode === 'bulan' || periode === 'tahun' ? '' : 'none';
            }

            document.addEventListener('DOMContentLoaded', toggleFilterPeriode);
        </script>
        @endpush
        
        {{-- INFORMASI JUMLAH DATA --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <small class="text-muted">
                Menampilkan
                <strong>{{ $transactions->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $transactions->lastItem() ?? 0 }}</strong>
                dari
                <strong>{{ $transactions->total() }}</strong>
                transaksi barang masuk
            </small>
        </div>

        {{-- TABEL TRANSAKSI --}}
        <div class="card transaction-card shadow-sm">
            <div class="card-body p-0">

                @if ($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle transaction-table">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>No. Transaksi</th>
                                    <th>No. Nota Pembelian</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Vendor Pengirim</th>
                                    <th class="text-center">Jenis Barang</th>
                                    <th class="text-end">Total Qty</th>
                                    <th class="text-end">Subtotal Barang</th>
                                    <th class="text-end">Diskon Tambahan</th>
                                    <th class="text-end">Biaya Kuli</th>
                                    <th class="text-end">Biaya BBM</th>
                                    <th class="text-end">Biaya Parkir</th>
                                    <th class="text-end">Total Keseluruhan</th>
                                    <th width="115" class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($transactions as $transaction)
                                    @php
                                        $details = $transaction->details;

                                        /*
                                         * Total kuantitas semua detail.
                                         */
                                        $totalQty = (float) $details->sum('quantity');

                                        /*
                                         * Subtotal barang harus berasal dari
                                         * kolom subtotal detail yang tersimpan.
                                         *
                                         * Rumus penyimpanan yang benar:
                                         * (quantity * unit_price) - discount
                                         *
                                         * Diskon barang tidak dipotong lagi
                                         * di halaman Index.
                                         */
                                        $subtotalBarang = round(
                                            (float) $details->sum('subtotal'),
                                            2
                                        );

                                        /*
                                         * Diskon tambahan nota:
                                         * amount  = nominal rupiah.
                                         * percent = persentase subtotal barang.
                                         */
                                        $discountType = $transaction->purchase_discount_type
                                            ?: 'amount';

                                        $discountValue = max(
                                            0,
                                            (float) ($transaction->purchase_discount_value ?? 0)
                                        );

                                        if ($discountType === 'percent') {
                                            $diskonTambahan = round(
                                                $subtotalBarang * $discountValue / 100,
                                                2
                                            );
                                        } else {
                                            $diskonTambahan = round(
                                                $discountValue,
                                                2
                                            );
                                        }

                                        /*
                                         * Batasi diskon tambahan agar tidak
                                         * melebihi subtotal barang.
                                         */
                                        $diskonTambahan = min(
                                            $subtotalBarang,
                                            max(0, $diskonTambahan)
                                        );

                                        /*
                                         * Biaya kuli.
                                         */
                                        $porterCost = round(
                                            max(
                                                0,
                                                (float) ($transaction->porter_cost ?? 0)
                                            ),
                                            2
                                        );

                                        $fuelCost = round(
                                            max(
                                                0,
                                                (float) ($transaction->fuel_cost ?? 0)
                                            ),
                                            2
                                        );

                                        $parkingCost = round(
                                            max(
                                                0,
                                                (float) ($transaction->parking_cost ?? 0)
                                            ),
                                            2
                                        );

                                        /*
                                         * Total akhir:
                                         * subtotal barang
                                         * - diskon tambahan
                                         * + biaya kuli
                                         * + biaya bbm
                                         * + biaya parkir.
                                         */
                                        $grandTotal = round(
                                            max(
                                                0,
                                                $subtotalBarang - $diskonTambahan
                                            ) + $porterCost + $fuelCost + $parkingCost,
                                            2
                                        );
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

                                        {{-- NOMOR NOTA PEMBELIAN --}}
                                        <td>
                                            {{ $transaction->purchase_note_number ?? '-' }}
                                        </td>

                                        {{-- TANGGAL --}}
                                        <td class="text-nowrap">
                                            {{ $transaction->transaction_date
                                                ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d-m-Y')
                                                : '-' }}
                                        </td>

                                        {{-- VENDOR --}}
                                        <td>
                                            {{ $transaction->vendor_name ?? '-' }}
                                        </td>

                                        {{-- JENIS BARANG --}}
                                        <td class="text-center">
                                            <span class="badge rounded-pill badge-in px-2 py-2">
                                                {{ $details->count() }} item
                                            </span>
                                        </td>

                                        {{-- TOTAL QTY --}}
                                        <td class="text-end text-nowrap">
                                            {{ number_format($totalQty, 2, ',', '.') }}
                                        </td>

                                        {{-- SUBTOTAL BARANG --}}
                                        <td class="text-end text-nowrap subtotal-amount">
                                            Rp {{ number_format($subtotalBarang, 2, ',', '.') }}
                                        </td>

                                        {{-- DISKON TAMBAHAN --}}
                                        <td class="text-end text-nowrap discount-amount">
                                            @if ($diskonTambahan > 0)
                                                − Rp {{ number_format($diskonTambahan, 2, ',', '.') }}

                                                @if ($discountType === 'percent')
                                                    <small class="d-block text-muted">
                                                        {{ number_format($discountValue, 2, ',', '.') }}%
                                                    </small>
                                                @endif
                                            @else
                                                Rp 0,00
                                            @endif
                                        </td>

                                        {{-- BIAYA KULI --}}
                                        <td class="text-end text-nowrap porter-cost">
                                            Rp {{ number_format($porterCost, 2, ',', '.') }}
                                        </td>

                                        {{-- BIAYA BBM --}}
                                        <td class="text-end text-nowrap">
                                            Rp {{ number_format($fuelCost, 2, ',', '.') }}
                                        </td>

                                        {{-- BIAYA PARKIR --}}
                                        <td class="text-end text-nowrap">
                                            Rp {{ number_format($parkingCost, 2, ',', '.') }}
                                        </td>

                                        {{-- TOTAL KESELURUHAN --}}
                                        <td class="text-end text-nowrap grand-total">
                                            Rp {{ number_format($grandTotal, 2, ',', '.') }}
                                        </td>

                                        {{-- AKSI --}}
                                        <td class="text-center">
                                            <div class="action-buttons">

                                                @if ($transaction->type === 'IN')
                                                    <a
                                                        href="{{ route('stock-transactions.edit-in', $transaction) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Edit barang masuk"
                                                        aria-label="Edit barang masuk">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endif

                                                <a
                                                    href="{{ route('stock-transactions.show', $transaction) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Lihat detail transaksi"
                                                    aria-label="Lihat detail transaksi">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <form
                                                    action="{{ route('stock-transactions.destroy', $transaction->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi {{ $transaction->transaction_number }}? Stok dan data terkait dapat terpengaruh.');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Hapus transaksi"
                                                        aria-label="Hapus transaksi">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>

                        <h6 class="mt-3">Belum Ada Transaksi Barang Masuk</h6>

                        <p class="mb-3">
                            @if (request()->filled('search') || request()->filled('date'))
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
                    Halaman
                    <strong>{{ $transactions->currentPage() }}</strong>
                    dari
                    <strong>{{ $transactions->lastPage() }}</strong>
                </div>

                {{ $transactions->withQueryString()->links('pagination::bootstrap-5') }}

            </div>
        @endif

    </div>
</div>
@endsection