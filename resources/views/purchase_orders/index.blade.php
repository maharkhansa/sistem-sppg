@extends('layouts.app')

@section('title', 'Purchase Order | Sistem SPPG')

@push('styles')
<style>
    .po-page {
        color: #333;
        padding: 24px;
    }

    .po-page .page-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Header */
    .po-page .header-action {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 24px;
    }

    .po-page .page-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
    }

    .po-page .page-subtitle {
        color: #6c757d;
        margin: 6px 0 0;
        font-size: 14px;
    }

    .po-page .header-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Tombol umum */
    .po-page .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 9px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
        line-height: 1.4;
    }

    .po-page .btn-add,
    .po-page .btn-success {
        background: #198754;
        color: #fff;
    }

    .po-page .btn-print {
        background: #0d6efd;
        color: #fff;
    }

    .po-page .btn-process {
        background: #fd7e14;
        color: #fff;
    }

    .po-page .btn-info {
        background: #0dcaf0;
        color: #052c36;
    }

    .po-page .btn-reset {
        background: #6c757d;
        color: #fff;
    }

    .po-page .btn:hover {
        filter: brightness(.94);
    }

    /* Pesan */
    .po-page .alert {
        padding: 13px 16px;
        border-radius: 7px;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }

    .po-page .alert-success {
        background: #d1e7dd;
        color: #0f5132;
        border-color: #badbcc;
    }

    .po-page .alert-danger {
        background: #f8d7da;
        color: #842029;
        border-color: #f5c2c7;
    }

    /* Filter */
    .po-page .filter-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025);
    }

    .po-page .filter-title {
        font-size: 17px;
        font-weight: 700;
        margin: 0 0 18px;
    }

    .po-page .filter-grid {
        display: grid;
        grid-template-columns: minmax(220px, 2fr) repeat(3, minmax(140px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .po-page .filter-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .po-page .filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #495057;
    }

    .po-page .filter-group input,
    .po-page .filter-group select {
        width: 100%;
        min-height: 42px;
        box-sizing: border-box;
        padding: 9px 11px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 14px;
        background: #fff;
        color: #333;
    }

    .po-page .filter-group input:focus,
    .po-page .filter-group select:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, .13);
    }

    .po-page .filter-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .po-page .result-info {
        margin-left: auto;
        color: #6c757d;
        font-size: 13px;
    }

    .po-page .date-error {
        display: none;
        color: #842029;
        font-size: 13px;
        margin: 12px 0 0;
    }

    /* Status */
    .po-page .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .po-page .status-pending {
        background: #fff3cd;
        color: #664d03;
    }

    .po-page .status-approved {
        background: #d1e7dd;
        color: #0f5132;
    }

    .po-page .status-rejected {
        background: #f8d7da;
        color: #842029;
    }

    .po-page .status-processed {
        background: #cfe2ff;
        color: #084298;
    }

    /* Tampilan kosong */
    .po-page .empty-state,
    .po-page .empty-filter {
        padding: 36px 20px;
        text-align: center;
        color: #6c757d;
        background: #fff;
        border: 1px dashed #ced4da;
        border-radius: 9px;
        margin-bottom: 24px;
    }

    .po-page .empty-filter {
        display: none;
    }

    .po-page .empty-state strong,
    .po-page .empty-filter strong {
        display: block;
        color: #495057;
        margin-bottom: 7px;
    }

    /* Tabel */
    .po-page .po-table-wrapper {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025);
    }

    .po-page .po-list-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
        table-layout: fixed;
    }

    .po-page .po-list-table thead th {
        background: #f1f5f9;
        color: #334155;
        padding: 10px 8px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    .po-page .po-list-table tbody td {
        padding: 10px 8px;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .po-page .po-list-table tbody tr:last-child td {
        border-bottom: none;
    }

    .po-page .po-list-table tbody tr:hover {
        background: #f8fafc;
    }

    .po-page .po-number {
        font-weight: 700;
        color: #1d4ed8;
        white-space: nowrap;
    }

    .po-page .po-kitchen {
        font-weight: 600;
        color: #334155;
    }

    .po-page .po-date {
        white-space: nowrap;
    }

    /* Kolom Aksi */
    .po-page .po-list-table th:last-child,
    .po-page .po-list-table td:last-child {
        width: 280px;
        padding-left: 8px;
        padding-right: 8px;
        text-align: center;
    }

    .po-page .po-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        justify-content: center;
        min-width: 0;
    }

    /* Tombol ikon */
    .po-page .po-list-table .btn-icon {
        width: 30px;
        height: 30px;
        min-width: 30px;
        flex: 0 0 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 6px;
        line-height: 1;
    }

    .po-page .po-list-table .btn-icon svg {
        width: 16px;
        height: 16px;
        display: block;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .po-page .po-list-table .btn-icon:hover {
        transform: translateY(-1px);
        filter: brightness(.93);
    }

    .po-page .po-list-table .btn-detail {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #bae6fd;
    }

    .po-page .po-list-table .btn-delivery {
        background: #e0e7ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .po-page .po-list-table .btn-edit {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }

    .po-page .po-list-table .btn-delete {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .po-page .po-list-table .btn-process {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .po-page .po-list-table .btn-invoice {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    /* Form aksi biasa */
    .po-page .po-list-table .action-form {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        margin: 0;
    }

    /* Form proses: label di atas, tanggal dan tombol di bawah */
    .po-page .po-list-table .process-form {
        display: flex;
        flex: 0 0 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin: 5px 0 0;
        padding: 8px 0 0;
        border-top: 1px solid #e5e7eb;
    }

    .po-page .process-form .invoice-date-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        margin: 0;
    }

    .po-page .process-form .invoice-date-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
    }

    .po-page .process-form .invoice-date-input {
        width: 170px;
        max-width: 100%;
        min-width: 0;
        height: 32px;
        box-sizing: border-box;
        padding: 4px 6px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: #334155;
        font-size: 12px;
    }

    .po-page .process-form .btn-process {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
    }

    /* Filter dan tabel di layar menengah */
    @media (max-width: 1100px) {
        .po-page .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Mobile */
    @media (max-width: 768px) {
        .po-page {
            padding: 15px;
        }

        .po-page .page-title {
            font-size: 22px;
        }

        .po-page .filter-grid {
            grid-template-columns: 1fr;
        }

        .po-page .filter-card {
            padding: 15px;
        }

        .po-page .result-info {
            width: 100%;
            margin-left: 0;
        }

        .po-page .header-buttons {
            width: 100%;
        }

        .po-page .header-buttons .btn {
            flex: 1;
        }
    }

    /* Cetak */
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
        }

        .po-page {
            padding: 0 !important;
            color: #000;
        }

        .po-page .no-print {
            display: none !important;
        }

        .po-page .page-container {
            max-width: 100%;
        }

        .po-page .po-table-wrapper {
            box-shadow: none;
            border: 1px solid #000;
            border-radius: 0;
            overflow: visible;
        }

        .po-page .po-list-table {
            min-width: 0;
            table-layout: auto;
        }

        .po-page .po-list-table th,
        .po-page .po-list-table td {
            border: 1px solid #000;
        }
    }
</style>
@endpush

@section('content')
<div class="po-page">
    <div class="page-container">

        {{-- HEADER HALAMAN --}}
        <div class="header-action">
            <div>
                <h1 class="page-title">Purchase Order</h1>
                <p class="page-subtitle">
                    Daftar pesanan barang untuk kebutuhan dapur SPPG.
                </p>
            </div>

            <div class="header-buttons no-print">
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-add">
                    + Tambah Purchase Order
                </a>

                <button onclick="window.print()" type="button" class="btn btn-print">
                    Cetak
                </button>
            </div>
        </div>

        {{-- PESAN --}}
        @if (session('error'))
            <div class="alert alert-danger no-print" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PENCARIAN DAN FILTER --}}
        <div class="filter-card no-print">
            <h2 class="filter-title">Pencarian dan Filter Purchase Order</h2>

            <div class="filter-grid">
                <div class="filter-group">
                    <label for="searchPO">Cari Purchase Order</label>
                    <input
                        type="search"
                        id="searchPO"
                        placeholder="Nomor PO, nama SPPG, ID SPPG, catatan..."
                        autocomplete="off"
                    >
                </div>

                <div class="filter-group">
                    <label for="filterStatus">Status PO</label>
                    <select id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="processed">Processed</option>
                        <option value="received">Received</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filterTanggalMulai">Tanggal PO Dari</label>
                    <input type="date" id="filterTanggalMulai">
                </div>

                <div class="filter-group">
                    <label for="filterTanggalAkhir">Tanggal PO Sampai</label>
                    <input type="date" id="filterTanggalAkhir">
                </div>
            </div>

            <div class="filter-actions">
                <button type="button" class="btn btn-reset" id="resetFilter">
                    Reset Filter
                </button>

                <span class="result-info" id="resultInfo" aria-live="polite">
                    Memuat daftar Purchase Order...
                </span>
            </div>

            <p id="tanggalError" class="date-error">
                Tanggal awal tidak boleh lebih besar dari tanggal akhir.
            </p>
        </div>

        {{-- DAFTAR PO --}}
        <div id="purchaseOrderList">
            <div class="po-table-wrapper">
                <table class="po-list-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">No. Purchase Order</th>
                            <th style="width: 16%;">Tanggal PO</th>
                            <th style="width: 42%;">Nama Dapur / SPPG</th>
                            <th class="no-print">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
            @forelse ($purchaseOrders as $purchaseOrder)
                @php
                    $statusText = strtoupper(trim((string) $purchaseOrder->status));

                    $tanggalPO = $purchaseOrder->po_date
                        ? \Illuminate\Support\Carbon::parse($purchaseOrder->po_date)->format('Y-m-d')
                        : '';

                    $searchText = implode(' ', array_filter([
                        $purchaseOrder->po_number,
                        $purchaseOrder->kitchen?->id_sppg,
                        $purchaseOrder->kitchen?->name,
                        $purchaseOrder->notes,
                        $statusText,
                    ]));
                @endphp

                <tr
                    class="po-card"
                    data-search="{{ strtolower($searchText) }}"
                    data-status="{{ strtolower($statusText) }}"
                    data-date="{{ $tanggalPO }}"
                >
                    <td>
                        <span class="po-number">
                            {{ $purchaseOrder->po_number }}
                        </span>
                    </td>

                    <td class="po-date">
                        {{ $tanggalPO
                            ? \Illuminate\Support\Carbon::parse($tanggalPO)->format('d-m-Y')
                            : '-' }}
                    </td>

                    <td>
                        <div class="po-kitchen">
                            {{ $purchaseOrder->kitchen?->name ?? 'N/A' }}
                        </div>

                        @if ($purchaseOrder->kitchen?->id_sppg)
                            <small class="text-muted">
                                {{ $purchaseOrder->kitchen->id_sppg }}
                            </small>
                        @endif
                    </td>

                    <td class="no-print">
                        <div class="po-actions">

                            {{-- Detail --}}
                            <a
                                href="{{ route('purchase-orders.daftar-pesanan', $purchaseOrder->id) }}"
                                class="btn btn-icon btn-detail"
                                title="Detail Purchase Order"
                                aria-label="Detail Purchase Order {{ $purchaseOrder->po_number }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>

                            {{-- Delivery Note --}}
                            <a
                                href="{{ route('purchase-orders.delivery-note', $purchaseOrder->id) }}"
                                class="btn btn-icon btn-delivery"
                                title="Delivery Note"
                                aria-label="Delivery Note {{ $purchaseOrder->po_number }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <path d="M14 2v6h6"/>
                                    <path d="M8 13h8M8 17h8"/>
                                </svg>
                            </a>

                            @if ($statusText === 'RECEIVED')

                                {{-- Edit --}}
                                <a
                                    href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}"
                                    class="btn btn-icon btn-edit"
                                    title="Edit Purchase Order"
                                    aria-label="Edit Purchase Order {{ $purchaseOrder->po_number }}"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>
                                    </svg>
                                </a>

                                {{-- Hapus --}}
                                <form
                                    action="{{ route('purchase-orders.destroy', $purchaseOrder->id) }}"
                                    method="POST"
                                    class="action-form"
                                    onsubmit="return confirm('Yakin ingin menghapus PO {{ $purchaseOrder->po_number }} beserta detailnya?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-icon btn-delete"
                                        title="Hapus Purchase Order"
                                        aria-label="Hapus Purchase Order {{ $purchaseOrder->po_number }}"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v5M14 11v5"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Proses --}}
                                <form
                                    action="{{ route('purchase-orders.process', $purchaseOrder->id) }}"
                                    method="POST"
                                    class="action-form process-form"
                                    onsubmit="return confirm('Proses PO menjadi OUT? Stok akan berkurang dan Invoice dibuat otomatis.');"
                                >
                                    @csrf

                                    <label
                                        class="invoice-date-label"
                                        for="invoice_date_{{ $purchaseOrder->id }}"
                                    >
                                        Tanggal Invoice
                                    </label>

                                    <div class="invoice-date-row">
                                        <input
                                            type="date"
                                            name="invoice_date"
                                            id="invoice_date_{{ $purchaseOrder->id }}"
                                            class="invoice-date-input"
                                            value="{{ old('invoice_date', \Illuminate\Support\Carbon::parse($purchaseOrder->po_date)->format('Y-m-d')) }}"
                                            required
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-icon btn-process"
                                            title="Proses Purchase Order"
                                            aria-label="Proses Purchase Order {{ $purchaseOrder->po_number }}"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M5 12h14"/>
                                                <path d="m13 6 6 6-6 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </form>

                            @elseif ($statusText === 'PROCESSED')

                                <span class="status status-processed">
                                    Diproses
                                </span>

                                @if ($purchaseOrder->invoice)
                                    <a
                                        href="{{ route('invoices.show', $purchaseOrder->invoice->id) }}"
                                        class="btn btn-icon btn-invoice"
                                        title="Lihat Invoice"
                                        aria-label="Lihat Invoice {{ $purchaseOrder->po_number }}"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <path d="M14 2v6h6"/>
                                            <path d="M8 13h8M8 17h5"/>
                                        </svg>
                                    </a>
                                @endif

                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr id="emptyOriginal">
                    <td colspan="4">
                        <div class="empty-state" style="margin: 0;">
                            <strong>Belum ada Purchase Order.</strong>
                            <span>
                                Silakan tambah Purchase Order untuk memulai.
                            </span>
                        </div>
                    </td>
                </tr>
            @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PESAN HASIL FILTER KOSONG --}}
        <div id="emptyFilter" class="empty-filter">
            <strong>Purchase Order tidak ditemukan.</strong>
            <p style="margin-bottom: 0;">
                Coba ubah kata pencarian, status, atau rentang tanggal.
            </p>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchPO');
    const statusFilter = document.getElementById('filterStatus');
    const startDateInput = document.getElementById('filterTanggalMulai');
    const endDateInput = document.getElementById('filterTanggalAkhir');
    const resetButton = document.getElementById('resetFilter');
    const resultInfo = document.getElementById('resultInfo');
    const emptyFilter = document.getElementById('emptyFilter');
    const tanggalError = document.getElementById('tanggalError');

    const cards = Array.from(document.querySelectorAll('.po-page .po-card'));
    const totalPO = cards.length;

    function applyFilters() {
        const keyword = searchInput.value.trim().toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        const invalidDateRange =
            startDate !== '' &&
            endDate !== '' &&
            startDate > endDate;

        tanggalError.style.display = invalidDateRange ? 'block' : 'none';

        if (invalidDateRange) {
            cards.forEach(card => {
                card.style.display = 'none';
            });

            emptyFilter.style.display = totalPO > 0 ? 'block' : 'none';
            resultInfo.textContent = 'Rentang tanggal tidak valid';
            return;
        }

        let visibleCount = 0;

        cards.forEach(card => {
            const searchText = (card.dataset.search || '').toLowerCase();
            const status = (card.dataset.status || '').toLowerCase();
            const date = card.dataset.date || '';

            const matchesSearch =
                keyword === '' || searchText.includes(keyword);

            const matchesStatus =
                selectedStatus === '' || status === selectedStatus;

            const matchesStartDate =
                startDate === '' || (date !== '' && date >= startDate);

            const matchesEndDate =
                endDate === '' || (date !== '' && date <= endDate);

            const isVisible =
                matchesSearch &&
                matchesStatus &&
                matchesStartDate &&
                matchesEndDate;

            card.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
            }
        });

        emptyFilter.style.display =
            visibleCount === 0 && totalPO > 0 ? 'block' : 'none';

        if (totalPO === 0) {
            resultInfo.textContent = 'Belum ada Purchase Order';
        } else {
            resultInfo.textContent =
                'Menampilkan ' + visibleCount +
                ' dari ' + totalPO + ' Purchase Order';
        }
    }

    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    startDateInput.addEventListener('change', applyFilters);
    endDateInput.addEventListener('change', applyFilters);

    resetButton.addEventListener('click', function () {
        searchInput.value = '';
        statusFilter.value = '';
        startDateInput.value = '';
        endDateInput.value = '';

        applyFilters();
        searchInput.focus();
    });

    applyFilters();
});
</script>
@endpush