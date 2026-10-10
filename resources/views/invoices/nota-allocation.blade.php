<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Alokasi Nota - {{ $invoice->invoice_number ?? 'Invoice' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            color: #243247;
            font-family: Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 1450px;
            margin: 28px auto;
            padding: 0 18px;
        }

        .main-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
        }

        .note-card {
            height: 100%;
            padding: 18px;
            background: #fff;
            border: 1px solid #dfe5ec;
            border-radius: 12px;
        }

        .note-card h6 {
            font-weight: 700;
        }

        .note-card .form-label {
            font-size: 13px;
            font-weight: 600;
        }

        .note-total {
            font-size: 18px;
            font-weight: 700;
            color: #198754;
        }

        .item-row {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
            background: #fff;
        }

        .item-name {
            font-weight: 600;
        }

        .item-meta {
            color: #64748b;
            font-size: 13px;
        }

        .section-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
        }

        .empty-message {
            padding: 22px;
            text-align: center;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            color: #64748b;
        }

        .btn {
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .main-card {
                padding: 15px;
            }

            .page-wrapper {
                padding: 0 10px;
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>
<div class="page-wrapper">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Pembagian Tabel Invoice dan Nota</h3>
            <div class="text-muted">
                Invoice:
                <strong>{{ $invoice->invoice_number ?? '-' }}</strong>
            </div>
        </div>

        <a href="{{ route('invoices.show', $invoice) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Invoice
        </a>
    </div>

    {{-- PESAN --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFORMASI INVOICE --}}
    <div class="main-card mb-4">
        <div class="section-title">
            <i class="bi bi-receipt"></i> Informasi Invoice
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Nomor Invoice</div>
                <strong>{{ $invoice->invoice_number ?? '-' }}</strong>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Tanggal Invoice</div>
                <strong>
                    {{ $invoice->invoice_date
                        ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y')
                        : '-' }}
                </strong>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Total Invoice</div>
                <strong class="text-success">
                    Rp {{ number_format((float) ($invoice->total_amount ?? 0), 0, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>

    <form
        id="allocationForm"
        method="POST"
        action="{{ route('invoices.nota-allocation.save', $invoice) }}"
    >
        @csrf

        {{-- BAGIAN NOTA --}}
        <div class="main-card mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <div class="section-title mb-1">
                        Bagian Tabel Invoice / Nota
                    </div>
                    <div class="text-muted small">
                        Satu bagian menghasilkan satu Nota. Supplier yang sama boleh mempunyai beberapa bagian.
                    </div>
                </div>

                <button type="button" class="btn btn-primary" id="addNoteButton">
                    <i class="bi bi-plus-circle"></i> Tambah Tabel/Bagian
                </button>
            </div>

            <div class="row g-3" id="notesContainer">

                @foreach($notes as $noteIndex => $nota)
                    @php
                        $supplierNotes = $notes
                            ->where('supplier_id', $nota->supplier_id)
                            ->sortBy('section_order')
                            ->values();

                        $supplierNoteNumber = $supplierNotes->search(
                            fn ($item) => (int) $item->id === (int) $nota->id
                        );

                        $supplierNoteNumber = $supplierNoteNumber === false
                            ? $noteIndex + 1
                            : $supplierNoteNumber + 1;

                        $sectionName = $nota->section_name
                            ?: 'Bagian ' . $supplierNoteNumber;
                    @endphp

                    <div
                        class="col-md-6 col-xl-4"
                        data-note-wrapper
                        data-note-index="{{ $noteIndex }}"
                        data-existing="1"
                        data-saved-note-id="{{ $nota->id }}"
                        data-original-supplier="{{ $nota->supplier_id }}"
                    >
                        <div class="note-card">

                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bi bi-file-earmark-text"></i>
                                        Bagian Nota
                                    </h6>

                                    <div class="text-muted small" data-note-label>
                                        {{ $nota->supplier->name ?? 'Supplier' }}
                                        — {{ $sectionName }}
                                    </div>
                                </div>

                                <span class="badge text-bg-success">Tersimpan</span>
                            </div>

                            <input
                                type="hidden"
                                name="notes[{{ $noteIndex }}][nota_keluar_id]"
                                value="{{ $nota->id }}"
                            >

                            <div class="mb-3">
                                <label class="form-label">Supplier</label>

                                <select class="form-select" disabled>
                                    <option selected>
                                        {{ $nota->supplier->name ?? 'Supplier' }}
                                    </option>
                                </select>

                                <input
                                    type="hidden"
                                    name="notes[{{ $noteIndex }}][supplier_id]"
                                    value="{{ $nota->supplier_id }}"
                                    data-hidden-supplier
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Bagian</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="notes[{{ $noteIndex }}][section_name]"
                                    value="{{ $sectionName }}"
                                    maxlength="100"
                                    required
                                    data-section-name
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Urutan Bagian</label>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="notes[{{ $noteIndex }}][section_order]"
                                    value="{{ $nota->section_order ?: $supplierNoteNumber }}"
                                    min="1"
                                    required
                                    data-section-order
                                >
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div>
                                    <div class="text-muted small">Subtotal Bagian</div>
                                    <div class="note-total" data-note-total>Rp 0</div>
                                </div>

                                <span class="badge text-bg-light border" data-note-item-count>
                                    0 barang
                                </span>
                            </div>

                            <div class="mt-3">
                                <a
                                    href="{{ route('nota-keluars.print', ['notaKeluar' => $nota->id]) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    <i class="bi bi-printer"></i> Cetak Nota
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>

            <div class="empty-message mt-3 d-none" id="emptyNotesMessage">
                Belum ada bagian. Klik <strong>Tambah Tabel/Bagian</strong> untuk membuat bagian baru.
            </div>
        </div>

        {{-- ALOKASI BARANG --}}
        <div class="main-card mb-4">
            <div class="section-title">
                <i class="bi bi-box-seam"></i> Pembagian Barang ke Bagian
            </div>

            <div class="text-muted small mb-3">
                Setiap baris barang harus masuk tepat ke satu bagian Nota dengan supplier yang sesuai.
            </div>

            @forelse($invoice->details as $detail)
                @php
                    $poDetails = $invoice->purchaseOrder?->details ?? collect();

                    $poDetail = $poDetails->first(
                        fn ($row) =>
                            (int) $row->item_id === (int) $detail->item_id
                            && !empty($row->supplier_id)
                    );

                    $detailSupplierId = $detail->supplier_id
                        ?? $detail->supplier?->id
                        ?? $detail->item?->supplier_id
                        ?? $detail->item?->supplier?->id
                        ?? $poDetail?->supplier_id;

                    $detailName = $detail->item?->name ?? 'Barang';

                    $detailCode = $detail->item?->code
                        ?? $detail->item?->item_code
                        ?? '-';

                    $detailQuantity = (float) ($detail->quantity ?? 0);
                    $detailPrice = (float) ($detail->unit_price ?? 0);

                    $detailSubtotal = (float) (
                        $detail->subtotal
                        ?? ($detailQuantity * $detailPrice)
                    );

                    /*
                     * Temukan Nota yang saat ini mengalokasikan detail ini.
                     * Data ini digunakan untuk memilih Nota secara otomatis.
                     */
                    $allocatedNote = $notes->first(
                        fn ($nota) => $nota->invoiceNotaAllocations->contains(
                            fn ($allocation) =>
                                (int) $allocation->invoice_detail_id === (int) $detail->id
                        )
                    );

                    $selectedAllocation = $allocatedNote
                        ? 'existing:' . $allocatedNote->id
                        : '';
                @endphp

                <div
                    class="item-row"
                    data-invoice-detail
                    data-detail-id="{{ $detail->id }}"
                    data-supplier-id="{{ $detailSupplierId }}"
                    data-subtotal="{{ $detailSubtotal }}"
                >
                    <div class="row g-3 align-items-center">

                        <div class="col-md-5">
                            <div class="item-name">{{ $detailName }}</div>

                            <div class="item-meta">
                                Kode: {{ $detailCode }}
                                <span class="mx-1">|</span>
                                Qty:
                                {{ rtrim(rtrim(number_format($detailQuantity, 2, '.', ''), '0'), '.') }}
                                {{ $detail->unit ?? '' }}
                            </div>

                            <div class="item-meta mt-1">
                                Supplier:
                                {{ $detail->supplier?->name
                                    ?? $detail->item?->supplier?->name
                                    ?? $poDetail?->supplier?->name
                                    ?? 'Belum ditentukan' }}
                            </div>

                            <div class="item-meta mt-1">
                                Harga satuan:
                                Rp {{ number_format($detailPrice, 0, ',', '.') }}
                            </div>

                            <div class="fw-semibold mt-1">
                                Jumlah:
                                Rp {{ number_format($detailSubtotal, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Masuk ke Bagian</label>

                            <select
                                class="form-select"
                                data-detail-note-select
                                data-detail-id="{{ $detail->id }}"
                                data-initial-selection="{{ $selectedAllocation }}"
                                required
                            >
                                <option value="">-- Pilih Bagian Nota --</option>

                                @foreach($notes as $nota)
                                    @if((string) $nota->supplier_id === (string) $detailSupplierId)
                                        @php
                                            $supplierNotesForDropdown = $notes
                                                ->where('supplier_id', $nota->supplier_id)
                                                ->sortBy('section_order')
                                                ->values();

                                            $numberForDropdown = $supplierNotesForDropdown->search(
                                                fn ($item) => (int) $item->id === (int) $nota->id
                                            );

                                            $numberForDropdown = $numberForDropdown === false
                                                ? 1
                                                : $numberForDropdown + 1;

                                            $dropdownSectionName = $nota->section_name
                                                ?: 'Bagian ' . $numberForDropdown;
                                        @endphp

                                        <option
                                            value="existing:{{ $nota->id }}"
                                            @selected($selectedAllocation === 'existing:' . $nota->id)
                                        >
                                            {{ $nota->supplier->name ?? 'Supplier' }}
                                            — {{ $dropdownSectionName }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            @if(!$detailSupplierId)
                                <div class="form-text text-danger">
                                    Supplier belum ditemukan. Periksa supplier pada detail Invoice, master barang, atau Purchase Order.
                                </div>
                            @else
                                <div class="form-text">
                                    Pilih bagian Nota milik supplier barang ini.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            @empty
                <div class="empty-message">
                    Tidak ada detail barang pada Invoice ini.
                </div>
            @endforelse
        </div>

        {{-- RINGKASAN --}}
        <div class="main-card mb-4">
            <div class="section-title">
                <i class="bi bi-calculator"></i> Ringkasan Alokasi
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="text-muted small">Jumlah Detail Invoice</div>
                        <div class="fs-4 fw-bold" id="totalDetails">
                            {{ $invoice->details->count() }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="text-muted small">Sudah Dialokasikan</div>
                        <div class="fs-4 fw-bold text-success" id="allocatedDetails">0</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="text-muted small">Belum Dialokasikan</div>
                        <div class="fs-4 fw-bold text-danger" id="unallocatedDetails">0</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="btn btn-outline-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-success" id="saveAllocationButton">
                    <i class="bi bi-save"></i> Simpan Pembagian
                </button>
            </div>
        </div>
    </form>
</div>

{{-- TEMPLATE BAGIAN BARU --}}
<template id="noteTemplate">
    <div class="col-md-6 col-xl-4" data-note-wrapper data-existing="0">
        <div class="note-card">

            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="mb-1">
                        <i class="bi bi-file-earmark-plus"></i> Bagian Nota Baru
                    </h6>
                    <div class="text-muted small" data-note-label>Bagian baru</div>
                </div>

                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        data-remove-note
                        title="Hapus bagian baru">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label">Supplier</label>

                <select class="form-select" data-supplier-select required>
                    <option value="">-- Pilih supplier --</option>

                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Bagian</label>

                <input
                    type="text"
                    class="form-control"
                    data-section-name
                    maxlength="100"
                    placeholder="Contoh: Bagian 2"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Urutan Bagian</label>

                <input
                    type="number"
                    class="form-control"
                    data-section-order
                    min="1"
                    value="1"
                    required
                >
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <div>
                    <div class="text-muted small">Subtotal Bagian</div>
                    <div class="note-total" data-note-total>Rp 0</div>
                </div>

                <span class="badge text-bg-light border" data-note-item-count>
                    0 barang
                </span>
            </div>

            <div class="small text-muted mt-3">
                Nomor Nota dibuat setelah pembagian disimpan.
            </div>

        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('allocationForm');
    const notesContainer = document.getElementById('notesContainer');
    const noteTemplate = document.getElementById('noteTemplate');
    const addNoteButton = document.getElementById('addNoteButton');
    const emptyNotesMessage = document.getElementById('emptyNotesMessage');

    let nextNoteIndex = {{ $notes->count() }};

    const rupiah = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
        maximumFractionDigits: 2
    });

    function getWrappers() {
        return Array.from(
            notesContainer.querySelectorAll('[data-note-wrapper]')
        );
    }

    function getToken(wrapper) {
        if (wrapper.dataset.existing === '1') {
            return 'existing:' + wrapper.dataset.savedNoteId;
        }

        return 'new:' + wrapper.dataset.noteIndex;
    }

    function getSupplier(wrapper) {
        const select = wrapper.querySelector('[data-supplier-select]');
        const hidden = wrapper.querySelector('[data-hidden-supplier]');

        return select?.value || hidden?.value || '';
    }

    function getSupplierSectionNumber(wrapper) {
        const supplierId = getSupplier(wrapper);

        if (!supplierId) {
            return 1;
        }

        const sameSupplierWrappers = getWrappers().filter(item =>
            String(getSupplier(item)) === String(supplierId)
        );

        return sameSupplierWrappers.indexOf(wrapper) + 1;
    }

    function getSupplierName(wrapper) {
        const select = wrapper.querySelector('[data-supplier-select]');

        if (select) {
            return select.selectedOptions[0]?.textContent.trim() || 'Supplier';
        }

        const supplierHidden = wrapper.querySelector('[data-hidden-supplier]');

        if (supplierHidden) {
            const supplierId = supplierHidden.value;
            const option = document.querySelector(
                '#noteTemplate [data-supplier-select] option[value="' + supplierId + '"]'
            );

            return option?.textContent.trim() || 'Supplier';
        }

        return 'Supplier';
    }

    function refreshLabels() {
        getWrappers().forEach(wrapper => {
            const supplierName = getSupplierName(wrapper);
            const sectionInput = wrapper.querySelector('[data-section-name]');
            const sectionName = sectionInput?.value.trim()
                || ('Bagian ' + getSupplierSectionNumber(wrapper));

            const label = wrapper.querySelector('[data-note-label]');

            if (label) {
                label.textContent = supplierName + ' — ' + sectionName;
            }
        });
    }

    function refreshDetailOptions() {
        const allWrappers = getWrappers();

        document.querySelectorAll('[data-detail-note-select]').forEach(select => {
            const row = select.closest('[data-invoice-detail]');
            const supplierId = row?.dataset.supplierId || '';

            const previousValue = select.value;
            const initialValue = select.dataset.initialSelection || '';

            select.replaceChildren();

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Pilih Bagian Nota --';
            select.appendChild(placeholder);

            allWrappers.forEach(wrapper => {
                const noteSupplierId = getSupplier(wrapper);

                if (
                    !supplierId ||
                    !noteSupplierId ||
                    String(noteSupplierId) !== String(supplierId)
                ) {
                    return;
                }

                const option = document.createElement('option');
                option.value = getToken(wrapper);

                const sectionInput = wrapper.querySelector('[data-section-name]');
                const sectionName = sectionInput?.value.trim()
                    || ('Bagian ' + getSupplierSectionNumber(wrapper));

                option.textContent = getSupplierName(wrapper)
                    + ' — ' + sectionName;

                select.appendChild(option);
            });

            /*
             * Pertahankan pilihan pengguna.
             * Jika belum ada pilihan, gunakan alokasi yang sudah tersimpan.
             */
            const desiredValue = previousValue || initialValue;

            const optionExists = Array.from(select.options).some(
                option => option.value === desiredValue
            );

            select.value = optionExists ? desiredValue : '';

            /*
             * Pilihan awal hanya diperlukan saat halaman pertama kali dimuat.
             */
            select.dataset.initialSelection = '';
        });
    }

    function updateTotals() {
        const totals = new Map();
        const counts = new Map();

        getWrappers().forEach(wrapper => {
            const token = getToken(wrapper);

            totals.set(token, 0);
            counts.set(token, 0);
        });

        let allocated = 0;

        document.querySelectorAll('[data-invoice-detail]').forEach(row => {
            const select = row.querySelector('[data-detail-note-select]');
            const token = select?.value;

            if (!token || !totals.has(token)) {
                return;
            }

            totals.set(
                token,
                totals.get(token) + Number(row.dataset.subtotal || 0)
            );

            counts.set(token, counts.get(token) + 1);
            allocated++;
        });

        getWrappers().forEach(wrapper => {
            const token = getToken(wrapper);

            const totalElement = wrapper.querySelector('[data-note-total]');
            const countElement = wrapper.querySelector('[data-note-item-count]');

            if (totalElement) {
                totalElement.textContent = rupiah(totals.get(token) || 0);
            }

            if (countElement) {
                countElement.textContent = (counts.get(token) || 0) + ' barang';
            }
        });

        const totalDetails = document.querySelectorAll(
            '[data-invoice-detail]'
        ).length;

        document.getElementById('totalDetails').textContent = totalDetails;
        document.getElementById('allocatedDetails').textContent = allocated;
        document.getElementById('unallocatedDetails').textContent =
            totalDetails - allocated;

        emptyNotesMessage.classList.toggle(
            'd-none',
            getWrappers().length > 0
        );
    }

    function refreshAll() {
        refreshLabels();
        refreshDetailOptions();
        updateTotals();
    }

    /*
     * TAMBAH BAGIAN BARU
     */
    addNoteButton.addEventListener('click', function () {
        const fragment = noteTemplate.content.cloneNode(true);
        const wrapper = fragment.querySelector('[data-note-wrapper]');

        const index = nextNoteIndex++;

        wrapper.dataset.noteIndex = String(index);

        const supplierSelect = wrapper.querySelector('[data-supplier-select]');
        const sectionName = wrapper.querySelector('[data-section-name]');
        const sectionOrder = wrapper.querySelector('[data-section-order]');

        /*
         * Beri nama field sejak bagian dibuat, bukan hanya saat submit.
         */
        supplierSelect.name = `notes[${index}][supplier_id]`;
        sectionName.name = `notes[${index}][section_name]`;
        sectionOrder.name = `notes[${index}][section_order]`;

        sectionName.value = 'Bagian 1';
        sectionOrder.value = '1';

        /*
         * Controller menerima null untuk nota_keluar_id pada bagian baru.
         */
        const noteIdInput = document.createElement('input');
        noteIdInput.type = 'hidden';
        noteIdInput.name = `notes[${index}][nota_keluar_id]`;
        noteIdInput.value = '';
        noteIdInput.dataset.generatedNoteId = '1';

        wrapper.querySelector('.note-card').appendChild(noteIdInput);

        notesContainer.appendChild(fragment);

        refreshAll();

        wrapper.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    });

    /*
     * GANTI SUPPLIER PADA BAGIAN BARU
     */
    notesContainer.addEventListener('change', function (event) {
        if (!event.target.matches('[data-supplier-select]')) {
            return;
        }

        const wrapper = event.target.closest('[data-note-wrapper]');
        const supplierId = event.target.value;

        const sameSupplierCount = getWrappers().filter(item =>
            item !== wrapper &&
            supplierId &&
            String(getSupplier(item)) === String(supplierId)
        ).length;

        const nextNumber = sameSupplierCount + 1;

        wrapper.querySelector('[data-section-order]').value = nextNumber;
        wrapper.querySelector('[data-section-name]').value =
            'Bagian ' + nextNumber;

        refreshAll();
    });

    /*
     * PERUBAHAN NAMA ATAU URUTAN BAGIAN
     */
    notesContainer.addEventListener('input', function (event) {
        if (
            event.target.matches('[data-section-name]') ||
            event.target.matches('[data-section-order]')
        ) {
            refreshLabels();
            refreshDetailOptions();
            updateTotals();
        }
    });

    /*
     * HAPUS BAGIAN BARU YANG BELUM TERSIMPAN
     */
    notesContainer.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-note]');

        if (!button) {
            return;
        }

        const wrapper = button.closest('[data-note-wrapper]');

        if (!wrapper || wrapper.dataset.existing === '1') {
            return;
        }

        const token = getToken(wrapper);

        document.querySelectorAll('[data-detail-note-select]').forEach(select => {
            if (select.value === token) {
                select.value = '';
            }
        });

        wrapper.remove();

        refreshAll();
    });

    /*
     * PERUBAHAN PILIHAN ALOKASI BARANG
     */
    document.addEventListener('change', function (event) {
        if (event.target.matches('[data-detail-note-select]')) {
            updateTotals();
        }
    });

    /*
     * VALIDASI DAN KIRIM FORM
     */
    form.addEventListener('submit', function (event) {
        const detailRows = Array.from(
            document.querySelectorAll('[data-invoice-detail]')
        );

        const unallocated = detailRows.filter(row => {
            const select = row.querySelector('[data-detail-note-select]');
            return !select || !select.value;
        });

        if (unallocated.length > 0) {
            event.preventDefault();

            alert(
                'Masih ada ' + unallocated.length
                + ' detail barang yang belum dialokasikan.'
            );

            unallocated[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return;
        }

        const assignedTokens = new Set();

        detailRows.forEach(row => {
            const token = row.querySelector(
                '[data-detail-note-select]'
            )?.value;

            if (token) {
                assignedTokens.add(token);
            }
        });

        const usedWrappers = getWrappers().filter(
            wrapper => assignedTokens.has(getToken(wrapper))
        );

        for (const wrapper of usedWrappers) {
            const supplierId = getSupplier(wrapper);
            const sectionName = wrapper.querySelector('[data-section-name]');
            const sectionOrder = wrapper.querySelector('[data-section-order]');

            if (!supplierId) {
                event.preventDefault();

                alert('Pilih supplier untuk setiap bagian yang digunakan.');

                wrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return;
            }

            if (
                !sectionName?.value.trim() ||
                !Number.isInteger(Number(sectionOrder?.value)) ||
                Number(sectionOrder?.value) < 1
            ) {
                event.preventDefault();

                alert('Nama bagian dan urutan bagian wajib diisi dengan benar.');

                wrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return;
            }
        }

        /*
         * Hapus hidden detail_ids dari submit sebelumnya bila form dicoba
         * dikirim ulang setelah validasi gagal.
         */
        form.querySelectorAll('[data-generated-detail-id]').forEach(input => {
            input.remove();
        });

        /*
         * Buat field detail_ids sesuai pilihan pada dropdown.
         */
        usedWrappers.forEach(wrapper => {
            const index = wrapper.dataset.noteIndex;
            const token = getToken(wrapper);

            detailRows.forEach(row => {
                const select = row.querySelector('[data-detail-note-select]');

                if (select?.value !== token) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `notes[${index}][detail_ids][]`;
                input.value = row.dataset.detailId;
                input.dataset.generatedDetailId = '1';

                form.appendChild(input);
            });
        });
    });

    /*
     * MUAT PILIHAN ALOKASI LAMA DAN HITUNG SUBTOTAL AWAL.
     */
    refreshAll();
});
</script>
</body>
</html>