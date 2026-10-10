<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Purchase Order</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .header-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-add {
            background: #198754;
            color: white;
        }

        .btn-print {
            background: #0d6efd;
            color: white;
            margin-left: 8px;
        }

        .btn-process {
            background: #fd7e14;
            color: white;
        }

        .btn-process:hover {
            opacity: 0.9;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .btn-reset {
            background: #6c757d;
            color: white;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #badbcc;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c2c7;
        }

        /* PENCARIAN DAN FILTER */

        .filter-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .filter-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 15px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: minmax(220px, 2fr)
                repeat(3, minmax(150px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: bold;
            color: #495057;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
            background: white;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .result-info {
            color: #6c757d;
            font-size: 13px;
            margin-left: auto;
        }

        .po-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
            background: #fff;
        }

        .po-header {
            background: #f8f9fa;
            padding: 16px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .po-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .po-header td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .po-header td.label {
            width: 140px;
            font-weight: bold;
            color: #555;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            font-size: 14px;
        }

        .detail-table th {
            background: #f1f3f5;
            color: #495057;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #664d03;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .status-processed {
            background: #cfe2ff;
            color: #084298;
        }

        .empty-filter {
            display: none;
            padding: 35px 20px;
            text-align: center;
            color: #6c757d;
            background: #fff;
            border: 1px dashed #ced4da;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        @media (max-width: 1000px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .container {
                padding: 20px;
            }
        }

        @media (max-width: 600px) {
            body {
                margin: 10px;
            }

            .container {
                padding: 15px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .header-action {
                align-items: flex-start;
            }

            .po-card {
                overflow-x: auto;
            }

            .po-header,
            .detail-table {
                min-width: 750px;
            }

            .result-info {
                width: 100%;
                margin-left: 0;
            }
        }

        @media print {
            body {
                margin: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .po-card {
                page-break-inside: avoid;
                border: 1px solid #000;
            }

            .detail-table th,
            .detail-table td {
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>

<div class="container">

    {{-- HEADER HALAMAN --}}
    <div class="header-action">

        <h1>Purchase Order</h1>

        <div class="no-print">

            <a href="{{ route('purchase-orders.create') }}"
               class="btn btn-add">
                + Tambah Purchase Order
            </a>

            <button onclick="window.print()"
                    type="button"
                    class="btn btn-print">
                🖨️ Cetak
            </button>

        </div>

    </div>

    {{-- PESAN BERHASIL --}}
    @if (session('success'))
        <div class="success no-print">
            {{ session('success') }}
        </div>
    @endif

    {{-- PESAN ERROR --}}
    @if (session('error'))
        <div class="error no-print">
            {{ session('error') }}
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

            <button type="button"
                    class="btn btn-reset"
                    id="resetFilter">
                ↻ Reset Filter
            </button>

            <span class="result-info" id="resultInfo" aria-live="polite">
                Menampilkan semua Purchase Order
            </span>

        </div>

        <p id="tanggalError"
           style="display:none;color:#842029;font-size:13px;margin:12px 0 0;">
            Tanggal awal tidak boleh lebih besar dari tanggal akhir.
        </p>

    </div>

    {{-- DAFTAR PO --}}
    <div id="purchaseOrderList">

        @forelse ($purchaseOrders as $purchaseOrder)

            @php
                $statusText = strtolower((string) $purchaseOrder->status);

                $statusClass = match ($statusText) {
                    'approved' => 'status-approved',
                    'processed' => 'status-processed',
                    'rejected', 'canceled' => 'status-rejected',
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

                {{-- HEADER PO --}}
                <div class="po-header">

                    <table>

                        <tr>
                            <td class="label">No. PO</td>
                            <td>
                                <strong>{{ $purchaseOrder->po_number }}</strong>
                            </td>

                            <td class="label">Tanggal PO</td>
                            <td>
                                {{ $purchaseOrder->po_date
                                    ? \Illuminate\Support\Carbon::parse($purchaseOrder->po_date)->format('d-m-Y')
                                    : '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">SPPG / Dapur</td>
                            <td colspan="3">
                                {{ $purchaseOrder->kitchen?->id_sppg ?? '-' }}
                                -
                                {{ $purchaseOrder->kitchen?->name ?? 'N/A' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">Status</td>
                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $purchaseOrder->status }}
                                </span>
                            </td>

                            <td class="label">Tanggal Approve</td>
                            <td>
                                {{ $purchaseOrder->approved_at
                                    ? $purchaseOrder->approved_at->format('d-m-Y H:i')
                                    : '-' }}
                            </td>
                        </tr>

                        @if ($purchaseOrder->notes)
                            <tr>
                                <td class="label">Catatan</td>
                                <td colspan="3">
                                    {{ $purchaseOrder->notes }}
                                </td>
                            </tr>
                        @endif

                    {{-- AKSI --}}
                    <tr class="no-print">
                        <td class="label">Aksi</td>
                        <td colspan="3">

                            {{-- TOMBOL DAFTAR PESANAN SELALU MUNCUL --}}
                            <a
                                href="{{ route('purchase-orders.daftar-pesanan', $purchaseOrder->id) }}"
                                class="btn btn-success"
                                target="_blank"
                            >
                                📋 Daftar Pesanan MBG
                            </a>

                            <a
                                href="{{ route('purchase-orders.delivery-note', $purchaseOrder->id) }}"
                                class="btn btn-info"
                                target="_blank"
                            >
                                📦 Delivery Note
                            </a>

                            {{-- TOMBOL PROSES HANYA UNTUK PO YANG BELUM DIPROSES --}}
                            @if (strtoupper((string) $purchaseOrder->status) !== 'PROCESSED')

                                <form
                                    action="{{ route('purchase-orders.process', $purchaseOrder->id) }}"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Apakah PO ini akan diproses menjadi OUT? Stok akan berkurang dan Invoice akan dibuat otomatis.');"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-process"
                                    >
                                        Proses PO → OUT
                                    </button>
                                </form>

                            @else

                                <span class="btn btn-success">
                                    ✓ Sudah Diproses menjadi OUT
                                </span>

                                @if ($purchaseOrder->invoice)
                                    <a
                                        href="{{ route('invoices.show', $purchaseOrder->invoice->id) }}"
                                        class="btn btn-print"
                                    >
                                        🧾 Lihat Invoice
                                    </a>
                                @endif

                            @endif

                        </td>
                    </tr>

                {{-- DETAIL PO --}}
                <table class="detail-table">

                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th width="80">Qty</th>
                            <th width="80">Satuan</th>
                            <th width="120">Harga</th>
                            <th width="140">Jumlah</th>
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

        @empty

            <p class="text-center"
               style="padding:40px;color:#777;">
                Belum ada Purchase Order.
            </p>

        @endforelse

    </div>

    {{-- PESAN JIKA HASIL FILTER KOSONG --}}
    <div id="emptyFilter" class="empty-filter">
        <strong>Purchase Order tidak ditemukan.</strong>
        <p style="margin-bottom:0;">
            Coba ubah kata pencarian, status, atau rentang tanggal.
        </p>
    </div>

</div>

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

    const cards = Array.from(document.querySelectorAll('.po-card'));
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

</body>
</html>