@extends('layouts.app')

@section('title', 'Rekap PO Mingguan')

@push('styles')
<style>
    /* =========================
       HEADER
    ========================= */
    .recap-header {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }

    .recap-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .recap-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.5;
    }

    /* =========================
       CARD
       ========================= */
    .filter-card,
    .table-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
    }

    .filter-card .card-body,
    .table-card .card-body {
        padding: 1rem;
    }

    .filter-card .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.35rem;
        color: #374151;
    }

    .filter-card .form-control {
        font-size: 0.85rem;
        min-height: 36px;
    }

    .filter-card .btn {
        font-size: 0.82rem;
        min-height: 36px;
        white-space: nowrap;
    }

    .period-label {
        font-size: 0.78rem;
        color: #6b7280;
    }

    /* =========================
       TABEL REKAP
       ========================= */
    .recap-table {
        width: 100%;
        min-width: 760px;
        margin-bottom: 0;
        border-color: #dbe1e8;
        table-layout: fixed;
    }

    .recap-table th,
    .recap-table td {
        padding: 0.4rem 0.45rem;
        vertical-align: middle;
        border-color: #dbe1e8;
    }

    /* Header tabel */
    .recap-table thead th {
        background: #f3f6fa;
        color: #374151;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.3;
    }

    /* Header tanggal */
    .recap-table thead .date-header {
        background: #e8eef6;
        color: #1f2937;
        text-align: center;
        font-size: 0.72rem;
        padding: 0.5rem 0.3rem;
    }

    /* Header nama dapur */
    .recap-table thead .kitchen-header {
        background: #f8fafc;
        font-size: 0.66rem;
        font-weight: 600;
        text-align: center;
        white-space: normal;
        width: 65px;
        min-width: 55px;
        max-width: 80px;
        padding: 0.35rem 0.2rem;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    /* Kolom vendor, bahan, satuan */
    .recap-table .fixed-column {
        background: #fff;
        text-align: left;
    }

    .recap-table tbody td {
        font-size: 0.76rem;
        line-height: 1.35;
    }

    .recap-table tbody tr:hover td {
        background: #f8fafc;
    }

    .recap-table .vendor-cell {
        width: 125px;
        min-width: 110px;
        overflow-wrap: anywhere;
    }

    .recap-table .item-cell {
        width: 155px;
        min-width: 140px;
        overflow-wrap: anywhere;
    }

    .recap-table .unit-cell {
        width: 55px;
        min-width: 50px;
        text-align: center;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    /* Jumlah barang */
    .recap-table .quantity-cell {
        text-align: right;
        font-size: 0.74rem;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .recap-table .empty-cell {
        color: #9ca3af;
        text-align: center;
        font-size: 0.72rem;
    }

    /* =========================
       EMPTY STATE
       ========================= */
    .empty-state {
        padding: 2rem 1rem !important;
        color: #6b7280;
        text-align: center;
        font-size: 0.85rem;
    }

    /* =========================
       RESPONSIVE
       ========================= */
    @media (max-width: 767.98px) {
        .recap-title {
            font-size: 1.1rem;
        }

        .recap-subtitle {
            font-size: 0.76rem;
        }

        .recap-header .btn {
            font-size: 0.78rem;
            padding: 0.4rem 0.65rem;
        }

        .filter-card .card-body,
        .table-card .card-body {
            padding: 0.7rem;
        }

        .filter-card .form-control {
            font-size: 0.8rem;
        }

        .filter-card .btn {
            width: 100%;
        }

        .period-label {
            font-size: 0.73rem;
        }

        .recap-table {
            min-width: 680px;
        }

        .recap-table th,
        .recap-table td {
            padding: 0.32rem 0.35rem;
        }

        .recap-table thead th {
            font-size: 0.65rem;
        }

        .recap-table thead .date-header {
            font-size: 0.65rem;
        }

        .recap-table thead .kitchen-header {
            font-size: 0.6rem;
            width: 55px;
            min-width: 48px;
            max-width: 65px;
            padding: 0.3rem 0.15rem;
        }

        .recap-table tbody td {
            font-size: 0.68rem;
        }

        .recap-table .vendor-cell {
            width: 105px;
            min-width: 95px;
        }

        .recap-table .item-cell {
            width: 130px;
            min-width: 120px;
        }

        .recap-table .unit-cell {
            width: 45px;
            min-width: 40px;
        }

        .recap-table .quantity-cell {
            font-size: 0.67rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Header halaman --}}
    <div class="recap-header mb-3">
        <div class="d-flex flex-wrap justify-content-between
                    align-items-center gap-3 p-3">

            <div>
                <h4 class="recap-title">
                    Rekap PO Mingguan
                </h4>

                <div class="recap-subtitle">
                    Rekap kebutuhan bahan kering seluruh dapur,
                    Senin sampai Kamis.
                </div>
            </div>

            <a href="{{ route('purchase-orders.index') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke Daftar PO
            </a>

        </div>
    </div>

    {{-- Filter rentang tanggal --}}
    <div class="filter-card mb-3">
        <div class="card-body">

            <form method="GET"
                  action="{{ route('purchase-orders.weekly-recap') }}"
                  class="row g-2 align-items-end">

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="start_date" class="form-label fw-semibold">
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control"
                        value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="end_date" class="form-label fw-semibold">
                        Tanggal Sampai
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control"
                        value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                        min="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>
                        Tampilkan Rekap
                    </button>
                </div>

            </form>

            <div class="period-label mt-2">
                <i class="bi bi-calendar-range me-1"></i>
                Periode:
                <strong class="text-dark">
                    {{ $startDate->translatedFormat('d M Y') }}
                    -
                    {{ $endDate->translatedFormat('d M Y') }}
                </strong>
            </div>

        </div>
    </div>

    {{-- Tabel rekap --}}
    <div class="table-card">
        <div class="card-body">

            @if ($kitchens->isEmpty())

                <div class="empty-state">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>

                    <div class="fw-semibold">
                        Tidak ada PO pada periode ini.
                    </div>

                    <div class="small mt-1">
                        Pilih periode lain untuk melihat rekap.
                    </div>
                </div>

            @elseif ($recap->isEmpty())

                <div class="empty-state">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                    <div class="fw-semibold">
                        Tidak ada kebutuhan bahan pada periode ini.
                    </div>

                    <div class="small mt-1">
                        PO yang berisi kebutuhan bahan kering akan ditampilkan di sini.
                    </div>
                </div>

            @else

                @php
                    /*
                     * Urutkan nama bahan A-Z.
                     * Tidak mengubah data PO harian.
                     */
                    $sortedRecap = collect($recap)
                        ->sortBy(
                            fn ($row) => $row['item'] ?? '',
                            SORT_NATURAL | SORT_FLAG_CASE
                        )
                        ->values();

                    /*
                     * Nama pendek khusus untuk tampilan rekap.
                     * Nama asli dari data Kitchen tetap digunakan
                     * sebagai identitas dan relasi.
                     */
                    $kitchenNameMap = [
                        'tempuran' => 'Tempuran',
                        'sidowayah' => 'Sidowayah',
                        'ngrundul' => 'Ngrundul',
                        'kota magelang magelang utara kedungsari 2' => 'Potrobangsan',
                        'adikarto' => 'Adikarto',
                        'petanjuangan 2' => 'Petanjuangan 2',
                        'awitan' => 'Awitan',
                        'kalidoni 2' => 'Kalidoni 2',
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle recap-table">

                        <thead>
                            {{-- Header tanggal --}}
                            <tr>
                                <th rowspan="2" class="fixed-column vendor-cell">
                                    Nama Vendor
                                </th>

                                <th rowspan="2" class="fixed-column item-cell">
                                    Nama Bahan
                                </th>

                                <th rowspan="2" class="fixed-column unit-cell">
                                    Satuan
                                </th>

                                @foreach ($days as $day)
                                    <th
                                        colspan="{{ $kitchens->count() }}"
                                        class="date-header"
                                    >
                                        {{ $day->translatedFormat('l, d M Y') }}
                                    </th>
                                @endforeach
                            </tr>

                            {{-- Subheader nama dapur --}}
                            <tr>
                                @foreach ($days as $day)
                                    @foreach ($kitchens as $kitchen)

                                        @php
                                            /*
                                             * Ambil nama asli dari data Kitchen.
                                             * Tidak mengubah nama di database.
                                             */
                                            $originalKitchenName = trim(
                                                $kitchen->display_name
                                                ?: $kitchen->name
                                                ?: ''
                                            );

                                            /*
                                             * Hapus awalan SPPG untuk
                                             * kebutuhan tampilan saja.
                                             */
                                            $normalizedKitchenName = preg_replace(
                                                '/^SPPG\s+/i',
                                                '',
                                                $originalKitchenName
                                            );

                                            $normalizedLower = mb_strtolower(
                                                trim($normalizedKitchenName)
                                            );

                                            /*
                                             * Default: nama asli setelah
                                             * awalan SPPG dihapus.
                                             */
                                            $kitchenName = $normalizedKitchenName;

                                            /*
                                             * Cocokkan nama asli dengan
                                             * daftar nama pendek.
                                             */
                                            foreach ($kitchenNameMap as $keyword => $shortName) {
                                                if (
                                                    str_contains(
                                                        $normalizedLower,
                                                        $keyword
                                                    )
                                                ) {
                                                    $kitchenName = $shortName;
                                                    break;
                                                }
                                            }
                                        @endphp

                                        <th class="kitchen-header">
                                            {{ $kitchenName }}
                                        </th>

                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($sortedRecap as $row)
                                <tr>
                                    <td class="fixed-column vendor-cell">
                                        {{ $row['vendor'] }}
                                    </td>

                                    <td class="fixed-column item-cell">
                                        {{ $row['item'] }}
                                    </td>

                                    <td class="fixed-column unit-cell">
                                        {{ $row['unit'] }}
                                    </td>

                                    @foreach ($days as $day)
                                        @foreach ($kitchens as $kitchen)

                                            @php
                                                /*
                                                 * Tetap gunakan ID Kitchen
                                                 * dari PO untuk mengambil jumlah.
                                                 */
                                                $cellKey = $day->format('Y-m-d')
                                                    . '|'
                                                    . $kitchen->id;

                                                $quantity = $row['quantities'][$cellKey]
                                                    ?? null;
                                            @endphp

                                            @if ($quantity !== null && $quantity > 0)

                                                <td class="quantity-cell">
                                                    {{ rtrim(rtrim(number_format(
                                                        $quantity,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ), '0'), ',') }}
                                                </td>

                                            @else

                                                <td class="empty-cell">-</td>

                                            @endif

                                        @endforeach
                                    @endforeach

                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @endif

        </div>
    </div>

</div>
@endsection