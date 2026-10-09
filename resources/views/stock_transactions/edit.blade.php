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
            background-color: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .page-container {
            max-width: 1500px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .main-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.07);
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .table th {
            background-color: #f0f3f8;
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

        .item-select {
            min-width: 200px;
        }

        .item-supplier {
            min-width: 180px;
        }

        .item-code {
            min-width: 130px;
        }

        .item-unit {
            min-width: 100px;
        }

        .item-quantity,
        .item-price {
            min-width: 120px;
        }

        .item-subtotal {
            min-width: 130px;
        }

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

        .required-mark {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .main-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="page-container">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="page-title">Edit Barang Keluar</h3>
            <div class="text-muted">
                Ubah tanggal transaksi, catatan, supplier, kode barang, satuan, dan rincian barang keluar.
            </div>
        </div>

        <a href="{{ route('stock-transactions.out') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
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

        <div class="mb-4">
            <h5 class="fw-bold mb-3">Informasi Transaksi</h5>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Nomor Transaksi</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $stockTransaction->transaction_number }}"
                           readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted">Nomor PO</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $stockTransaction->purchaseOrder?->po_number ?? '-' }}"
                           readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted">Dapur SPPG</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $stockTransaction->kitchen?->name ?? '-' }}"
                           readonly>
                </div>
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

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="fw-bold mb-0">Daftar Barang Keluar</h5>

                <button type="button"
                        class="btn btn-outline-primary"
                        id="addItemButton">
                    + Tambah Barang
                </button>
            </div>

            @php
                $oldItems = old('items');

                if (is_array($oldItems)) {
                    $displayItems = $oldItems;
                } else {
                    $displayItems = $stockTransaction->details->map(function ($detail) {
                        return [
                            'item_id' => $detail->item_id,
                            'supplier_id' => $detail->supplier_id ?? $detail->item?->supplier_id ?? '',
                            'code' => $detail->code ?? $detail->item?->code ?? '',
                            'unit' => $detail->unit ?? $detail->item?->unit ?? '',
                            'quantity' => $detail->quantity,
                            'unit_price' => $detail->unit_price,
                        ];
                    })->toArray();
                }
            @endphp

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga Satuan (Rp)</th>
                            <th>Subtotal (Rp)</th>
                            <th style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="itemsTableBody">

                        @forelse ($displayItems as $index => $detail)

                            @php
                                $selectedItem = $items->firstWhere('id', $detail['item_id'] ?? null);

                                $itemCode = $detail['code'] ?? ($selectedItem->code ?? '');
                                $itemUnit = $detail['unit'] ?? ($selectedItem->unit ?? '');

                                $selectedSupplierId = $detail['supplier_id']
                                    ?? ($selectedItem->supplier_id ?? '');
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
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][code]"
                                           class="form-control item-code"
                                           placeholder="Kode barang">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][unit]"
                                           class="form-control item-unit"
                                           placeholder="Satuan"
                                           required>
                                </td>

                                <td>
                                    <input type="number"
                                           name="items[0][quantity]"
                                           class="form-control item-quantity"
                                           value="1"
                                           min="0.01"
                                           step="0.01"
                                           required>
                                </td>

                                <td>
                                    <input type="number"
                                           name="items[0][unit_price]"
                                           class="form-control item-price"
                                           value="0"
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

                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="total-box mt-4 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">TOTAL BARANG KELUAR</span>

                    <span class="total-value">
                        Rp <span id="grandTotal">0</span>
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('stock-transactions.out') }}"
                   class="btn btn-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
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

    function updateRow(row, itemChanged = false) {
        const select = row.querySelector('.item-select');
        const selectedOption = select.options[select.selectedIndex];

        const codeInput = row.querySelector('.item-code');
        const unitInput = row.querySelector('.item-unit');
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        const subtotalInput = row.querySelector('.item-subtotal');
        const supplierSelect = row.querySelector('.item-supplier');

        if (select.value && selectedOption) {
            // Hanya isi otomatis ketika barang benar-benar diganti.
            // Edit manual kode/satuan tidak akan tertimpa saat qty/harga berubah.
            if (itemChanged) {
                codeInput.value = selectedOption.dataset.code || '';
                unitInput.value = selectedOption.dataset.unit || '';

                // Isi harga dan supplier awal dari master barang.
                priceInput.value = selectedOption.dataset.price || 0;

                const masterSupplier = selectedOption.dataset.supplier || '';

                if (masterSupplier) {
                    supplierSelect.value = masterSupplier;
                }
            }
        } else if (!select.value && itemChanged) {
            codeInput.value = '';
            unitInput.value = '';
        }

        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        subtotalInput.value = formatNumber(quantity * price);

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;

        tableBody.querySelectorAll('.item-row').forEach(function (row) {
            const quantity = parseFloat(
                row.querySelector('.item-quantity').value
            ) || 0;

            const price = parseFloat(
                row.querySelector('.item-price').value
            ) || 0;

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
    }

    function createRow(index) {
        const row = document.createElement('tr');
        row.className = 'item-row';

        row.innerHTML = `
            <td>
                <select name="items[${index}][item_id]"
                        class="form-select item-select"
                        required>
                    ${itemOptions}
                </select>
            </td>

            <td>
                <select name="items[${index}][supplier_id]"
                        class="form-select item-supplier"
                        required>
                    ${supplierOptions}
                </select>
            </td>

            <td>
                <input type="text"
                       name="items[${index}][code]"
                       class="form-control item-code"
                       placeholder="Kode barang">
            </td>

            <td>
                <input type="text"
                       name="items[${index}][unit]"
                       class="form-control item-unit"
                       placeholder="Satuan"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][quantity]"
                       class="form-control item-quantity"
                       value="1"
                       min="0.01"
                       step="0.01"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][unit_price]"
                       class="form-control item-price"
                       value="0"
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
        `;

        return row;
    }

    addButton.addEventListener('click', function () {
        const row = createRow(nextIndex);

        tableBody.appendChild(row);
        nextIndex++;

        updateRow(row);
    });

    tableBody.addEventListener('change', function (event) {
        if (event.target.classList.contains('item-select')) {
            const row = event.target.closest('.item-row');
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
        }
    });

    tableBody.addEventListener('input', function (event) {
        if (
            event.target.classList.contains('item-quantity') ||
            event.target.classList.contains('item-price')
        ) {
            updateRow(event.target.closest('.item-row'));
        }
    });

    tableBody.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-item');

        if (!removeButton) {
            return;
        }

        const rows = tableBody.querySelectorAll('.item-row');

        if (rows.length <= 1) {
            alert('Minimal harus ada satu baris barang.');
            return;
        }

        removeButton.closest('.item-row').remove();

        renumberRows();
        updateGrandTotal();
    });

    form.addEventListener('submit', function (event) {
        const rows = tableBody.querySelectorAll('.item-row');

        let hasValidItem = false;
        let hasMissingSupplier = false;
        let hasMissingUnit = false;

        rows.forEach(function (row) {
            const itemId = row.querySelector('.item-select').value;
            const supplierId = row.querySelector('.item-supplier').value;
            const unit = row.querySelector('.item-unit').value.trim();

            const quantity = parseFloat(
                row.querySelector('.item-quantity').value
            ) || 0;

            if (itemId && quantity > 0) {
                hasValidItem = true;

                if (!supplierId) {
                    hasMissingSupplier = true;
                }

                if (!unit) {
                    hasMissingUnit = true;
                }
            }
        });

        if (!hasValidItem) {
            event.preventDefault();
            alert('Pilih minimal satu barang dengan jumlah lebih dari nol.');
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
        }
    });

    // Hitung subtotal awal tanpa menimpa kode dan satuan yang sudah tersimpan.
    tableBody.querySelectorAll('.item-row').forEach(function (row) {
        updateRow(row);
    });

    updateGrandTotal();
});
</script>

</body>
</html>