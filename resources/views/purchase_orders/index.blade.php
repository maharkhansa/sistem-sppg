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

    .po-page .po-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 24px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025);
    }

    .po-page .po-header {
        padding: 16px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        overflow-x: auto;
    }

    .po-page .po-header-table {
        width: 100%;
        min-width: 650px;
        border-collapse: collapse;
    }

    .po-page .po-header-table td {
        padding: 7px 8px;
        vertical-align: top;
        font-size: 13px;
    }

    .po-page .po-header-table td.label {
        width: 145px;
        font-weight: 700;
        color: #495057;
    }

    .po-page .po-header-table td.value {
        overflow-wrap: anywhere;
    }

    .po-page .po-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .po-page .po-actions form {
        display: inline-flex;
        margin: 0;
    }

    .po-page .detail-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .po-page .detail-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .po-page .detail-table th,
    .po-page .detail-table td {
        border: 1px solid #e0e0e0;
        padding: 10px 12px;
        font-size: 13px;
    }

    .po-page .detail-table th {
        background: #f1f3f5;
        color: #495057;
        font-weight: 700;
        white-space: nowrap;
    }

    .po-page .detail-table tbody tr:hover {
        background: #fafbfc;
    }

    .po-page .text-right {
        text-align: right;
    }

    .po-page .text-center {
        text-align: center;
    }

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

    @media (max-width: 1100px) {
        .po-page .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

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

        .po-page .po-card {
            box-shadow: none;
            border: 1px solid #000;
            border-radius: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .po-page .po-header {
            background: #fff;
            border-bottom: 1px solid #000;
        }

        .po-page .detail-table th,
        .po-page .detail-table td {
            border: 1px solid #000;
        }

        .po-page .detail-table th {
            background: #eee !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .po-page .po-card[style*="display: none"] {
            display: none !important;
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
        @if (session('success'))
            <div class="alert alert-success no-print">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger no-print">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print">
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
            @forelse ($purchaseOrders as $purchaseOrder)
                @php
                    $statusText = strtolower(trim((string) $purchaseOrder->status));

                    $statusClass = match ($statusText) {
                        'approved' => 'status-approved',
                        'processed' => 'status-processed',
                        'rejected', 'canceled', 'cancelled' => 'status-rejected',
                        default => 'status-pending',
                    };

                    $tanggalPO = $purchaseOrder->po_date
                        ? \Illuminate\Support\Carbon::parse($purchaseOrder->po_date)->format('Y-m-d')
                        : '';

                    $searchText = implode(' ', array_filter([
                        $purchaseOrder->po_number,
                        $purchaseOrder->kitchen?->id_sppg,
                        $purchaseOrder->kitchen?->name,
                        $purchaseOrder->notes,
                        $purchaseOrder->status,
                    ]));
                @endphp

                <div
                    class="po-card"
                    data-search="{{ strtolower($searchText) }}"
                    data-status="{{ $statusText }}"
                    data-date="{{ $tanggalPO }}"
                >
                    {{-- INFORMASI PO --}}
                    <div class="po-header">
                        <table class="po-header-table">
                            <tbody>
                                <tr>
                                    <td class="label">No. PO</td>
                                    <td class="value">
                                        <strong>{{ $purchaseOrder->po_number }}</strong>
                                    </td>

                                    <td class="label">Tanggal PO</td>
                                    <td class="value">
                                        {{ $purchaseOrder->po_date
                                            ? \Illuminate\Support\Carbon::parse($purchaseOrder->po_date)->format('d-m-Y')
                                            : '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label">SPPG / Dapur</td>
                                    <td class="value" colspan="3">
                                        {{ $purchaseOrder->kitchen?->id_sppg ?? '-' }}
                                        -
                                        {{ $purchaseOrder->kitchen?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label">Status</td>
                                    <td class="value">
                                        <span class="status {{ $statusClass }}">
                                            {{ $purchaseOrder->status }}
                                        </span>
                                    </td>

                                    <td class="label">Tanggal Approve</td>
                                    <td class="value">
                                        {{ $purchaseOrder->approved_at
                                            ? $purchaseOrder->approved_at->format('d-m-Y H:i')
                                            : '-' }}
                                    </td>
                                </tr>

                                @if ($purchaseOrder->notes)
                                    <tr>
                                        <td class="label">Catatan</td>
                                        <td class="value" colspan="3">
                                            {{ $purchaseOrder->notes }}
                                        </td>
                                    </tr>
                                @endif

                                {{-- AKSI PO --}}
                                <tr class="no-print">
                                    <td class="label">Aksi</td>
                                    <td colspan="3">
                                        <div class="po-actions">
                                            <a
                                                href="{{ route('purchase-orders.daftar-pesanan', $purchaseOrder->id) }}"
                                                class="btn btn-success"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Daftar Pesanan MBG
                                            </a>

                                            <a
                                                href="{{ route('purchase-orders.delivery-note', $purchaseOrder->id) }}"
                                                class="btn btn-info"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Delivery Note
                                            </a>

                                            @if (strtoupper((string) $purchaseOrder->status) !== 'PROCESSED')
                                                <form
                                                    action="{{ route('purchase-orders.process', $purchaseOrder->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Apakah PO ini akan diproses menjadi OUT? Stok akan berkurang dan Invoice akan dibuat otomatis.');"
                                                >
                                                    @csrf

                                                    <button type="submit" class="btn btn-process">
                                                        Proses PO menjadi OUT
                                                    </button>
                                                </form>
                                            @else
                                                <span class="btn btn-success">
                                                    Sudah Diproses menjadi OUT
                                                </span>

                                                @if ($purchaseOrder->invoice)
                                                    <a
                                                        href="{{ route('invoices.show', $purchaseOrder->invoice->id) }}"
                                                        class="btn btn-print"
                                                    >
                                                        Lihat Invoice
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- DETAIL PO --}}
                    <div class="detail-wrapper">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>Supplier</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($purchaseOrder->details as $index => $detail)
                                    <tr>
                                        <td class="text-center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td>
                                            {{ $detail->supplier?->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $detail->item?->code ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $detail->item?->name ?? '-' }}
                                        </td>

                                        <td class="text-right">
                                            {{ number_format((float) $detail->quantity, 2, ',', '.') }}
                                        </td>

                                        <td class="text-center">
                                            {{ $detail->unit ?? '-' }}
                                        </td>

                                        <td class="text-right">
                                            Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                        </td>

                                        <td class="text-right">
                                            Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            Tidak ada item detail untuk PO ini.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr>
                                    <td colspan="7" class="text-right">
                                        <strong>Total</strong>
                                    </td>

                                    <td class="text-right">
                                        <strong>
                                            Rp {{ number_format((float) $purchaseOrder->details->sum('subtotal'), 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="empty-state" id="emptyOriginal">
                    <strong>Belum ada Purchase Order.</strong>
                    <span>Silakan tambah Purchase Order untuk memulai.</span>
                </div>
            @endforelse
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