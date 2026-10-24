@extends('layouts.app')

@section('title', 'Detail Vendor')

@section('content')
<style>
    .vendor-detail-page {
        --vendor-primary: #2563eb;
        --vendor-border: #e5e7eb;
        --vendor-muted: #64748b;
        color: #1e293b;
    }

    .vendor-detail-page .page-heading {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: #0f172a;
    }

    .vendor-detail-page .page-subtitle {
        color: var(--vendor-muted);
        font-size: .925rem;
    }

    .vendor-detail-page .vendor-card {
        border: 1px solid var(--vendor-border);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .025);
    }

    .vendor-detail-page .vendor-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--vendor-border);
        background: #fff;
    }

    .vendor-detail-page .vendor-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .vendor-detail-page .vendor-card-body {
        padding: 22px;
    }

    .vendor-detail-page .vendor-avatar {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #eff6ff;
        color: #2563eb;
        font-size: 1.35rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .vendor-detail-page .vendor-name {
        font-size: 1.3rem;
        font-weight: 750;
        color: #0f172a;
        margin-bottom: 6px;
        overflow-wrap: anywhere;
    }

    .vendor-detail-page .vendor-label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        color: var(--vendor-muted);
        margin-bottom: 6px;
    }

    .vendor-detail-page .vendor-value {
        font-size: .925rem;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .vendor-detail-page .vendor-info-item {
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .vendor-detail-page .vendor-info-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .vendor-detail-page .vendor-info-item:first-child {
        padding-top: 0;
    }

    .vendor-detail-page .vendor-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 11px;
        border-radius: 30px;
        font-size: .8rem;
        font-weight: 700;
    }

    .vendor-detail-page .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .vendor-detail-page .status-inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .vendor-detail-page .vendor-pj {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .vendor-detail-page .vendor-pj:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .vendor-detail-page .vendor-pj:first-child {
        padding-top: 0;
    }

    .vendor-detail-page .pj-avatar {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: .85rem;
    }

    .vendor-detail-page .vendor-table {
        margin-bottom: 0;
    }

    .vendor-detail-page .vendor-table thead th {
        padding: 14px 18px;
        background: #f8fafc;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        border-bottom: 1px solid var(--vendor-border);
    }

    .vendor-detail-page .vendor-table tbody td {
        padding: 15px 18px;
        font-size: .9rem;
        vertical-align: middle;
        border-color: #f1f5f9;
    }

    .vendor-detail-page .vendor-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .vendor-detail-page .item-code {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        font-size: .8rem;
        font-weight: 600;
    }

    .vendor-detail-page .vendor-count {
        padding: 5px 10px;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .8rem;
        font-weight: 700;
    }

    .vendor-detail-page .vendor-empty {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
    }

    .vendor-detail-page .vendor-empty-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        border-radius: 14px;
        background: #f1f5f9;
        font-size: 1.4rem;
    }

    @media (max-width: 767.98px) {
        .vendor-detail-page .vendor-card-body {
            padding: 17px;
        }

        .vendor-detail-page .page-heading {
            font-size: 1.25rem;
        }

        .vendor-detail-page .vendor-name {
            font-size: 1.1rem;
        }

        .vendor-detail-page .vendor-avatar {
            width: 48px;
            height: 48px;
            font-size: 1.1rem;
        }
    }
</style>

<div class="container-fluid vendor-detail-page pb-4">

    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <div class="page-heading">Detail Vendor</div>
            <p class="page-subtitle mb-0 mt-1">
                Informasi lengkap dan daftar barang dari vendor.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('vendors.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

            <a
                href="{{ route('vendors.edit', $vendor) }}"
                class="btn btn-warning"
            >
                <i class="bi bi-pencil-square me-1"></i>
                Edit Vendor
            </a>
        </div>
    </div>

    {{-- Informasi utama vendor --}}
    <div class="vendor-card mb-4">
        <div class="vendor-card-body">

            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">

                <div class="vendor-avatar">
                    {{ mb_substr($vendor->name, 0, 1) }}
                </div>

                <div class="flex-grow-1">
                    <div class="vendor-name">
                        {{ $vendor->name }}
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">

                        @if($vendor->is_active)
                            <span class="vendor-status status-active">
                                <i class="bi bi-check-circle-fill"></i>
                                Aktif
                            </span>
                        @else
                            <span class="vendor-status status-inactive">
                                <i class="bi bi-pause-circle-fill"></i>
                                Nonaktif
                            </span>
                        @endif

                        <span class="text-muted small">
                            ID Vendor: {{ $vendor->id }}
                        </span>
                    </div>
                </div>
            </div>

            <hr class="border-light-subtle">

            <div class="row">

                <div class="col-12 col-md-6">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            <i class="bi bi-geo-alt me-1"></i>
                            Alamat
                        </span>

                        <div class="vendor-value">
                            {{ $vendor->address ?: 'Alamat belum diisi' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            <i class="bi bi-person me-1"></i>
                            Kontak Person
                        </span>

                        <div class="vendor-value">
                            {{ $vendor->contact_person ?: 'Kontak belum diisi' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            <i class="bi bi-telephone me-1"></i>
                            Nomor Telepon
                        </span>

                        <div class="vendor-value">
                            @if($vendor->phone)
                                <a
                                    href="tel:{{ $vendor->phone }}"
                                    class="text-decoration-none"
                                >
                                    {{ $vendor->phone }}
                                </a>
                            @else
                                Nomor telepon belum diisi
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Informasi rekening dan nota vendor --}}
    <div class="vendor-card mb-4">
        <div class="vendor-card-header">
            <h5 class="vendor-card-title">
                <i class="bi bi-bank me-2 text-primary"></i>
                Rekening dan Nota Vendor
            </h5>
        </div>

        <div class="vendor-card-body">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            Nama Bank
                        </span>

                        <div class="vendor-value">
                            {{ $vendor->bank_name ?: 'Belum diisi' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            Nomor Rekening
                        </span>

                        <div class="vendor-value">
                            {{ $vendor->bank_account_number ?: 'Belum diisi' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="vendor-info-item">
                        <span class="vendor-label">
                            Nama Pemilik Rekening
                        </span>

                        <div class="vendor-value">
                            {{ $vendor->bank_account_holder ?: 'Belum diisi' }}
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="vendor-info-item">
                <span class="vendor-label">
                    Nota atau Bukti Vendor
                </span>

                @if($vendor->receipt_path)
                    <div class="vendor-value">
                        <a
                            href="{{ asset('storage/' . $vendor->receipt_path) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Buka Nota Vendor
                        </a>
                    </div>
                @else
                    <div class="vendor-value text-muted">
                        Belum ada nota yang diunggah.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PJ Vendor --}}
    <div class="vendor-card mb-4">

        <div class="vendor-card-header">
            <div class="d-flex justify-content-between
                        align-items-center gap-2">

                <h5 class="vendor-card-title">
                    <i class="bi bi-people me-2 text-primary"></i>
                    Penanggung Jawab Vendor
                </h5>

                <span class="vendor-count">
                    {{ $vendor->purchaseResponsibles->count() }} PJ
                </span>
            </div>
        </div>

        <div class="vendor-card-body">

            @forelse($vendor->purchaseResponsibles as $pj)
                <div class="vendor-pj">

                    <div class="pj-avatar">
                        {{ mb_substr($pj->name, 0, 1) }}
                    </div>

                    <div class="vendor-value fw-semibold">
                        {{ $pj->name }}
                    </div>

                </div>
            @empty
                <div class="vendor-empty">
                    <div class="vendor-empty-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <div class="fw-semibold text-dark mb-1">
                        Belum ada PJ vendor
                    </div>

                    <div class="small">
                        Tambahkan penanggung jawab melalui menu Edit Vendor.
                    </div>
                </div>
            @endforelse

        </div>
    </div>

    {{-- Daftar barang vendor --}}
    <div class="vendor-card">

        <div class="vendor-card-header">
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2">

                <div>
                    <h5 class="vendor-card-title">
                        <i class="bi bi-box-seam me-2 text-primary"></i>
                        Barang Vendor
                    </h5>

                    <p class="text-muted small mb-0 mt-1">
                        Daftar barang yang terhubung dengan vendor ini.
                    </p>
                </div>

                <span class="vendor-count">
                    {{ $vendor->items->count() }} barang
                </span>
                </div>
        </div>

        <div class="table-responsive">
            <table class="table vendor-table">

                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center">
                            No.
                        </th>

                        <th style="width: 180px;">
                            Kode Barang
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th style="width: 140px;">
                            Satuan
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($vendor->items as $item)
                        <tr>
                            <td class="text-center text-muted">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                @if($item->code)
                                    <span class="item-code">
                                        {{ $item->code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="fw-semibold">
                                {{ $item->name }}
                            </td>

                            <td>
                                {{ $item->unit ?: '-' }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="vendor-empty">
                                    <div class="vendor-empty-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                    <div class="fw-semibold text-dark mb-1">
                                        Belum ada barang terhubung
                                    </div>

                                    <div class="small">
                                        Barang untuk vendor ini belum ditambahkan.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection