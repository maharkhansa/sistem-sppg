@extends('layouts.app')

@section('title', 'Sub Kas Operasional')

@push('styles')
<style>
    .petty-cash-page {
        padding-bottom: 2rem;
    }

    .petty-cash-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .petty-cash-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        height: 100%;
    }

    .summary-label {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .summary-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111827;
    }

    .petty-cash-table {
        vertical-align: middle;
        margin-bottom: 0;
    }

    .petty-cash-table thead th {
        background: #f8fafc;
        color: #374151;
        font-size: 0.85rem;
        white-space: nowrap;
        padding: 0.85rem;
    }

    .petty-cash-table tbody td {
        padding: 0.85rem;
    }

    .pj-name {
        font-weight: 600;
        color: #111827;
    }

    .balance-amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .balance-status {
        display: inline-block;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-available {
        background: #dcfce7;
        color: #166534;
    }

    .status-empty {
        background: #f3f4f6;
        color: #4b5563;
    }

    .status-minus {
        background: #fee2e2;
        color: #991b1b;
    }

    .search-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    @media (max-width: 576px) {
        .petty-cash-title {
            font-size: 1.25rem;
        }

        .summary-value {
            font-size: 1.1rem;
        }

        .petty-cash-header {
            align-items: flex-start !important;
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container petty-cash-page">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 petty-cash-header">
        <div>
            <h1 class="petty-cash-title">Sub Kas Operasional</h1>
            <div class="petty-cash-subtitle">
                Ringkasan saldo kas kecil setiap PJ operasional.
            </div>
        </div>

        <a href="{{ route('petty-cash.top-up.create') }}"
           class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Tambah Top Up
        </a>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show"
             role="alert">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup">
            </button>
        </div>
    @endif

    {{-- Summary --}}
    @php
        $totalPj = $responsibles->total();

        $totalAvailable = 0;
        $totalMinus = 0;

        foreach ($balances as $balanceValue) {
            $balanceValue = (float) $balanceValue;

            if ($balanceValue > 0) {
                $totalAvailable += $balanceValue;
            } elseif ($balanceValue < 0) {
                $totalMinus += abs($balanceValue);
            }
        }
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="summary-card p-3">
                <div class="summary-label">Total PJ Sub Kas</div>
                <div class="summary-value">
                    {{ number_format($totalPj, 0, ',', '.') }} PJ
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="summary-card p-3">
                <div class="summary-label">Total Saldo Positif</div>
                <div class="summary-value text-success">
                Rp {{ number_format($totalAvailable, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="summary-card p-3">
                <div class="summary-label">Total Saldo Minus</div>
                <div class="summary-value text-danger">
                Rp {{ number_format($totalMinus, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="search-card p-3 mb-3">
        <form method="GET" action="{{ route('petty-cash.index') }}">
            <label for="search" class="form-label fw-semibold">
                Cari PJ Sub Kas
            </label>

            <div class="input-group">
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    placeholder="Masukkan nama PJ..."
                    value="{{ request('search') }}"
                >

                <button type="submit" class="btn btn-secondary">
                    Cari
                </button>

                @if (request('search'))
                    <a href="{{ route('petty-cash.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover petty-cash-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No.</th>
                        <th>Nama PJ</th>
                        <th class="text-end">Saldo Sub Kas</th>
                        <th class="text-center">Status Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($responsibles as $responsible)
                        @php
                            $balance = (float) (
                                $balances[$responsible->id] ?? 0
                            );
                        @endphp

                        <tr>
                            <td class="text-muted">
                                {{ $responsibles->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <span class="pj-name">
                                    {{ $responsible->name }}
                                </span>
                            </td>

                            <td class="text-end">
                                <span class="balance-amount {{ $balance < 0 ? 'text-danger' : '' }}">
                                    Rp {{ number_format($balance, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if ($balance < 0)
                                    <span class="balance-status status-minus">
                                        Saldo Minus
                                    </span>
                                @elseif ($balance == 0)
                                    <span class="balance-status status-empty">
                                        Saldo Habis
                                    </span>
                                @else
                                    <span class="balance-status status-available">
                                        Saldo Tersedia
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    Belum ada data PJ Sub Kas.
                                </div>

                                <a href="{{ route('petty-cash.top-up.create') }}"
                                   class="btn btn-sm btn-primary">
                                    Tambah Top Up
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="text-muted small">
            @if ($responsibles->total() > 0)
                Menampilkan {{ $responsibles->firstItem() }}
                sampai {{ $responsibles->lastItem() }}
                dari {{ $responsibles->total() }} PJ
            @else
                Tidak ada data
            @endif
        </div>

        {{ $responsibles->links() }}
    </div>

</div>
@endsection