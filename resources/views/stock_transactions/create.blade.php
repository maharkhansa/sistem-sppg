@extends('layouts.app')

@section('title', 'Tambah Barang Masuk')

@push('styles')
<style>
    .page-title {
        font-size: 1.45rem;
        font-weight: 700;
    }

    .purchase-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        background: #fff;
    }

    .purchase-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        font-weight: 700;
    }

    .purchase-card-body {
        padding: 20px;
    }

    .form-label {
        font-weight: 600;
    }

    .table th {
        white-space: nowrap;
        font-size: .85rem;
        background: #f8fafc;
    }

    .table td {
        vertical-align: middle;
    }

    .item-number {
        min-width: 100px;
    }

    .total-panel {
        max-width: 460px;
        margin-left: auto;
        padding: 18px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 10px;
    }

    .total-grand {
        padding-top: 12px;
        margin-top: 12px;
        border-top: 1px solid #d1d5db;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .currency {
        white-space: nowrap;
    }

    .item-search-wrapper {
        position: relative;
    }

    .item-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-top: 4px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .10);
        display: none;
    }

    .item-search-results.show {
        display: block;
    }

    .item-search-result {
        display: block;
        width: 100%;
        padding: 11px 14px;
        text-align: left;
        background: #fff;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }

    .item-search-result:last-child {
        border-bottom: 0;
    }

    .item-search-result:hover,
    .item-search-result:focus {
        background: #f0f7ff;
        outline: none;
    }

    .item-search-result:disabled {
        cursor: not-allowed;
        opacity: .6;
        background: #f8fafc;
    }

    .item-search-result strong {
        display: block;
        color: #1f2937;
    }

    .item-search-result small {
        color: #64748b;
    }

    .item-search-empty {
        padding: 14px;
        color: #64748b;
        font-size: .9rem;
    }

    @media (max-width: 768px) {
        .purchase-card-body {
            padding: 14px;
        }

        .total-panel {
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="page-title mb-1">Tambah Barang Masuk</h1>
            <div class="text-muted">Catat pembelian barang dari nota supplier.</div>
        </div>

        <a href="{{ route('stock-transactions.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">Terjadi kesalahan:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stock-transactions.store') }}"
          method="POST"
          id="barangMasukForm">
        @csrf

        {{-- INFORMASI NOTA --}}
        <div class="purchase-card">
            <div class="purchase-card-header">
                <i class="bi bi-receipt me-2"></i>Informasi Nota Pembelian
            </div>

            <div class="purchase-card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="purchase_note_number" class="form-label">
                            Nomor Nota Pembelian <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               id="purchase_note_number"
                               name="purchase_note_number"
                               class="form-control @error('purchase_note_number') is-invalid @enderror"
                               value="{{ old('purchase_note_number') }}"
                               maxlength="100"
                               required>

                        @error('purchase_note_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="vendor_name" class="form-label">
                            Nama Vendor / Supplier <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               id="vendor_name"
                               name="vendor_name"
                               class="form-control @error('vendor_name') is-invalid @enderror"
                               value="{{ old('vendor_name') }}"
                               maxlength="150"
                               required>

                        @error('vendor_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="transaction_date" class="form-label">
                            Tanggal Pembelian <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               id="transaction_date"
                               name="transaction_date"
                               class="form-control @error('transaction_date') is-invalid @enderror"
                               value="{{ old('transaction_date', date('Y-m-d')) }}"
                               required>

                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="porter_cost" class="form-label">
                            Biaya Kuli / Angkut (Rp)
                        </label>

                        <input type="number"
                               id="porter_cost"
                               name="porter_cost"
                               class="form-control @error('porter_cost') is-invalid @enderror"
                               value="{{ old('porter_cost', 0) }}"
                               min="0"
                               step="0.01">

                        @error('porter_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="purchase_discount_type" class="form-label">
                            Jenis Diskon Tambahan Nota
                            <span class="text-danger">*</span>
                        </label>

                        <select name="purchase_discount_type"
                                id="purchase_discount_type"
                                class="form-select @error('purchase_discount_type') is-invalid @enderror"
                                required>
                            <option value="amount"
                                {{ old('purchase_discount_type', 'amount') === 'amount' ? 'selected' : '' }}>
                                Nominal (Rp)
                            </option>

                            <option value="percent"
                                {{ old('purchase_discount_type') === 'percent' ? 'selected' : '' }}>
                                Persentase (%)
                            </option>
                        </select>

                        @error('purchase_discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="purchase_discount_value" class="form-label">
                            Nilai Diskon Tambahan Nota
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="purchase_discount_value"
                               id="purchase_discount_value"
                               class="form-control @error('purchase_discount_value') is-invalid @enderror"
                               value="{{ old('purchase_discount_value', 0) }}"
                               min="0"
                               max="9999999999999.99"
                               step="0.01"
                               required>

                        @error('purchase_discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-text">
                            Diskon persentase maksimal 100%. Diskon tambahan nota tidak boleh melebihi subtotal barang.
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>

                        <textarea name="notes"
                                  id="notes"
                                  rows="2"
                                  maxlength="2000"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>

                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- DAFTAR BARANG --}}
        <div class="purchase-card">
            <div class="purchase-card-header">
                <i class="bi bi-box-seam me-2"></i>Daftar Barang Masuk
            </div>

            <div class="purchase-card-body">

                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label for="itemSearch" class="form-label">
                            Cari Nama atau Kode Barang
                        </label>

                        <div class="item-search-wrapper">
                            <input type="text"
                                   id="itemSearch"
                                   class="form-control"
                                   placeholder="Ketik nama atau kode barang..."
                                   autocomplete="off">

                            <div id="itemSearchResults"
                                 class="item-search-results"></div>
                        </div>

                        <div class="form-text">
                            Klik nama barang pada hasil pencarian untuk langsung memasukkannya ke daftar transaksi.
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:50px">No.</th>
                                <th style="min-width:230px">Nama Barang</th>
                                <th style="min-width:110px">Jumlah</th>
                                <th style="min-width:150px">Harga Satuan (Rp)</th>
                                <th style="min-width:170px">Diskon per Jenis Barang (Rp)</th>
                                <th style="min-width:160px">Subtotal (Rp)</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>

                <div class="text-muted small">
                    Subtotal dihitung dari (jumlah × harga satuan) − diskon satu kali untuk jenis barang tersebut.
                    Diskon tidak dikalikan dengan jumlah barang. Barang yang sama hanya dapat dimasukkan satu kali.
                </div>

                {{-- TOTAL PEMBELIAN --}}
                <div class="total-panel mt-4">
                    <div class="total-row">
                        <span>Subtotal Barang</span>
                        <strong class="currency" id="goodsTotalLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Diskon Tambahan Nota</span>
                        <strong class="currency text-danger" id="purchaseDiscountLabel">− Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya Kuli / Angkut</span>
                        <strong class="currency" id="porterCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row total-grand">
                        <span>Total Pembelian</span>
                        <strong class="currency" id="grandTotalLabel">Rp0</strong>
                    </div>
                </div>

                <input type="hidden" id="goodsTotal" value="0">
                <input type="hidden" id="purchaseDiscountAmount" value="0">
                <input type="hidden" id="grandTotal" value="0">

            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
            <a href="{{ route('stock-transactions.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-success" id="submitButton">
                <i class="bi bi-save me-1"></i> Simpan Barang Masuk
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = {{ Illuminate\Support\Js::from(
        $items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code ?? '',
            'unit' => $item->unit ?? '',
            'price' => (float) ($item->price ?? 0),
        ])->values()
    ) }};

    const oldItems = {{ Illuminate\Support\Js::from(old('items', [])) }};

    const tbody = document.getElementById('itemsTableBody');
    const itemSearch = document.getElementById('itemSearch');
    const searchResults = document.getElementById('itemSearchResults');

    const porterInput = document.getElementById('porter_cost');
    const discountTypeInput = document.getElementById('purchase_discount_type');
    const discountValueInput = document.getElementById('purchase_discount_value');
    const form = document.getElementById('barangMasukForm');

    let rowIndex = 0;

    const formatNumber = value => new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(Number(value) || 0);

    const formatRupiah = value => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2
    }).format(Number(value) || 0);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function getItem(itemId) {
        return items.find(item => String(item.id) === String(itemId));
    }

    function isItemAlreadyAdded(itemId) {
        return Array.from(tbody.querySelectorAll('.item-id-input'))
            .some(input => String(input.value) === String(itemId));
    }

    function updateRowNumbers() {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            const number = row.querySelector('.row-number');

            if (number) {
                number.textContent = index + 1;
            }
        });
    }

    function hideSearchResults() {
        searchResults.classList.remove('show');
        searchResults.innerHTML = '';
    }

    function renderSearchResults() {
        const keyword = itemSearch.value.trim().toLowerCase();

        hideSearchResults();

        if (!keyword) {
            return;
        }

        const matches = items.filter(item => {
            const name = String(item.name || '').toLowerCase();
            const code = String(item.code || '').toLowerCase();

            return name.includes(keyword) || code.includes(keyword);
        }).slice(0, 30);

        if (matches.length === 0) {
            searchResults.innerHTML = `
                <div class="item-search-empty">
                    Barang tidak ditemukan. Periksa kembali nama atau kode barang.
                </div>
            `;

            searchResults.classList.add('show');
            return;
        }

        searchResults.innerHTML = matches.map(item => {
            const alreadyAdded = isItemAlreadyAdded(item.id);

            return `
                <button type="button"
                        class="item-search-result"
                        data-item-id="${escapeHtml(item.id)}"
                        ${alreadyAdded ? 'disabled' : ''}>
                    <strong>${escapeHtml(
                        (item.code ? item.code + ' - ' : '') + item.name
                    )}</strong>

                    <small>
                        Satuan: ${escapeHtml(item.unit || '-')}
                        &nbsp;|&nbsp;
                        Harga: ${escapeHtml(formatRupiah(item.price))}
                        ${alreadyAdded ? '&nbsp;|&nbsp; Sudah ditambahkan' : ''}
                    </small>
                </button>
            `;
        }).join('');

        searchResults.classList.add('show');
    }

    /*
     * Subtotal satu baris:
     * (jumlah × harga satuan) - diskon satu jenis barang.
     *
     * Contoh:
     * 10 × Rp7.000 = Rp70.000
     * Rp70.000 - Rp1.000 = Rp69.000
     */
    function calculateRow(row) {
        const quantity = Math.max(
            0,
            Number(row.querySelector('.quantity-input').value) || 0
        );

        const price = Math.max(
            0,
            Number(row.querySelector('.price-input').value) || 0
        );

        const discount = Math.max(
            0,
            Number(row.querySelector('.discount-input').value) || 0
        );

        const subtotalBeforeDiscount = quantity * price;
        const safeDiscount = Math.min(discount, subtotalBeforeDiscount);
        const subtotal = Math.max(0, subtotalBeforeDiscount - safeDiscount);

        row.querySelector('.subtotal-input').value = formatNumber(subtotal);
        row.querySelector('.subtotal-value').value = subtotal.toFixed(2);

        return subtotal;
    }

    function calculateTotals() {
        let goodsTotal = 0;

        tbody.querySelectorAll('tr').forEach(row => {
            goodsTotal += calculateRow(row);
        });

        const type = discountTypeInput.value;
        const value = Math.max(
            0,
            Number(discountValueInput.value) || 0
        );

        let discountAmount = type === 'percent'
            ? goodsTotal * value / 100
            : value;

        discountAmount = Math.min(
            goodsTotal,
            Math.max(0, discountAmount)
        );

        const porterCost = Math.max(
            0,
            Number(porterInput.value) || 0
        );

        const grandTotal = Math.max(
            0,
            goodsTotal - discountAmount + porterCost
        );

        document.getElementById('goodsTotal').value = goodsTotal.toFixed(2);
        document.getElementById('purchaseDiscountAmount').value = discountAmount.toFixed(2);
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);

        document.getElementById('goodsTotalLabel').textContent =
            formatRupiah(goodsTotal);

        document.getElementById('purchaseDiscountLabel').textContent =
            '− ' + formatRupiah(discountAmount);

        document.getElementById('porterCostLabel').textContent =
            formatRupiah(porterCost);

        document.getElementById('grandTotalLabel').textContent =
            formatRupiah(grandTotal);
    }

    function addRow(data = {}) {
        const item = getItem(data.item_id);

        if (!item) {
            alert('Barang tidak ditemukan. Silakan cari barang kembali.');
            return false;
        }

        if (isItemAlreadyAdded(item.id)) {
            alert('Barang "' + item.name + '" sudah ada dalam daftar.');
            return false;
        }

        const row = document.createElement('tr');
        const index = rowIndex++;

        const quantity = data.quantity ?? 1;
        const unitPrice = data.unit_price ?? item.price ?? 0;
        const discount = data.discount ?? 0;
        const unit = data.unit || item.unit || '';

        row.innerHTML = `
            <td class="text-center row-number"></td>

            <td>
                <input type="hidden"
                       name="items[${index}][item_id]"
                       class="item-id-input"
                       value="${escapeHtml(item.id)}">

                <input type="hidden"
                       name="items[${index}][unit]"
                       value="${escapeHtml(unit)}">

                <div class="fw-semibold">
                    ${escapeHtml(item.name)}
                </div>

                <div class="small text-muted">
                    ${escapeHtml(item.code || 'Tanpa kode')}
                    ${unit ? ' · Satuan: ' + escapeHtml(unit) : ''}
                </div>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][quantity]"
                       class="form-control item-number quantity-input"
                       value="${escapeHtml(quantity)}"
                       min="0"
                       step="0.001"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][unit_price]"
                       class="form-control item-number price-input"
                       value="${escapeHtml(unitPrice)}"
                       min="0"
                       step="0.01"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][discount]"
                       class="form-control item-number discount-input"
                       value="${escapeHtml(discount)}"
                       min="0"
                       step="0.01">
            </td>

            <td>
                <input type="text"
                       class="form-control subtotal-input text-end"
                       value="0,00"
                       readonly>

                <input type="hidden"
                       class="subtotal-value">
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger remove-row"
                        title="Hapus barang">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        row.querySelectorAll('.quantity-input, .price-input, .discount-input')
            .forEach(input => {
                input.addEventListener('input', function () {
                    const quantityValue = Number(
                        row.querySelector('.quantity-input').value
                    );

                    const priceValue = Number(
                        row.querySelector('.price-input').value
                    );

                    const discountInput = row.querySelector('.discount-input');
                    const discountValue = Number(discountInput.value) || 0;

                    const quantity = Number.isFinite(quantityValue)
                        ? Math.max(0, quantityValue)
                        : 0;

                    const price = Number.isFinite(priceValue)
                        ? Math.max(0, priceValue)
                        : 0;

                    const subtotalBeforeDiscount = quantity * price;

                    if (discountValue > subtotalBeforeDiscount) {
                        discountInput.setCustomValidity(
                            'Diskon tidak boleh melebihi subtotal jumlah barang.'
                        );
                    } else {
                        discountInput.setCustomValidity('');
                    }

                    calculateTotals();
                });
            });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            updateRowNumbers();
            calculateTotals();
            renderSearchResults();
        });

        updateRowNumbers();
        calculateTotals();

        return true;
    }

    // Ketik nama atau kode barang untuk mencari.
    itemSearch.addEventListener('input', renderSearchResults);

    // Klik hasil pencarian langsung menambahkan barang ke tabel.
    searchResults.addEventListener('click', function (event) {
        const button = event.target.closest('[data-item-id]');

        if (!button || button.disabled) {
            return;
        }

        const item = getItem(button.dataset.itemId);

        if (!item) {
            return;
        }

        const added = addRow({
            item_id: item.id,
            quantity: 1,
            unit_price: item.price,
            discount: 0,
            unit: item.unit
        });

        if (added) {
            itemSearch.value = '';
            hideSearchResults();
            itemSearch.focus();
        }
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.item-search-wrapper')) {
            hideSearchResults();
        }
    });

    porterInput.addEventListener('input', calculateTotals);
    discountTypeInput.addEventListener('change', calculateTotals);
    discountValueInput.addEventListener('input', calculateTotals);

    // Validasi form sebelum dikirim.
    form.addEventListener('submit', function (event) {
        const rows = Array.from(tbody.querySelectorAll('tr'));

        if (rows.length === 0) {
            event.preventDefault();
            alert('Tambahkan minimal satu barang.');
            return;
        }

        const selectedIds = rows.map(row => {
            return row.querySelector('.item-id-input').value;
        });

        if (selectedIds.some(id => !id)) {
            event.preventDefault();
            alert('Pastikan semua barang sudah dipilih dengan benar.');
            return;
        }

        if (new Set(selectedIds).size !== selectedIds.length) {
            event.preventDefault();
            alert('Barang yang sama tidak boleh ditambahkan dua kali.');
            return;
        }

        for (const row of rows) {
            const quantity = Number(
                row.querySelector('.quantity-input').value
            );

            const price = Number(
                row.querySelector('.price-input').value
            );

            const discount = Number(
                row.querySelector('.discount-input').value
            ) || 0;

            const subtotalBeforeDiscount = quantity * price;

            if (
                !Number.isFinite(quantity) ||
                !Number.isFinite(price) ||
                !Number.isFinite(discount) ||
                quantity <= 0 ||
                price < 0 ||
                discount < 0 ||
                discount > subtotalBeforeDiscount
            ) {
                event.preventDefault();

                alert(
                    'Periksa jumlah, harga satuan, dan diskon setiap barang. ' +
                    'Diskon tidak boleh melebihi subtotal jumlah barang.'
                );

                return;
            }
        }

        const discountType = discountTypeInput.value;
        const discountValue = Number(discountValueInput.value);

        if (
            !discountType ||
            !Number.isFinite(discountValue) ||
            discountValue < 0
        ) {
            event.preventDefault();
            alert('Jenis dan nilai diskon tambahan nota wajib diisi dengan benar.');
            return;
        }

        if (discountType === 'percent' && discountValue > 100) {
            event.preventDefault();
            alert('Diskon persentase tidak boleh lebih dari 100%.');
            return;
        }

        const goodsTotal =
            Number(document.getElementById('goodsTotal').value) || 0;

        const discountAmount =
            Number(document.getElementById('purchaseDiscountAmount').value) || 0;

        if (discountAmount > goodsTotal) {
            event.preventDefault();
            alert('Diskon tambahan nota tidak boleh melebihi subtotal barang.');
        }
    });

    // Pulihkan barang lama jika validasi server gagal.
    if (Array.isArray(oldItems) && oldItems.length > 0) {
        oldItems.forEach(item => {
            addRow(item);
        });
    }

    calculateTotals();
});
</script>
@endpush