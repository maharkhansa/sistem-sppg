<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang Masuk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .supplier-card,
        .supplier-selector-card {
            overflow: hidden;
            border-radius: 10px;
        }

        .supplier-search-filter {
            padding: 16px;
            margin-bottom: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .supplier-search-filter .form-label {
            font-size: .875rem;
            font-weight: 600;
        }

        .price-display {
            margin-top: 4px;
            color: #198754;
            font-size: .78rem;
            font-weight: 600;
        }

        .search-result-count,
        .item-pagination-info {
            color: #6c757d;
            font-size: .83rem;
        }

        .item-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 18px;
        }

        .item-pagination button {
            min-width: 35px;
            height: 35px;
            padding: 0 9px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #fff;
            color: #495057;
            font-size: 13px;
        }

        .item-pagination button:hover:not(:disabled) {
            background: #e9f7ef;
            border-color: #198754;
        }

        .item-pagination button.active {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }

        .item-pagination button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .item-pagination-dots {
            padding: 0 3px;
            color: #6c757d;
        }

        .no-search-result td {
            padding: 25px !important;
            text-align: center;
            color: #6c757d;
        }

        .table th {
            white-space: nowrap;
            font-size: .875rem;
        }

        .table td {
            vertical-align: middle;
        }

        .table .form-control {
            min-width: 90px;
        }

        @media (max-width: 768px) {
            .page-header {
                align-items: stretch !important;
                flex-direction: column;
                gap: 15px;
            }

            .page-header .btn-import {
                width: 100%;
            }

            .supplier-card .card-footer .footer-content {
                align-items: stretch !important;
                flex-direction: column;
                gap: 12px;
            }

            .supplier-card .card-footer .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container my-5">

    <div class="page-header d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('stock-transactions.index') }}"
               class="btn btn-outline-secondary btn-sm me-3"
               title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div>
                <h1 class="page-title text-dark mb-1">
                    <i class="bi bi-box-arrow-in-down text-success me-2"></i>
                    Tambah Stok Barang Masuk
                </h1>
                <p class="text-muted small mb-0">
                    Pilih supplier dan isi jumlah barang yang benar-benar masuk.
                </p>
            </div>
        </div>

        <button type="button"
                class="btn btn-success btn-import"
                data-bs-toggle="modal"
                data-bs-target="#importHargaModal">
            <i class="bi bi-file-earmark-excel me-1"></i>
            Import Harga Excel
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Terjadi kesalahan:
            </strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex">
            <i class="bi bi-info-circle-fill fs-5 me-2"></i>
            <div>
                <strong>Informasi Barang Masuk</strong>
                <div class="small mt-1">
                    Jumlah awal setiap barang adalah <strong>0</strong>.
                    Isi jumlah hanya untuk barang yang benar-benar masuk.<br>
                    Harga satuan diambil dari master barang dan boleh bernilai Rp0.<br>
                    Daftar barang dibatasi menjadi <strong>10 barang per halaman</strong>.<br>
                    Barang berjumlah 0 tidak akan dimasukkan ke transaksi.<br>
                    Hapus hanya mengeluarkan barang dari transaksi, bukan dari master barang.
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 supplier-selector-card">
        <div class="card-body p-4">
            <label for="supplier_selector" class="form-label fw-bold">
                <i class="bi bi-truck text-success me-2"></i>
                Pilih Supplier
            </label>

            <select id="supplier_selector" class="form-select">
                <option value="">-- Pilih Supplier --</option>

                @foreach ($suppliers as $supplier)
                    @php
                        $supplierItemCount = $items->filter(
                            fn ($item) => (int) $item->supplier_id === (int) $supplier->id
                        )->count();
                    @endphp

                    @if ($supplierItemCount > 0)
                        <option value="{{ $supplier->id }}"
                            {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} ({{ $supplierItemCount }} barang)
                        </option>
                    @endif
                @endforeach
            </select>

            <div class="form-text">
                Daftar barang akan tampil sesuai supplier yang dipilih.
            </div>
        </div>
    </div>

    @foreach ($suppliers as $supplier)
        @php
            $supplierItems = $items->filter(
                fn ($item) => (int) $item->supplier_id === (int) $supplier->id
            );

            $supplierItemCount = $supplierItems->count();
        @endphp

        @if ($supplierItemCount > 0)
            <div class="card supplier-card border-0 shadow-sm mb-4"
                 data-supplier-id="{{ $supplier->id }}"
                 style="display:none">

                <div class="card-header bg-white border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width:42px;height:42px">
                                <i class="bi bi-truck text-success fs-5"></i>
                            </div>

                            <div>
                                <div class="fw-bold">{{ $supplier->name }}</div>
                                <div class="small text-muted">
                                    Kode Supplier: {{ $supplier->code ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <span class="badge bg-success supplier-item-badge">
                            {{ $supplierItemCount }} Barang
                        </span>
                    </div>
                </div>

                <form action="{{ route('stock-transactions.store') }}"
                      method="POST"
                      class="supplier-form">

                    @csrf

                    <div class="card-body">

                        <div class="supplier-search-filter">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="search_barang_{{ $supplier->id }}"
                                           class="form-label">
                                        <i class="bi bi-search me-1"></i>
                                        Cari Barang
                                    </label>

                                    <input type="search"
                                           id="search_barang_{{ $supplier->id }}"
                                           class="form-control supplier-item-search"
                                           placeholder="Cari nama atau kode barang..."
                                           autocomplete="off">
                                </div>

                                <div class="col-md-4">
                                    <label for="filter_harga_{{ $supplier->id }}"
                                           class="form-label">
                                        <i class="bi bi-funnel me-1"></i>
                                        Filter Harga
                                    </label>

                                    <select id="filter_harga_{{ $supplier->id }}"
                                            class="form-select supplier-price-filter">
                                        <option value="all">Semua Harga</option>
                                        <option value="paid">Harga di atas Rp0</option>
                                        <option value="zero">Harga Rp0</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button type="button"
                                            class="btn btn-outline-secondary w-100 reset-supplier-filter">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <div class="search-result-count mt-3">
                                Total hasil pencarian:
                                <strong class="visible-item-count">{{ $supplierItemCount }}</strong>
                                barang
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="transaction_date_{{ $supplier->id }}"
                                       class="form-label fw-semibold">
                                    Tanggal Barang Masuk
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>

                                    <input type="date"
                                           id="transaction_date_{{ $supplier->id }}"
                                           name="transaction_date"
                                           class="form-control"
                                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Supplier</label>

                                <input type="text"
                                       class="form-control bg-light"
                                       value="{{ $supplier->name }}"
                                       readonly>

                                <input type="hidden"
                                       name="supplier_id"
                                       value="{{ $supplier->id }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-bold mb-0">
                                <i class="bi bi-list-check text-primary me-2"></i>
                                Detail Barang
                            </h2>

                            <span class="badge bg-primary-subtle text-primary">
                                10 barang per halaman
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:55px">No</th>
                                        <th style="width:140px">Kode</th>
                                        <th>Barang</th>
                                        <th style="width:140px" class="text-center">Jumlah</th>
                                        <th style="width:100px" class="text-center">Satuan</th>
                                        <th style="width:180px" class="text-end">Harga Satuan</th>
                                        <th style="width:180px" class="text-end">Subtotal</th>
                                        <th style="width:75px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($supplierItems as $item)
                                        @php
                                            $itemPrice = (float) ($item->price ?? 0);
                                        @endphp

                                        <tr data-item-id="{{ $item->id }}"
                                            data-item-name="{{ strtolower($item->name ?? '') }}"
                                            data-item-code="{{ strtolower($item->code ?? '') }}"
                                            data-has-price="{{ $itemPrice > 0 ? 'paid' : 'zero' }}">

                                            <td class="text-center text-muted row-number">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                <span class="badge text-bg-light border">
                                                    {{ $item->code ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="fw-semibold item-name">
                                                    {{ $item->name }}
                                                </div>

                                                <input type="hidden"
                                                       name="items[{{ $item->id }}][item_id]"
                                                       value="{{ $item->id }}">
                                            </td>

                                            <td>
                                                <input type="number"
                                                       name="items[{{ $item->id }}][quantity]"
                                                       class="form-control text-end quantity-input"
                                                       value="{{ old('items.'.$item->id.'.quantity', 0) }}"
                                                       min="0"
                                                       step="0.01">
                                            </td>

                                            <td>
                                                <input type="text"
                                                       name="items[{{ $item->id }}][unit]"
                                                       class="form-control bg-light text-center"
                                                       value="{{ $item->unit }}"
                                                       readonly>
                                            </td>

                                            <td>
                                                <input type="number"
                                                       name="items[{{ $item->id }}][unit_price]"
                                                       class="form-control text-end unit-price"
                                                       value="{{ old('items.'.$item->id.'.unit_price', $itemPrice) }}"
                                                       min="0"
                                                       step="0.01">

                                                <div class="price-display">
                                                    Rp {{ number_format($itemPrice, 0, ',', '.') }}
                                                </div>
                                            </td>

                                            <td>
                                                <input type="text"
                                                       class="form-control bg-light text-end fw-semibold subtotal"
                                                       value="0"
                                                       readonly>
                                            </td>

                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm remove-item"
                                                        title="Hapus dari transaksi"
                                                        aria-label="Hapus dari transaksi">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">
                                            Total Transaksi
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control bg-white text-end fw-bold supplier-total"
                                                   value="0"
                                                   readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="item-pagination"></div>
                        <div class="item-pagination-info text-center mt-2"></div>

                        <div class="mt-4">
                            <label for="notes_{{ $supplier->id }}"
                                   class="form-label fw-semibold">
                                Catatan
                            </label>

                            <textarea name="notes"
                                      id="notes_{{ $supplier->id }}"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Masukkan catatan transaksi">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top p-3">
                        <div class="footer-content d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <i class="bi bi-box-seam me-1"></i>
                                <span class="remaining-item-count">{{ $supplierItemCount }}</span>
                                barang tersedia pada daftar
                            </div>

                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save me-1"></i>
                                Simpan Barang Masuk
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    @endforeach

    <div id="select-supplier-message" class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-hand-index-thumb fs-1 text-muted"></i>
            <h3 class="h5 mt-3">Pilih Supplier Terlebih Dahulu</h3>
            <p class="text-muted mb-0">
                Gunakan dropdown di atas untuk menampilkan barang milik supplier.
            </p>
        </div>
    </div>

    @php
        $supplierIdsWithItems = $items
            ->pluck('supplier_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        $suppliersWithoutItems = $suppliers->filter(
            fn ($supplier) => !$supplierIdsWithItems->contains((int) $supplier->id)
        );
    @endphp

    @if ($suppliersWithoutItems->count() > 0)
        <div class="card border-warning shadow-sm mb-4">
            <div class="card-header bg-warning-subtle">
                <strong>
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Supplier Belum Memiliki Barang
                </strong>
            </div>

            <div class="card-body">
                <p class="small text-muted">
                    Supplier berikut belum memiliki barang pada master barang:
                </p>

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($suppliersWithoutItems as $supplier)
                        <span class="badge bg-light text-dark border">
                            {{ $supplier->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="text-end mb-5">
        <a href="{{ route('stock-transactions.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Barang Masuk
        </a>
    </div>

</div>

<div class="modal fade"
     id="importHargaModal"
     tabindex="-1"
     aria-labelledby="importHargaModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="importHargaModalLabel">
                    <i class="bi bi-file-earmark-excel text-success me-2"></i>
                    Import Harga dari Excel
                </h2>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
            </div>

            <form action="{{ route('items.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        Pastikan file Excel sesuai format import aplikasi.
                    </div>

                    <label for="file" class="form-label fw-semibold">
                        Pilih File Excel
                    </label>

                    <input type="file"
                           name="file"
                           id="file"
                           class="form-control"
                           accept=".xlsx,.xls,.csv"
                           required>

                    <div class="form-text">Format: XLSX, XLS, atau CSV.</div>

                    <div class="alert alert-warning small mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Import dapat memperbarui master barang sesuai logika controller.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i>
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const supplierSelector = document.getElementById('supplier_selector');
    const supplierCards = document.querySelectorAll('.supplier-card');
    const selectMessage = document.getElementById('select-supplier-message');
    const ITEMS_PER_PAGE = 10;

    function formatNumber(value) {
        return (Number(value) || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 2
        });
    }

    function formatRupiah(value) {
        return 'Rp ' + formatNumber(value);
    }

    function selectSupplier() {
        const selectedId = supplierSelector.value;
        let found = false;

        supplierCards.forEach(function (card) {
            const selected = card.dataset.supplierId === selectedId;
            card.style.display = selected ? '' : 'none';

            if (selected) {
                found = true;
            }
        });

        selectMessage.style.display = found ? 'none' : '';

        const heading = selectMessage.querySelector('h3');
        const description = selectMessage.querySelector('p');

        if (!found && selectedId) {
            heading.textContent = 'Supplier Belum Memiliki Barang';
            description.textContent =
                'Supplier ini belum memiliki barang pada master barang.';
        } else if (!selectedId) {
            heading.textContent = 'Pilih Supplier Terlebih Dahulu';
            description.textContent =
                'Gunakan dropdown di atas untuk menampilkan barang milik supplier.';
        }
    }

    supplierSelector.addEventListener('change', selectSupplier);

    supplierCards.forEach(function (card) {
        const form = card.querySelector('.supplier-form');
        const tbody = card.querySelector('tbody');
        const searchInput = card.querySelector('.supplier-item-search');
        const priceFilter = card.querySelector('.supplier-price-filter');
        const resetButton = card.querySelector('.reset-supplier-filter');
        const pagination = card.querySelector('.item-pagination');
        const paginationInfo = card.querySelector('.item-pagination-info');
        const countDisplay = card.querySelector('.visible-item-count');

        let currentPage = 1;

        function getRows() {
            return Array.from(tbody.querySelectorAll('tr[data-item-name]'));
        }

        const emptyRow = document.createElement('tr');
        emptyRow.className = 'no-search-result';
        emptyRow.style.display = 'none';

        const emptyCell = document.createElement('td');
        emptyCell.colSpan = 8;
        emptyCell.textContent = 'Barang tidak ditemukan. Coba kata kunci atau filter lain.';

        emptyRow.appendChild(emptyCell);
        tbody.appendChild(emptyRow);

        function calculateTotal() {
            let total = 0;

            getRows().forEach(function (row) {
                const quantityInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.unit-price');
                const subtotalInput = row.querySelector('.subtotal');
                const priceDisplay = row.querySelector('.price-display');

                const quantity = Math.max(0, Number(quantityInput.value) || 0);
                const price = Math.max(0, Number(priceInput.value) || 0);
                const subtotal = quantity * price;

                subtotalInput.value = formatNumber(subtotal);
                priceDisplay.textContent = formatRupiah(price);
                row.dataset.hasPrice = price > 0 ? 'paid' : 'zero';

                total += subtotal;
            });

            card.querySelector('.supplier-total').value = formatNumber(total);
        }

        function updateCounts(visibleCount) {
            card.querySelector('.remaining-item-count').textContent = getRows().length;
            card.querySelector('.supplier-item-badge').textContent =
                getRows().length + ' Barang';
            countDisplay.textContent = visibleCount;
        }

        function getFilteredRows() {
            const keyword = searchInput.value.trim().toLowerCase();
            const selectedPrice = priceFilter.value;

            return getRows().filter(function (row) {
                const matchesSearch =
                    (row.dataset.itemName || '').includes(keyword) ||
                    (row.dataset.itemCode || '').includes(keyword);

                const matchesPrice =
                    selectedPrice === 'all' ||
                    selectedPrice === row.dataset.hasPrice;

                return matchesSearch && matchesPrice;
            });
        }

        function addPageButton(label, page, disabled, active, title) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;
            button.title = title || '';

            if (active) {
                button.classList.add('active');
                button.setAttribute('aria-current', 'page');
            }

            button.addEventListener('click', function () {
                currentPage = page;
                renderPagination();
            });

            pagination.appendChild(button);
        }

        function addDots() {
            const dots = document.createElement('span');
            dots.className = 'item-pagination-dots';
            dots.textContent = '…';
            pagination.appendChild(dots);
        }

        function renderPagination() {
            const filteredRows = getFilteredRows();
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

            if (totalPages === 0) {
                currentPage = 1;
            } else if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            getRows().forEach(function (row) {
                row.style.display = 'none';
            });

            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);

            filteredRows.slice(startIndex, endIndex).forEach(function (row) {
                row.style.display = '';
            });

            emptyRow.style.display = totalItems === 0 ? '' : 'none';

            updateCounts(totalItems);

            paginationInfo.textContent = totalItems === 0
                ? 'Tidak ada barang yang ditampilkan.'
                : 'Menampilkan ' + (startIndex + 1) + '–' + endIndex +
                  ' dari ' + totalItems + ' barang';

            pagination.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            addPageButton('‹', currentPage - 1, currentPage === 1, false, 'Halaman sebelumnya');

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            startPage = Math.max(1, endPage - 4);

            if (startPage > 1) {
                addPageButton('1', 1, false, currentPage === 1, 'Halaman 1');

                if (startPage > 2) {
                    addDots();
                }
            }

            for (let page = startPage; page <= endPage; page++) {
                addPageButton(
                    String(page),
                    page,
                    false,
                    page === currentPage,
                    'Halaman ' + page
                );
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    addDots();
                }

                addPageButton(
                    String(totalPages),
                    totalPages,
                    false,
                    currentPage === totalPages,
                    'Halaman terakhir'
                );
            }

            addPageButton(
                '›',
                currentPage + 1,
                currentPage === totalPages,
                false,
                'Halaman berikutnya'
            );
        }

        function applyFilter() {
            currentPage = 1;
            renderPagination();
        }

        searchInput.addEventListener('input', applyFilter);
        priceFilter.addEventListener('change', applyFilter);

        resetButton.addEventListener('click', function () {
            searchInput.value = '';
            priceFilter.value = 'all';
            applyFilter();
            searchInput.focus();
        });

        tbody.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-item');

            if (!button) {
                return;
            }

            const row = button.closest('tr[data-item-name]');

            if (!row) {
                return;
            }

            const name =
                row.querySelector('.item-name')?.textContent.trim() || 'barang ini';

            if (!confirm(
                'Hapus "' + name + '" dari transaksi ini?\n\n' +
                'Barang hanya dikeluarkan dari transaksi saat ini, bukan dari master barang.'
            )) {
                return;
            }

            row.remove();

            getRows().forEach(function (itemRow, index) {
                itemRow.querySelector('.row-number').textContent = index + 1;
            });

            calculateTotal();
            applyFilter();
        });

        form.addEventListener('input', function (event) {
            if (event.target.matches('.quantity-input, .unit-price')) {
                calculateTotal();

                if (event.target.matches('.unit-price')) {
                    applyFilter();
                }
            }
        });

        form.addEventListener('submit', function (event) {
            if (supplierSelector.value !== card.dataset.supplierId) {
                event.preventDefault();
                alert('Silakan pilih supplier yang sesuai sebelum menyimpan.');
                return;
            }

            const rows = getRows();
            let valid = true;
            let selectedCount = 0;

            rows.forEach(function (row) {
                const quantity = row.querySelector('.quantity-input');
                const price = row.querySelector('.unit-price');

                const qty = Number(quantity.value);
                const unitPrice = Number(price.value);

                quantity.setCustomValidity('');
                price.setCustomValidity('');

                if (
                    quantity.value.trim() === '' ||
                    !Number.isFinite(qty) ||
                    qty < 0
                ) {
                    valid = false;
                    quantity.setCustomValidity('Jumlah harus 0 atau lebih.');
                    quantity.reportValidity();
                    return;
                }

                // Jumlah nol berarti barang tidak dibeli dan tidak dikirim.
                if (qty === 0) {
                    return;
                }

                selectedCount++;

                if (
                    price.value.trim() === '' ||
                    !Number.isFinite(unitPrice) ||
                    unitPrice < 0
                ) {
                    valid = false;
                    price.setCustomValidity('Harga harus diisi dengan angka 0 atau lebih.');
                    price.reportValidity();
                }
            });

            if (!valid) {
                event.preventDefault();
                return;
            }

            if (selectedCount === 0) {
                event.preventDefault();
                alert('Isi jumlah minimal satu barang yang benar-benar masuk.');
                return;
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });

        calculateTotal();
        renderPagination();
    });

    selectSupplier();
});
</script>

</body>
</html>