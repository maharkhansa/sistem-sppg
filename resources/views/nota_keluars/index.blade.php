@extends('layouts.app')

@section('title', 'Nota Keluar')

@push('styles')
<style>
    .nota-page {
        padding: 10px 0;
        color: #222;
    }

    .nota-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .nota-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }

    .nota-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        overflow-x: auto;
    }

    .nota-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .nota-table th,
    .nota-table td {
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 13px;
        vertical-align: middle;
    }

    .nota-table th {
        background: #f1f3f5;
        text-align: center;
        white-space: nowrap;
    }

    .nota-table .text-center {
        text-align: center;
    }

    .nota-table .text-right {
        text-align: right;
        white-space: nowrap;
    }

    .nota-btn {
        display: inline-block;
        padding: 9px 14px;
        border-radius: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 13px;
        white-space: nowrap;
    }

    .nota-btn-back {
        background: #6c757d;
        color: #fff;
    }

    .nota-btn-back:hover {
        background: #565e64;
        color: #fff;
    }

    .nota-btn-view {
        background: #198754;
        color: #fff;
    }

    .nota-btn-view:hover {
        background: #146c43;
        color: #fff;
    }

    .nota-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }

    .nota-status-draft {
        background: #fff3cd;
        color: #664d03;
    }

    .nota-status-approved {
        background: #d1e7dd;
        color: #0f5132;
    }

    .nota-empty {
        padding: 24px !important;
        text-align: center;
        color: #6c757d;
    }

    @media (max-width: 576px) {
        .nota-header {
            align-items: flex-start;
        }

        .nota-header h1 {
            font-size: 21px;
        }

        .nota-card {
            padding: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid nota-page">

    <div class="nota-header">
        <h1>📝 Nota Keluar</h1>

        <a href="{{ route('stock-transactions.out') }}"
           class="nota-btn nota-btn-back">
            ← Kembali ke Barang Keluar
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    <div class="nota-card">
        <table class="nota-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Nota</th>
                    <th>Tanggal</th>
                    <th>SPPG / Dapur</th>
                    <th>Nomor OUT</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($notaKeluars as $index => $nota)
                    <tr>
                        <td class="text-center">
                            {{ $index + 1 }}
                        </td>

                        <td>
                            {{ $nota->nota_number ?? '-' }}
                        </td>

                        <td>
                            {{ $nota->nota_date
                                ? \Carbon\Carbon::parse($nota->nota_date)->translatedFormat('d F Y')
                                : '-' }}
                        </td>

                        <td>
                            {{ $nota->kitchen?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $nota->stockTransaction?->transaction_number ?? '-' }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format((float) ($nota->total_amount ?? 0), 0, ',', '.') }}
                        </td>

                        <td class="text-center">
                            @if ($nota->status === 'APPROVED')
                                <span class="nota-status nota-status-approved">
                                    APPROVED
                                </span>
                            @else
                                <span class="nota-status nota-status-draft">
                                    {{ $nota->status ?? 'DRAFT' }}
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            <a href="{{ route('nota-keluars.show', $nota->id) }}"
                               class="nota-btn nota-btn-view">
                                👁 Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="nota-empty">
                            Belum ada Nota Keluar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection