@extends('layouts.app')

@section('title', 'Barang Keluar (OUT) | Sistem SPPG')

@push('styles')
<style>
    .out-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 24px;
        overflow: hidden;
        background: #fff;
    }

    .out-header {
        background: #f8fafc;
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .out-header table {
        width: 100%;
        border-collapse: collapse;
    }

    .out-header td {
        padding: 7px;
        vertical-align: top;
    }

    .out-label {
        font-weight: 600;
        width: 145px;
        color: #555;
    }

    .out-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .out-detail-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .out-detail-table th,
    .out-detail-table td {
        border: 1px solid #dee2e6;
        padding: 10px;
        font-size: 13px;
    }

    .out-detail-table th {
        background: #f8f9fa;
        color: #495057;
        white-space: nowrap;
    }

    .out-detail-table td {
        vertical-align: middle;
    }

    .out-text-center {
        text-align: center;
    }

    .out-text-right {
        text-align: right;
        white-space: nowrap;
    }

    .out-total {
        font-weight: 700;
        background: #f8f9fa;
    }

    .out-number {
        color: #0d6efd;
        font-weight: 700;
    }

    .out-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #f8d7da;
        color: #842029;
    }

    .out-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .out-actions form {
        margin: 0;
    }

    .out-actions .btn {
        font-size: 12px;
    }

    .out-filter {
        background: #f8fafc;
        border: 1px solid #dee2e6;
        border-radius: 10px;
    }

    .out-empty {
        padding: 45px 20px;
        text-align: center;
        color: #6c757d;
    }

    .out-empty i {
        font-size: 38px;
    }

    .out-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 24px;
    }

    @media (max-width: 767.98px) {
        .out-header {
            overflow-x: auto;
        }

        .out-header table {
            min-width: 600px;
        }

        .out-label {
            width: 120px;
        }

        .out-pagination {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid container-xl py-4 py-lg-5">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">
                <i class="bi bi-box-arrow-up me-2 text-danger"></i>
                Barang Keluar (OUT)
            </h2>
            <p class="text-muted small mb-0">
                Riwayat pengeluaran barang berdasarkan Purchase Order dan dapur SPPG.
            </p>
        </div>

        <a href="{{ route('purchase-orders.index') }}"
            class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Purchase Order
        </a>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}

            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}

            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

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
    <div class="card out-filter shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <h5 class="h6 fw-bold mb-3">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Cari dan Filter Barang Keluar
            </h5>

            <form method="GET" action="{{ route('stock-transactions.out') }}">
                <div class="row g-3">

                    <div class="col-12 col-lg-5">
                        <label for="search" class="form-label small fw-semibold">
                            Pencarian
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            placeholder="Nomor OUT, nomor PO, kode SPPG, nama dapur..."
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-12 col-lg-3">
                        <label for="kitchen_id" class="form-label small fw-semibold">
                            Dapur / SPPG
                        </label>

                        <select name="kitchen_id" id="kitchen_id" class="form-select">
                            <option value="">Semua Dapur / SPPG</option>

                            @foreach ($kitchens as $kitchen)
                                <option
                                    value="{{ $kitchen->id }}"
                                    {{ (string) request('kitchen_id') === (string) $kitchen->id ? 'selected' : '' }}>
                                    {{ $kitchen->id_sppg ?? '-' }} -
                                    {{ $kitchen->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="date_from" class="form-label small fw-semibold">
                            Tanggal Mulai
                        </label>

                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="date_to" class="form-label small fw-semibold">
                            Tanggal Selesai
                        </label>

                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}">
                    </div>

                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>
                        Terapkan Filter
                    </button>

                    <a href="{{ route('stock-transactions.out') }}"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- INFORMASI HASIL --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="text-muted small">
            Menampilkan
            <strong>{{ $transactions->firstItem() ?? 0 }}</strong>
            sampai
            <strong>{{ $transactions->lastItem() ?? 0 }}</strong>
            dari
            <strong>{{ $transactions->total() }}</strong>
            transaksi Barang Keluar
        </div>

        <span class="badge bg-secondary-subtle text-secondary border">
            {{ $transactions->count() }} transaksi pada halaman ini
        </span>
    </div>

    {{-- DAFTAR TRANSAKSI --}}
    @forelse ($transactions as $transaction)
        <div class="out-card shadow-sm">

            {{-- INFORMASI TRANSAKSI --}}
            <div class="out-header">
                <table>
                    <tbody>
                        <tr>
                            <td class="out-label">No. OUT</td>
                            <td>
                                <strong class="out-number">
                                    {{ $transaction->transaction_number }}
                                </strong>
                            </td>

                            <td class="out-label">Tanggal</td>
                            <td>
                                {{ $transaction->transaction_date
                                    ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d-m-Y')
                                    : '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="out-label">No. PO</td>
                            <td>
                                {{ $transaction->purchaseOrder?->po_number ?? '-' }}
                            </td>

                            <td class="out-label">Jenis Transaksi</td>
                            <td>
                                <span class="out-badge">BARANG KELUAR</span>
                            </td>
                        </tr>

                        <tr>
                            <td class="out-label">SPPG / Dapur</td>
                            <td colspan="3">
                                <strong>{{ $transaction->kitchen?->id_sppg ?? '-' }}</strong>
                                - {{ $transaction->kitchen?->name ?? '-' }}
                            </td>
                        </tr>

                        @if ($transaction->notes)
                            <tr>
                                <td class="out-label">Catatan</td>
                                <td colspan="3">{{ $transaction->notes }}</td>
                            </tr>
                        @endif

                        {{-- AKSI --}}
                        <tr>
                            <td class="out-label">Aksi</td>
                            <td colspan="3">
                                <div class="out-actions">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('stock-transactions.edit', $transaction->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        Edit
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        action="{{ route('stock-transactions.destroy', $transaction->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus transaksi {{ $transaction->transaction_number }}? Pastikan dampaknya terhadap stok, Invoice, dan Nota sudah diperiksa.');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash me-1"></i>
                                            Hapus
                                        </button>
                                    </form>

                                    {{-- INVOICE --}}
                                    @if ($transaction->invoice)
                                        <a
                                            href="{{ route('invoices.show', $transaction->invoice->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-receipt me-1"></i>
                                            Lihat Invoice
                                        </a>
                                    @else
                                        <form
                                            action="{{ route('stock-transactions.create-invoice', $transaction->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Buat Invoice otomatis dari transaksi OUT ini?');">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-file-earmark-plus me-1"></i>
                                                Buat Invoice
                                            </button>
                                        </form>
                                    @endif

                                    {{-- NOTA KELUAR --}}
                                    @if ($transaction->notaKeluars && $transaction->notaKeluars->isNotEmpty())
                                        @foreach ($transaction->notaKeluars as $nota)
                                            <a
                                                href="{{ route('nota-keluars.show', $nota->id) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                Nota
                                                @if ($nota->supplier?->name)
                                                    - {{ $nota->supplier->name }}
                                                @endif
                                            </a>
                                        @endforeach
                                    @else
                                        <form
                                            action="{{ route('stock-transactions.create-nota', $transaction->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Buat Nota Keluar berdasarkan supplier dari transaksi OUT ini?');">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="bi bi-file-earmark-plus me-1"></i>
                                                Buat Nota
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- DETAIL BARANG --}}
            <div class="out-table-wrapper">
                <table class="out-detail-table">
                    <thead>
                        <tr>
                            <th width="50" class="out-text-center">No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th width="100" class="out-text-right">Qty</th>
                            <th width="100" class="out-text-center">Satuan</th>
                            <th width="140" class="out-text-right">Harga</th>
                            <th width="160" class="out-text-right">Jumlah</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($transaction->details as $index => $detail)
                            <tr>
                                <td class="out-text-center">{{ $index + 1 }}</td>
                                <td>{{ $detail->item?->code ?? '-' }}</td>
                                <td>{{ $detail->item?->name ?? '-' }}</td>

                                <td class="out-text-right">
                                    {{ rtrim(rtrim(number_format((float) $detail->quantity, 2, ',', '.'), '0'), ',') }}
                                </td>

                                <td class="out-text-center">
                                    {{ $detail->unit ?? '-' }}
                                </td>

                                <td class="out-text-right">
                                    Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                </td>

                                <td class="out-text-right">
                                    Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="out-empty">
                                    Tidak ada detail barang.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="out-total">
                            <td colspan="6" class="out-text-right">Total</td>
                            <td class="out-text-right">
                                Rp {{ number_format((float) $transaction->details->sum('subtotal'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="out-empty">
                <i class="bi bi-inbox d-block mb-3"></i>
                <h5 class="fw-semibold">Belum Ada Transaksi Barang Keluar</h5>
                <p class="mb-3">
                    Tidak ada transaksi yang cocok dengan pencarian atau filter.
                </p>
                <a href="{{ route('purchase-orders.index') }}"
                    class="btn btn-primary">
                    <i class="bi bi-cart-check me-1"></i>
                    Buka Purchase Order
                </a>
            </div>
        </div>
    @endforelse

    {{-- PAGINATION --}}
    @if ($transactions->hasPages())
        <div class="out-pagination">
            <div class="text-muted small">
                Halaman {{ $transactions->currentPage() }}
                dari {{ $transactions->lastPage() }}
            </div>

            <div>
                {{ $transactions->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif

</div>
@endsection