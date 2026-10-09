<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Purchase Order</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            overflow-x: auto;
        }

        h1 {
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background: #f0f0f0;
        }

        .btn {
            padding: 9px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-add {
            background: #198754;
            color: white;
            margin-top: 15px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-save {
            background: #0d6efd;
            color: white;
            margin-top: 20px;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .subtotal {
            background: #f8f9fa;
        }

        .row-number {
            text-align: center;
        }

        .total-container {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }

        .total-container input {
            max-width: 250px;
            text-align: right;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>

<body>

<div class="container">

    <a href="{{ route('purchase-orders.index') }}" class="btn btn-back">
        ← Kembali
    </a>

    <h1>Tambah Purchase Order</h1>

    @if ($errors->any())
        <div class="error">
            <strong>Terdapat kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="error">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="po_date">Tanggal PO</label>
            <input
                type="date"
                name="po_date"
                id="po_date"
                value="{{ old('po_date', date('Y-m-d')) }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="kitchen_id">SPPG</label>
            <select name="kitchen_id" id="kitchen_id" required>
                <option value="">-- Pilih SPPG --</option>

                @foreach ($kitchens as $kitchen)
                    <option
                        value="{{ $kitchen->id }}"
                        {{ old('kitchen_id') == $kitchen->id ? 'selected' : '' }}
                    >
                        {{ $kitchen->id_sppg }} - {{ $kitchen->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Catatan</label>
            <textarea
                name="notes"
                id="notes"
                rows="3"
                placeholder="Catatan PO (opsional)"
            >{{ old('notes') }}</textarea>
        </div>

        <h3>Detail Barang</h3>

        <table id="itemsTable">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="220">Nama Barang</th>
                    <th width="180">Supplier</th>
                    <th width="150">Kode Barang</th>
                    <th width="100">Qty</th>
                    <th width="100">Satuan</th>
                    <th width="150">Harga</th>
                    <th width="150">Jumlah</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>

            <tbody id="itemsBody">
                <tr class="item-row">
                    <td class="row-number">1</td>

                    <td>
                        <select
                            name="items[0][item_id]"
                            class="item-select"
                            required
                        >
                            <option value="">-- Pilih Barang --</option>

                            @foreach ($items as $item)
                                <option
                                    value="{{ $item->id }}"
                                    data-code="{{ $item->code }}"
                                    data-unit="{{ $item->unit }}"
                                    data-supplier="{{ $item->supplier?->name ?? '-' }}"
                                    data-supplier-id="{{ $item->supplier_id }}"
                                    data-price="{{ $item->last_purchase_price ?? 0 }}"
                                    {{ old('items.0.item_id') == $item->id ? 'selected' : '' }}
                                >
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td>
                        <input type="text" class="supplier-name" readonly>
                        <input
                            type="hidden"
                            name="items[0][supplier_id]"
                            class="supplier-id"
                        >
                    </td>

                    <td>
                        <input
                            type="text"
                            name="items[0][code]"
                            class="item-code"
                            value="{{ old('items.0.code') }}"
                            placeholder="Kode barang"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="items[0][quantity]"
                            class="quantity"
                            min="0.01"
                            step="0.01"
                            value="{{ old('items.0.quantity') }}"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="text"
                            name="items[0][unit]"
                            class="unit"
                            value="{{ old('items.0.unit') }}"
                            placeholder="Satuan"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            name="items[0][unit_price]"
                            class="unit-price"
                            min="0"
                            step="0.01"
                            value="{{ old('items.0.unit_price') }}"
                            required
                        >
                    </td>

                    <td>
                        <input type="number" class="subtotal" readonly value="0.00">
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-delete"
                            onclick="removeRow(this)"
                        >
                            Hapus
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-add" onclick="addRow()">
            + Tambah Barang
        </button>

        <div class="total-container">
            Total PO:
            <input type="text" id="grandTotal" readonly value="Rp 0">
        </div>

        <button type="submit" class="btn btn-save">
            Simpan Purchase Order
        </button>
    </form>
</div>

<script>
let rowIndex = 1;

const itemOptions = `
    @foreach ($items as $item)
        <option
            value="{{ $item->id }}"
            data-code="{{ $item->code }}"
            data-unit="{{ $item->unit }}"
            data-supplier="{{ $item->supplier?->name ?? '-' }}"
            data-supplier-id="{{ $item->supplier_id }}"
            data-price="{{ $item->last_purchase_price ?? 0 }}"
        >{{ $item->name }}</option>
    @endforeach
`;

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');

    row.classList.add('item-row');

    row.innerHTML = `
        <td class="row-number"></td>

        <td>
            <select name="items[${rowIndex}][item_id]" class="item-select" required>
                <option value="">-- Pilih Barang --</option>
                ${itemOptions}
            </select>
        </td>

        <td>
            <input type="text" class="supplier-name" readonly>
            <input type="hidden" name="items[${rowIndex}][supplier_id]" class="supplier-id">
        </td>

        <td>
            <input
                type="text"
                name="items[${rowIndex}][code]"
                class="item-code"
                placeholder="Kode barang"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${rowIndex}][quantity]"
                class="quantity"
                min="0.01"
                step="0.01"
                required
            >
        </td>

        <td>
            <input
                type="text"
                name="items[${rowIndex}][unit]"
                class="unit"
                placeholder="Satuan"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${rowIndex}][unit_price]"
                class="unit-price"
                min="0"
                step="0.01"
                required
            >
        </td>

        <td>
            <input type="number" class="subtotal" readonly value="0.00">
        </td>

        <td>
            <button type="button" class="btn btn-delete" onclick="removeRow(this)">
                Hapus
            </button>
        </td>
    `;

    tbody.appendChild(row);
    rowIndex++;

    updateRowNumbers();
    updateGrandTotal();
}

function removeRow(button) {
    const rows = document.querySelectorAll('.item-row');

    if (rows.length <= 1) {
        alert('Minimal harus ada 1 barang.');
        return;
    }

    button.closest('tr').remove();

    updateRowNumbers();
    updateGrandTotal();
}

function updateRowNumbers() {
    document.querySelectorAll('.item-row').forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
    });
}

document.addEventListener('change', function(event) {
    if (!event.target.classList.contains('item-select')) {
        return;
    }

    const select = event.target;
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];

    if (!option.value) {
        row.querySelector('.item-code').value = '';
        row.querySelector('.supplier-name').value = '';
        row.querySelector('.supplier-id').value = '';
        row.querySelector('.unit').value = '';
        row.querySelector('.unit-price').value = '';
        calculateSubtotal(row);
        return;
    }

    // Kode dan satuan diisi otomatis saat memilih barang,
    // tetapi tetap dapat diubah secara manual setelahnya.
    row.querySelector('.item-code').value = option.dataset.code || '';
    row.querySelector('.supplier-name').value = option.dataset.supplier || '';
    row.querySelector('.supplier-id').value = option.dataset.supplierId || '';
    row.querySelector('.unit').value = option.dataset.unit || '';
    row.querySelector('.unit-price').value = option.dataset.price || 0;

    calculateSubtotal(row);
});

document.addEventListener('input', function(event) {
    if (
        event.target.classList.contains('quantity') ||
        event.target.classList.contains('unit-price')
    ) {
        calculateSubtotal(event.target.closest('tr'));
    }
});

function calculateSubtotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;

    row.querySelector('.subtotal').value = (quantity * unitPrice).toFixed(2);

    updateGrandTotal();
}

function updateGrandTotal() {
    let total = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        total += parseFloat(row.querySelector('.subtotal').value) || 0;
    });

    document.getElementById('grandTotal').value =
        'Rp ' + total.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
}

// Isi data awal untuk baris pertama, termasuk saat validasi sebelumnya gagal.
document.querySelectorAll('.item-row').forEach(row => {
    const select = row.querySelector('.item-select');

    if (select.value) {
        const option = select.options[select.selectedIndex];

        row.querySelector('.supplier-name').value = option.dataset.supplier || '';
        row.querySelector('.supplier-id').value = option.dataset.supplierId || '';

        if (!row.querySelector('.item-code').value) {
            row.querySelector('.item-code').value = option.dataset.code || '';
        }

        if (!row.querySelector('.unit').value) {
            row.querySelector('.unit').value = option.dataset.unit || '';
        }

        if (!row.querySelector('.unit-price').value) {
            row.querySelector('.unit-price').value = option.dataset.price || 0;
        }
    }

    calculateSubtotal(row);
});
</script>

</body>
</html>