<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang Keluar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .page-container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .main-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, .07);
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .table th {
            background: #f0f3f8;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table td {
            vertical-align: middle;
        }

        .form-control,
        .form-select {
            min-height: 38px;
        }

        .item-select { min-width: 200px; }
        .item-supplier { min-width: 170px; }
        .item-section { min-width: 160px; }
        .item-code { min-width: 120px; }
        .item-unit { min-width: 90px; }
        .item-quantity,
        .item-price { min-width: 120px; }
        .item-subtotal { min-width: 130px; }

        .total-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px 20px;
        }

        .total-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #198754;
        }

        .required-mark { color: #dc3545; }

        @media (max-width: 768px) {
            .main-card { padding: 15px; }
        }
    </style>
</head>

<body>
<div class="page-container">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="page-title">Edit Barang Keluar</h3>
            <div class="text-muted">
                Ubah rincian barang keluar. Bagian PO dipertahankan untuk pengelompokan tabel Invoice.
            </div>
        </div>

        <a href="{{ route('stock-transactions.out') }}"
           class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="main-card">

        <h5 class="fw-bold mb-3">Informasi Transaksi</h5>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label text-muted">Nomor Transaksi</label>
                <input class="form-control"
                       value="{{ $stockTransaction->transaction_number }}"
                       readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted">Nomor PO</label>
                <input class="form-control"
                       value="{{ $stockTransaction->purchaseOrder?->po_number ?? '-' }}"
                       readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted">Dapur SPPG</label>
                <input class="form-control"
                       value="{{ $stockTransaction->kitchen?->name ?? '-' }}"
                       readonly>
            </div>
        </div>

        <hr>

        <form action="{{ route('stock-transactions.update', $stockTransaction->id) }}"
              method="POST"
              id="editTransactionForm">

            @csrf
            @method('PUT')

            <h5 class="fw-bold mb-3">Detail Transaksi</h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="transaction_date" class="form-label">
                        Tanggal Transaksi <span class="required-mark">*</span>
                    </label>

                    <input type="date"
                           name="transaction_date"
                           id="transaction_date"
                           class="form-control @error('transaction_date') is-invalid @enderror"
                           value="{{ old('transaction_date', $stockTransaction->transaction_date ? \Illuminate\Support\Carbon::parse($stockTransaction->transaction_date)->format('Y-m-d') : '') }}"
                           required>

                    @error('transaction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="notes" class="form-label">Catatan</label>
                    <input type="text"
                           name="notes"
                           id="notes"
                           class="form-control"
                           value="{{ old('notes', $stockTransaction->notes) }}"
                           placeholder="Catatan transaksi">
                </div>
            </div>

            @php
                /*
                 * Jika detail Barang Keluar belum memiliki Bagian,
                 * coba ambil nama Bagian dari detail PO berdasarkan barang
                 * dan supplier yang sama.
                 */
                $purchaseOrderDetails = $stockTransaction->purchaseOrder?->details ?? collect();

                $oldItems = old('items');

                if (is_array($oldItems)) {
                    $displayItems = $oldItems;
                } else {
                    $displayItems = $stockTransaction->details->map(function ($detail) use ($purchaseOrderDetails) {
                        $poDetail = $purchaseOrderDetails->first(function ($po) use ($detail) {
                            return (string) $po->item_id === (string) $detail->item_id
                                && (
                                    empty($detail->supplier_id)
                                    || (string) ($po->supplier_id ?? '') === (string) $detail->supplier_id
                                );
                        });

                        return [
                            'item_id' => $detail->item_id,
                            'supplier_id' => $detail->supplier_id
                                ?? $detail->item?->supplier_id
                                ?? $poDetail?->supplier_id
                                ?? '',
                            'code' => $detail->code ?? $detail->item?->code ?? '',
                            'unit' => $detail->unit ?? $detail->item?->unit ?? '',
                            'quantity' => $detail->quantity,
                            'unit_price' => $detail->unit_price,
                            'section_name' => $detail->section_name
                                ?? $poDetail?->section_name
                                ?? '',
                            'section_order' => $detail->section_order
                                ?? $poDetail?->section_order
                                ?? 0,
                        ];
                    })->toArray();
                }

                $sectionNames = collect($displayItems)
                    ->pluck('section_name')
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-1">Daftar Barang Keluar</h5>
                    <small class="text-muted">
                        Isi Bagian sesuai PO. Gunakan nama Bagian yang sama untuk barang dalam tabel Invoice yang sama.
                    </small>
                </div>

                <button type="button"
                        class="btn btn-outline-primary"
                        id="addItemButton">
                    + Tambah Barang
                </button>
            </div>

            <datalist id="sectionOptions">
                @foreach ($sectionNames as $sectionName)
                    <option value="{{ $sectionName }}"></option>
                @endforeach
            </datalist>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Bagian PO</th>
                            <th>Kode Barang</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga Satuan (Rp)</th>
                            <th>Subtotal (Rp)</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="itemsTableBody">
                    @forelse ($displayItems as $index => $detail)
                        @php
                            $selectedItem = $items->firstWhere('id', $detail['item_id'] ?? null);
                            $itemCode = $detail['code'] ?? ($selectedItem->code ?? '');
                            $itemUnit = $detail['unit'] ?? ($selectedItem->unit ?? '');
                            $selectedSupplierId = $detail['supplier_id'] ?? ($selectedItem->supplier_id ?? '');
                        @endphp

                        <tr class="item-row">
                            <td>
                                <select name="items[{{ $index }}][item_id]"
                                        class="form-select item-select"
                                        required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}"
                                                data-code="{{ $item->code }}"
                                                data-unit="{{ $item->unit }}"
                                                data-price="{{ $item->price ?? 0 }}"
                                                data-supplier="{{ $item->supplier_id ?? '' }}"
                                            @selected((string) ($detail['item_id'] ?? '') === (string) $item->id)>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <select name="items[{{ $index }}][supplier_id]"
                                        class="form-select item-supplier"
                                        required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            @selected((string) $selectedSupplierId === (string) $supplier->id)>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][section_name]"
                                       class="form-control item-section"
                                       list="sectionOptions"
                                       value="{{ $detail['section_name'] ?? '' }}"
                                       placeholder="Nama Bagian">

                                <input type="hidden"
                                       name="items[{{ $index }}][section_order]"
                                       class="item-section-order"
                                       value="{{ $detail['section_order'] ?? 0 }}">
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][code]"
                                       class="form-control item-code"
                                       value="{{ $itemCode }}"
                                       placeholder="Kode barang">
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][unit]"
                                       class="form-control item-unit"
                                       value="{{ $itemUnit }}"
                                       placeholder="Satuan"
                                       required>
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][quantity]"
                                       class="form-control item-quantity"
                                       value="{{ $detail['quantity'] ?? 1 }}"
                                       min="0.01"
                                       step="0.01"
                                       required>
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][unit_price]"
                                       class="form-control item-price"
                                       value="{{ $detail['unit_price'] ?? 0 }}"
                                       min="0"
                                       step="0.01"
                                       required>
                            </td>

                            <td>
                                <input type="text"
                                       class="form-control item-subtotal"
                                       value="0"
                                       readonly>
                            </td>

                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger remove-item">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="item-row">
                            <td>
                                <select name="items[0][item_id]"
                                        class="form-select item-select"
                                        required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}"
                                                data-code="{{ $item->code }}"
                                                data-unit="{{ $item->unit }}"
                                                data-price="{{ $item->price ?? 0 }}"
                                                data-supplier="{{ $item->supplier_id ?? '' }}">
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <select name="items[0][supplier_id]"
                                        class="form-select item-supplier"
                                        required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                       name="items[0][section_name]"
                                       class="form-control item-section"
                                       list="sectionOptions"
                                       placeholder="Nama Bagian">
                                <input type="hidden"
                                       name="items[0][section_order]"
                                       class="item-section-order"
                                       value="0">
                            </td>

                            <td>
                                <input type="text" name="items[0][code]"
                                       class="form-control item-code" placeholder="Kode barang">
                            </td>

                            <td>
                                <input type="text" name="items[0][unit]"
                                       class="form-control item-unit" placeholder="Satuan" required>
                            </td>

                            <td>
                                <input type="number" name="items[0][quantity]"
                                       class="form-control item-quantity" value="1"
                                       min="0.01" step="0.01" required>
                            </td>

                            <td>
                                <input type="number" name="items[0][unit_price]"
                                       class="form-control item-price" value="0"
                                       min="0" step="0.01" required>
                            </td>

                            <td>
                                <input type="text" class="form-control item-subtotal"
                                       value="0" readonly>
                            </td>

                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger remove-item">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="total-box mt-4 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">TOTAL BARANG KELUAR</span>
                    <span class="total-value">Rp <span id="grandTotal">0</span></span>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('stock-transactions.out') }}"
                   class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('itemsTableBody');
    const addButton = document.getElementById('addItemButton');
    const grandTotalElement = document.getElementById('grandTotal');
    const form = document.getElementById('editTransactionForm');

    let nextIndex = tableBody.querySelectorAll('.item-row').length;

    const supplierOptions = `
        <option value="">-- Pilih Supplier --</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
        @endforeach
    `;

    const itemOptions = `
        <option value="">-- Pilih Barang --</option>
        @foreach ($items as $item)
            <option value="{{ $item->id }}"
                    data-code="{{ $item->code }}"
                    data-unit="{{ $item->unit }}"
                    data-price="{{ $item->price ?? 0 }}"
                    data-supplier="{{ $item->supplier_id ?? '' }}">
                {{ $item->name }}
            </option>
        @endforeach
    `;

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(number);
    }

    function updateSectionOrders() {
        /*
         * Bagian dengan nama yang sama dan supplier yang sama
         * mendapat urutan yang sama.
         */
        const orders = new Map();
        let nextOrder = 1;

        tableBody.querySelectorAll('.item-row').forEach(function (row) {
            const supplierId = row.querySelector('.item-supplier').value;
            const sectionName = row.querySelector('.item-section').value.trim();
            const orderInput = row.querySelector('.item-section-order');

            if (!sectionName) {
                orderInput.value = 0;
                return;
            }

            const key = supplierId + '|' + sectionName.toLocaleLowerCase('id-ID');

            if (!orders.has(key)) {
                orders.set(key, nextOrder++);
            }

            orderInput.value = orders.get(key);
        });
    }

    function updateRow(row, itemChanged = false) {
        const select = row.querySelector('.item-select');
        const option = select.options[select.selectedIndex];

        const codeInput = row.querySelector('.item-code');
        const unitInput = row.querySelector('.item-unit');
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        const supplierSelect = row.querySelector('.item-supplier');

        if (select.value && option && itemChanged) {
            codeInput.value = option.dataset.code || '';
            unitInput.value = option.dataset.unit || '';
            priceInput.value = option.dataset.price || 0;

            if (option.dataset.supplier) {
                supplierSelect.value = option.dataset.supplier;
            }
        } else if (!select.value && itemChanged) {
            codeInput.value = '';
            unitInput.value = '';
        }

        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        row.querySelector('.item-subtotal').value = formatNumber(quantity * price);

        updateSectionOrders();
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;

        tableBody.querySelectorAll('.item-row').forEach(function (row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = quantity * price;

            row.querySelector('.item-subtotal').value = formatNumber(subtotal);
            total += subtotal;
        });

        grandTotalElement.textContent = formatNumber(total);
    }

    function renumberRows() {
        tableBody.querySelectorAll('.item-row').forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (input) {
                const name = input.getAttribute('name');

                if (name) {
                    input.setAttribute(
                        'name',
                        name.replace(/items\[\d+\]/, 'items[' + index + ']')
                    );
                }
            });
        });

        nextIndex = tableBody.querySelectorAll('.item-row').length;
        updateSectionOrders();
    }

    function createRow(index) {
        const row = document.createElement('tr');
        row.className = 'item-row';

        row.innerHTML = `
            <td>
                <select name="items[${index}][item_id]"
                        class="form-select item-select" required>
                    ${itemOptions}
                </select>
            </td>

            <td>
                <select name="items[${index}][supplier_id]"
                        class="form-select item-supplier" required>
                    ${supplierOptions}
                </select>
            </td>

            <td>
                <input type="text"
                       name="items[${index}][section_name]"
                       class="form-control item-section"
                       list="sectionOptions"
                       placeholder="Nama Bagian">
                <input type="hidden"
                       name="items[${index}][section_order]"
                       class="item-section-order"
                       value="0">
            </td>

            <td>
                <input type="text" name="items[${index}][code]"
                       class="form-control item-code" placeholder="Kode barang">
            </td>

            <td>
                <input type="text" name="items[${index}][unit]"
                       class="form-control item-unit" placeholder="Satuan" required>
            </td>

            <td>
                <input type="number" name="items[${index}][quantity]"
                       class="form-control item-quantity" value="1"
                       min="0.01" step="0.01" required>
            </td>

            <td>
                <input type="number" name="items[${index}][unit_price]"
                       class="form-control item-price" value="0"
                       min="0" step="0.01" required>
            </td>

            <td>
                <input type="text" class="form-control item-subtotal"
                       value="0" readonly>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    Hapus
                </button>
            </td>
        `;

        return row;
    }

    addButton.addEventListener('click', function () {
        tableBody.appendChild(createRow(nextIndex++));
        updateSectionOrders();
        updateGrandTotal();
    });

    tableBody.addEventListener('change', function (event) {
        const row = event.target.closest('.item-row');

        if (!row) return;

        if (event.target.classList.contains('item-select')) {
            const selectedId = event.target.value;

            if (selectedId) {
                const duplicate = Array.from(
                    tableBody.querySelectorAll('.item-select')
                ).some(function (select) {
                    return select !== event.target && select.value === selectedId;
                });

                if (duplicate) {
                    alert('Barang tersebut sudah ada dalam daftar transaksi.');
                    event.target.value = '';
                    updateRow(row, true);
                    return;
                }
            }

            updateRow(row, true);
            return;
        }

        if (
            event.target.classList.contains('item-supplier') ||
            event.target.classList.contains('item-section')
        ) {
            updateSectionOrders();
        }
    });

    tableBody.addEventListener('input', function (event) {
        const row = event.target.closest('.item-row');

        if (!row) return;

        if (
            event.target.classList.contains('item-quantity') ||
            event.target.classList.contains('item-price')
        ) {
            updateRow(row);
        }

        if (event.target.classList.contains('item-section')) {
            updateSectionOrders();
        }
    });

    tableBody.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-item');

        if (!button) return;

        if (tableBody.querySelectorAll('.item-row').length <= 1) {
            alert('Minimal harus ada satu baris barang.');
            return;
        }

        button.closest('.item-row').remove();
        renumberRows();
        updateGrandTotal();
    });

    form.addEventListener('submit', function (event) {
        const rows = tableBody.querySelectorAll('.item-row');

        let hasValidItem = false;
        let hasMissingSupplier = false;
        let hasMissingUnit = false;
        let hasDuplicateItem = false;
        const selectedIds = new Set();

        rows.forEach(function (row) {
            const itemId = row.querySelector('.item-select').value;
            const supplierId = row.querySelector('.item-supplier').value;
            const unit = row.querySelector('.item-unit').value.trim();
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;

            if (itemId && quantity > 0) {
                hasValidItem = true;

                if (!supplierId) hasMissingSupplier = true;
                if (!unit) hasMissingUnit = true;

                if (selectedIds.has(itemId)) {
                    hasDuplicateItem = true;
                }

                selectedIds.add(itemId);
            }
        });

        if (!hasValidItem) {
            event.preventDefault();
            alert('Pilih minimal satu barang dengan jumlah lebih dari nol.');
            return;
        }

        if (hasDuplicateItem) {
            event.preventDefault();
            alert('Barang yang sama tidak boleh dimasukkan lebih dari satu kali.');
            return;
        }

        if (hasMissingSupplier) {
            event.preventDefault();
            alert('Pilih supplier untuk setiap barang.');
            return;
        }

        if (hasMissingUnit) {
            event.preventDefault();
            alert('Satuan barang wajib diisi.');
            return;
        }

        updateSectionOrders();
    });

    tableBody.querySelectorAll('.item-row').forEach(function (row) {
        updateRow(row);
    });

    updateSectionOrders();
    updateGrandTotal();
});
</script>
</body>
</html>