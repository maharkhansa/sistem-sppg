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
            max-width: 1500px;
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
            min-width: 1250px;
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

        .btn-section {
            background: #6f42c1;
            color: white;
            margin-top: 15px;
            margin-left: 5px;
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

        .section-info {
            margin-top: 12px;
            padding: 12px;
            background: #f3efff;
            border: 1px solid #d8ccff;
            border-radius: 5px;
            color: #493078;
            font-size: 13px;
        }

        .section-select {
            min-width: 145px;
        }

        .section-order {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                margin: 10px;
            }

            .container {
                padding: 15px;
            }
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

        <div class="section-info">
            <strong>Pembagian bagian barang (opsional)</strong><br>
            Klik tombol <strong>+ Tambah Bagian</strong> untuk membuat nama bagian.
            Setelah itu, pilih bagian pada masing-masing barang. Barang yang tidak
            perlu dibagi cukup pilih <strong>Tanpa bagian</strong>.
        </div>

        <button
            type="button"
            class="btn btn-section"
            onclick="addSection()"
        >
            + Tambah Bagian
        </button>

        <button
            type="button"
            class="btn btn-add"
            onclick="addRow()"
        >
            + Tambah Barang
        </button>

        <table id="itemsTable">
            <thead>
                <tr>
                    <th width="45">No</th>
                    <th width="220">Nama Barang</th>
                    <th width="170">Supplier</th>
                    <th width="130">Kode Barang</th>
                    <th width="90">Qty</th>
                    <th width="100">Satuan</th>
                    <th width="130">Harga</th>
                    <th width="140">Jumlah</th>
                    <th width="180">Bagian (Opsional)</th>
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
                            value="{{ old('items.0.supplier_id') }}"
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
                        <input
                            type="number"
                            class="subtotal"
                            readonly
                            value="0.00"
                        >
                    </td>

                    <td>
                        <select
                            name="items[0][section_name]"
                            class="section-select"
                        >
                            <option value="">Tanpa bagian</option>
                        </select>

                        <input
                            type="hidden"
                            name="items[0][section_order]"
                            class="section-order"
                            value=""
                        >
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

    /*
    |--------------------------------------------------------------------------
    | Data barang untuk pilihan dropdown
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Daftar bagian yang dibuat pengguna
    |--------------------------------------------------------------------------
    */

    let sectionNames = [];

    /*
     * Pulihkan pilihan bagian yang sebelumnya dikirim ketika validasi gagal.
     */
    const oldSections = @json(
        collect(old('items', []))
            ->pluck('section_name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->unique()
            ->values()
    );

    sectionNames = oldSections.map(name => String(name).trim());

    /*
    |--------------------------------------------------------------------------
    | Tambah bagian
    |--------------------------------------------------------------------------
    */

    function addSection() {
        const input = prompt(
            'Masukkan nama bagian, contoh: Bagian 1, Bagian 2, atau Pengiriman Pagi:'
        );

        if (input === null) {
            return;
        }

        const sectionName = input.trim();

        if (sectionName === '') {
            alert('Nama bagian tidak boleh kosong.');
            return;
        }

        if (sectionName.length > 100) {
            alert('Nama bagian maksimal 100 karakter.');
            return;
        }

        const alreadyExists = sectionNames.some(
            name => name.toLowerCase() === sectionName.toLowerCase()
        );

        if (alreadyExists) {
            alert('Nama bagian tersebut sudah tersedia.');
            return;
        }

        sectionNames.push(sectionName);

        refreshSectionOptions();

        alert(
            'Bagian "' + sectionName + '" berhasil ditambahkan. ' +
            'Silakan pilih bagian tersebut pada barang yang sesuai.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Isi pilihan bagian untuk semua baris barang
    |--------------------------------------------------------------------------
    */

    function refreshSectionOptions() {
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.section-select');
            const orderInput = row.querySelector('.section-order');

            if (!select) {
                return;
            }

            const previousValue = select.value;

            select.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Tanpa bagian';
            select.appendChild(defaultOption);

            sectionNames.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                select.appendChild(option);
            });

            if (sectionNames.includes(previousValue)) {
                select.value = previousValue;
            }

            updateSectionOrder(select, orderInput);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan urutan bagian
    |--------------------------------------------------------------------------
    */

    function updateSectionOrder(select, orderInput) {
        if (!select || !orderInput) {
            return;
        }

        const selectedName = select.value;

        if (!selectedName) {
            orderInput.value = '';
            return;
        }

        const index = sectionNames.indexOf(selectedName);

        orderInput.value = index >= 0 ? index + 1 : '';
    }

    document.addEventListener('change', function(event) {
        if (!event.target.classList.contains('section-select')) {
            return;
        }

        const row = event.target.closest('.item-row');

        updateSectionOrder(
            event.target,
            row.querySelector('.section-order')
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Tambah baris barang
    |--------------------------------------------------------------------------
    */

    function addRow() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');

        row.classList.add('item-row');

        row.innerHTML = `
            <td class="row-number"></td>

            <td>
                <select
                    name="items[${rowIndex}][item_id]"
                    class="item-select"
                    required
                >
                    <option value="">-- Pilih Barang --</option>
                    ${itemOptions}
                </select>
            </td>

            <td>
                <input type="text" class="supplier-name" readonly>

                <input
                    type="hidden"
                    name="items[${rowIndex}][supplier_id]"
                    class="supplier-id"
                >
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
                <input
                    type="number"
                    class="subtotal"
                    readonly
                    value="0.00"
                >
            </td>

            <td>
                <select
                    name="items[${rowIndex}][section_name]"
                    class="section-select"
                >
                    <option value="">Tanpa bagian</option>
                </select>

                <input
                    type="hidden"
                    name="items[${rowIndex}][section_order]"
                    class="section-order"
                    value=""
                >
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
        `;

        tbody.appendChild(row);

        refreshSectionOptions();

        rowIndex++;

        updateRowNumbers();
        updateGrandTotal();
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus baris barang
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Nomor urut baris
    |--------------------------------------------------------------------------
    */

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Saat barang dipilih
    |--------------------------------------------------------------------------
    */

    document.addEventListener('change', function(event) {
        if (!event.target.classList.contains('item-select')) {
            return;
        }

        const select = event.target;
        const row = select.closest('.item-row');
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

        row.querySelector('.item-code').value =
            option.dataset.code || '';

        row.querySelector('.supplier-name').value =
            option.dataset.supplier || '';

        row.querySelector('.supplier-id').value =
            option.dataset.supplierId || '';

        row.querySelector('.unit').value =
            option.dataset.unit || '';

        row.querySelector('.unit-price').value =
            option.dataset.price || 0;

        calculateSubtotal(row);
    });

    /*
    |--------------------------------------------------------------------------
    | Hitung subtotal saat qty atau harga berubah
    |--------------------------------------------------------------------------
    */

    document.addEventListener('input', function(event) {
        if (
            event.target.classList.contains('quantity') ||
            event.target.classList.contains('unit-price')
        ) {
            calculateSubtotal(event.target.closest('.item-row'));
        }
    });

    function calculateSubtotal(row) {
        if (!row) {
            return;
        }

        const quantity =
            parseFloat(row.querySelector('.quantity').value) || 0;

        const unitPrice =
            parseFloat(row.querySelector('.unit-price').value) || 0;

        row.querySelector('.subtotal').value =
            (quantity * unitPrice).toFixed(2);

        updateGrandTotal();
    }

    /*
    |--------------------------------------------------------------------------
    | Hitung total PO
    |--------------------------------------------------------------------------
    */

    function updateGrandTotal() {
        let total = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            total +=
                parseFloat(row.querySelector('.subtotal').value) || 0;
        });

        document.getElementById('grandTotal').value =
            'Rp ' + total.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Inisialisasi form saat halaman dibuka
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function() {
        refreshSectionOptions();

        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-select');

            if (select.value) {
                const option = select.options[select.selectedIndex];

                row.querySelector('.supplier-name').value =
                    option.dataset.supplier || '';

                if (!row.querySelector('.supplier-id').value) {
                    row.querySelector('.supplier-id').value =
                        option.dataset.supplierId || '';
                }

                if (!row.querySelector('.item-code').value) {
                    row.querySelector('.item-code').value =
                        option.dataset.code || '';
                }

                if (!row.querySelector('.unit').value) {
                    row.querySelector('.unit').value =
                        option.dataset.unit || '';
                }

                if (!row.querySelector('.unit-price').value) {
                    row.querySelector('.unit-price').value =
                        option.dataset.price || 0;
                }
            }

            calculateSubtotal(row);
        });
    });
</script>

</body>
</html>