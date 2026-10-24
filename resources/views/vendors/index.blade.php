@extends('layouts.app')

@section('title', 'Data Vendor')

@section('content')

<style>
    .vendor-index {
        color: #1e293b;
    }

    .vendor-index .page-title {
        font-size: 1.45rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -.025em;
    }

    .vendor-index .page-subtitle {
        color: #64748b;
        font-size: .925rem;
    }

    .vendor-index .vendor-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .025);
    }

    .vendor-index .vendor-card-body {
        padding: 22px;
    }

    .vendor-index .form-label {
        font-size: .85rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
    }

    .vendor-index .form-control,
    .vendor-index .form-select {
        border-color: #dbe2ea;
        min-height: 42px;
        font-size: .9rem;
    }

    .vendor-index .form-control:focus,
    .vendor-index .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .1);
    }

    /* Tabel vendor */
    .vendor-index .table-responsive {
        overflow-x: hidden;
    }

    .vendor-index .vendor-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    /* Lebar masing-masing kolom */
    .vendor-index .vendor-table th:nth-child(1),
    .vendor-index .vendor-table td:nth-child(1) {
        width: 4%;
    }

    .vendor-index .vendor-table th:nth-child(2),
    .vendor-index .vendor-table td:nth-child(2) {
        width: 19%;
    }

    .vendor-index .vendor-table th:nth-child(3),
    .vendor-index .vendor-table td:nth-child(3) {
        width: 13%;
    }

    .vendor-index .vendor-table th:nth-child(4),
    .vendor-index .vendor-table td:nth-child(4) {
        width: 14%;
    }

    .vendor-index .vendor-table th:nth-child(5),
    .vendor-index .vendor-table td:nth-child(5) {
        width: 20%;
    }

    /* Jumlah barang dibuat lebih sempit */
    .vendor-index .vendor-table th:nth-child(6),
    .vendor-index .vendor-table td:nth-child(6) {
        width: 7%;
    }

    /* Lebar sisa diberikan ke kolom aksi */
    .vendor-index .vendor-table th:nth-child(7),
    .vendor-index .vendor-table td:nth-child(7) {
        width: 23%;
    }

    .vendor-index .vendor-table thead th {
        padding: 10px 8px;
        background: #f8fafc;
        color: #64748b;
        font-size: .70rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: normal;
        overflow-wrap: normal;
        word-break: normal;
        border-bottom: 1px solid #e5e7eb;
    }

    .vendor-index .vendor-table tbody td {
        padding: 11px 8px;
        font-size: .82rem;
        vertical-align: middle;
        border-color: #f1f5f9;
        overflow-wrap: anywhere;
    }

    .vendor-index .vendor-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .vendor-index .vendor-table tbody tr:hover {
        background: #f8fafc;
    }

    .vendor-index .vendor-number {
        color: #64748b;
        font-size: .82rem;
        font-weight: 600;
    }

    /* Informasi vendor */
    .vendor-index .vendor-name {
        font-weight: 700;
        color: #0f172a;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .vendor-index .vendor-address,
    .vendor-index .vendor-contact-sub {
        color: #64748b;
        font-size: .8rem;
        margin-top: 5px;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .vendor-index .vendor-info-label {
        color: #64748b;
        font-size: .75rem;
        margin-bottom: 3px;
    }

    .vendor-index .vendor-info-value {
        color: #334155;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    /* Status */
    .vendor-index .vendor-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 95px;
        padding: 7px 11px;
        border-radius: 30px;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .vendor-index .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .vendor-index .status-inactive {
        background: #f1f5f9;
        color: #475569;
    }

    /* Jumlah barang */
    .vendor-index .vendor-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        min-width: 0;
        max-width: 100%;
        padding: 4px 5px;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 700;
        white-space: normal;
        overflow-wrap: normal;
        word-break: normal;
        text-align: center;
    }

    /* Tombol aksi */
    .vendor-index .vendor-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 0;
        white-space: normal;
    }

    .vendor-index .vendor-actions .btn {
        width: 28px;
        height: 28px;
        min-width: 28px;
        flex: 0 0 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 1rem;
        line-height: 1;
        border-radius: 8px;
    }

    .vendor-index .vendor-actions form {
        display: inline-flex;
        margin: 0;
    }

    .vendor-index .vendor-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
    }

    .vendor-index .vendor-empty-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        border-radius: 14px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 1.4rem;
    }

    .vendor-index .vendor-table-title {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .vendor-index .vendor-table-subtitle {
        color: #64748b;
        font-size: .8rem;
        margin-top: 4px;
    }

    /* Tampilan layar kecil */
    @media (max-width: 767.98px) {
        .vendor-index .vendor-card-body {
            padding: 16px;
        }

        .vendor-index .page-title {
            font-size: 1.25rem;
        }

        .vendor-index .vendor-table thead th {
            padding: 8px 4px;
            font-size: .62rem;
        }

        .vendor-index .vendor-table tbody td {
            padding: 8px 4px;
            font-size: .72rem;
        }

        .vendor-index .vendor-actions {
            gap: 3px;
        }

        .vendor-index .vendor-actions .btn {
            width: 24px;
            height: 24px;
            min-width: 24px;
            flex-basis: 24px;
            font-size: .8rem;
        }

        .vendor-index .vendor-count {
            font-size: .62rem;
            padding: 3px;
        }
    }
</style>

<div class="container-fluid vendor-index pb-4">

    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-shop me-2 text-primary"></i>
                Data Vendor
            </h4>

            <p class="page-subtitle mb-0">
                Kelola pasar, toko, dan tempat pembelian barang.
            </p>
        </div>

        <a href="{{ route('vendors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Vendor
        </a>
    </div>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Notifikasi error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Error validasi --}}
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bi bi-exclamation-circle me-1"></i>
                Terjadi kesalahan
            </div>

            <ul class="mb-0 ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Pencarian dan filter --}}
    <div class="vendor-card mb-4">
        <div class="vendor-card-body">

            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-funnel text-primary"></i>
                <h6 class="fw-bold mb-0">Pencarian dan Filter</h6>
            </div>

            <form action="{{ route('vendors.index') }}" method="GET">
                <div class="row g-3 align-items-end">

                    {{-- Pencarian --}}
                    <div class="col-12 col-lg-6">
                        <label for="search" class="form-label">
                            Cari Vendor
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search text-secondary"></i>
                            </span>

                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Nama vendor, kontak, telepon, alamat...">
                        </div>
                    </div>

                    {{-- Tombol pencarian --}}
                    <div class="col-12 col-lg-3">
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i>
                                Cari
                            </button>

                            <a href="{{ route('vendors.index') }}"
                               class="btn btn-outline-secondary"
                               title="Reset pencarian">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </a>

                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Tabel vendor --}}
    <div class="vendor-card">
        <div class="vendor-card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                <div>
                    <h6 class="vendor-table-title mb-1">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        Daftar Vendor
                    </h6>

                    <div class="vendor-table-subtitle">
                        Informasi vendor dan status pengelolaan.
                    </div>
                </div>

                <span class="vendor-count">
                    <i class="bi bi-shop"></i>
                    {{ $vendors->count() }} vendor
                </span>

            </div>

            <div class="table-responsive">
                <table class="table vendor-table">

                    <thead>
                        <tr>
                            <th class="text-center">No.</th>
                            <th>Nama Vendor</th>
                            <th>PJ</th>
                            <th>Kontak</th>
                            <th>Rekening Vendor</th>
                            <th class="text-center">Jumlah Barang</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                @forelse($vendors as $vendor)
                    <tr>

                        {{-- Nomor --}}
                        <td class="text-center">
                            <span class="vendor-number">
                                {{ $loop->iteration }}
                            </span>
                        </td>

                        {{-- Nama vendor --}}
                        <td>
                            <div class="vendor-name">
                                {{ $vendor->name }}
                            </div>

                            @if($vendor->address)
                                <div class="vendor-address">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $vendor->address }}
                                </div>
                            @else
                                <div class="vendor-address">
                                    Alamat belum diisi
                                </div>
                            @endif
                        </td>

                        {{-- PJ --}}
                        <td>
                            @forelse($vendor->purchaseResponsibles as $pj)
                                <div class="vendor-info-value mb-1">
                                    <i class="bi bi-person text-secondary me-1"></i>
                                    {{ $pj->name }}
                                </div>
                            @empty
                                <span class="text-muted small">
                                    Belum ada PJ
                                </span>
                            @endforelse
                        </td>

                        {{-- Kontak --}}
                        <td>
                            <div class="vendor-info-value">
                                {{ $vendor->contact_person ?? '-' }}
                            </div>

                            @if($vendor->phone)
                                <div class="vendor-contact-sub">
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $vendor->phone }}
                                </div>
                            @else
                                <div class="vendor-contact-sub">
                                    Telepon belum diisi
                                </div>
                            @endif
                        </td>

                        {{-- Rekening vendor --}}
                        <td>
                            @if($vendor->bank_name || $vendor->bank_account_number)
                                <div class="vendor-info-value">
                                    {{ $vendor->bank_name ?: 'Bank belum diisi' }}
                                </div>

                                <div class="vendor-contact-sub">
                                    {{ $vendor->bank_account_number ?: 'Nomor rekening belum diisi' }}
                                </div>

                                @if($vendor->bank_account_holder)
                                    <div class="vendor-contact-sub">
                                        a.n. {{ $vendor->bank_account_holder }}
                                    </div>
                                @endif
                            @else
                                <span class="text-muted small">
                                    Rekening belum diisi
                                </span>
                            @endif
                        </td>

                        {{-- Jumlah barang --}}
                        <td class="text-center">
                            <span class="vendor-count">
                                <i class="bi bi-box-seam"></i>
                                {{ $vendor->items_count }} barang
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <div class="vendor-actions">

                                {{-- Detail --}}
                                <a href="{{ route('vendors.show', $vendor) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="Lihat detail vendor">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('vendors.edit', $vendor) }}"
                                   class="btn btn-sm btn-outline-warning"
                                   title="Edit vendor">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('vendors.destroy', $vendor) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus vendor ini? Vendor yang dihapus tidak dapat dikembalikan.')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Hapus vendor">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7">
                            <div class="vendor-empty">

                                <div class="vendor-empty-icon">
                                    <i class="bi bi-shop"></i>
                                </div>

                                <div class="fw-semibold text-dark mb-1">
                                    @if(request('search'))
                                        Vendor tidak ditemukan
                                    @else
                                        Belum ada data vendor
                                    @endif
                                </div>

                                <div class="small mb-3">
                                    @if(request('search'))
                                        Coba ubah kata pencarian.
                                    @else
                                        Silakan tambahkan vendor baru untuk mulai mengelola data.
                                    @endif
                                </div>

                                @if(!request('search'))
                                    <a href="{{ route('vendors.create') }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Tambah Vendor
                                    </a>
                                @else
                                    <a href="{{ route('vendors.index') }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Reset Filter
                                    </a>
                                @endif

                            </div>
                        </td>
                    </tr>
                @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection