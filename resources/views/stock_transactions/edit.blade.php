<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit Barang Keluar
    </title>

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
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        h3 {
            margin-top: 25px;
        }

        .info {
            background: #f1f3f5;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .info table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 6px;
        }

        .label {
            width: 180px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        select {
            background: white;
            cursor: pointer;
        }

        textarea {
            resize: vertical;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            min-width: 900px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: middle;
        }

        .detail-table th {
            background: #f8f9fa;
            text-align: center;
        }

        .detail-table th:nth-child(1) {
            width: 50px;
        }

        .detail-table th:nth-child(2) {
            width: 150px;
        }

        .detail-table th:nth-child(4) {
            width: 130px;
        }

        .detail-table th:nth-child(5) {
            width: 160px;
        }

        .detail-table th:nth-child(6) {
            width: 160px;
        }

        .detail-table th:nth-child(7) {
            width: 80px;
        }

        .number {
            text-align: right;
        }

        .total {
            text-align: right;
            font-weight: bold;
            background: #f8f9fa;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-primary:hover {
            background: #0b5ed7;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5c636a;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .btn-success:hover {
            background: #157347;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 7px 11px;
        }

        .btn-danger:hover {
            background: #bb2d3b;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .warning {
            background: #fff3cd;
            color: #664d03;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .item-code {
            font-weight: bold;
        }

        .item-unit {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #6c757d;
        }

        .subtotal {
            font-weight: bold;
        }

        .empty-row {
            text-align: center;
            color: #6c757d;
            padding: 20px !important;
        }

    </style>

</head>


<body>

<div class="container">

    <h1>
        Edit Barang Keluar
    </h1>


    {{-- ERROR VALIDASI --}}
    @if ($errors->any())

        <div class="alert alert-error">

            <strong>
                Terdapat kesalahan:
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ERROR DARI CONTROLLER --}}
    @if (session('error'))

        <div class="alert alert-error">
            {{ session('error') }}
        </div>

    @endif


    {{-- SUCCESS --}}
    @if (session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- INFORMASI TRANSAKSI --}}
    <div class="info">

        <table>

            <tr>

                <td class="label">
                    No. OUT
                </td>

                <td>
                    <strong>
                        {{ $stockTransaction->transaction_number }}
                    </strong>
                </td>

            </tr>


            <tr>

                <td class="label">
                    No. PO
                </td>

                <td>
                    {{ $stockTransaction->purchaseOrder?->po_number ?? '-' }}
                </td>

            </tr>


            <tr>

                <td class="label">
                    SPPG / Dapur
                </td>

                <td>

                    {{ $stockTransaction->kitchen?->id_sppg ?? '-' }}

                    -

                    {{ $stockTransaction->kitchen?->name ?? '-' }}

                </td>

            </tr>

        </table>

    </div>


    {{-- PERINGATAN --}}
    <div class="warning">

        <strong>Perhatian:</strong>

        Perubahan barang, jumlah, atau harga akan otomatis
        menyesuaikan data Barang Keluar dan stok gudang.

        <br><br>

        Jika barang dihapus, stok barang tersebut akan
        dikembalikan.

        Jika barang baru ditambahkan, stok barang tersebut
        akan dikurangi.

        Supplier barang akan mengikuti supplier yang
        tercatat pada Purchase Order.

    </div>


    <form
        action="{{ route(
            'stock-transactions.update',
            $stockTransaction->id
        ) }}"
        method="POST"
        id="editForm"
    >

        @csrf

        @method('PUT')


        {{-- TANGGAL --}}
        <div class="form-group">

            <label for="transaction_date">
                Tanggal Barang Keluar
            </label>

            <input
                type="date"
                id="transaction_date"
                name="transaction_date"
                value="{{ old(
                    'transaction_date',
                    optional(
                        $stockTransaction->transaction_date
                    )->format('Y-m-d')
                ) }}"
                required
            >

        </div>


        {{-- DETAIL BARANG --}}
        <h3>
            Detail Barang
        </h3>


        <button
            type="button"
            class="btn btn-success"
            id="addItemBtn"
        >
            ➕ Tambah Barang
        </button>


        <div class="table-wrapper">

            <table class="detail-table">

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Kode
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Harga
                        </th>

                        <th>
                            Subtotal
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody id="itemsBody">

                    @foreach (
                        $stockTransaction->details
                        as $index => $detail
                    )

                        <tr class="item-row">

                            {{-- NO --}}
                            <td class="row-number">
                                {{ $index + 1 }}
                            </td>


                            {{-- KODE BARANG --}}
                            <td>

                                <span
                                    class="item-code"
                                    id="item-code-{{ $index }}"
                                >
                                    {{ $detail->item?->code ?? '-' }}
                                </span>

                                <span
                                    class="item-unit"
                                    id="item-unit-{{ $index }}"
                                >
                                    Satuan:
                                    {{ $detail->item?->unit ?? '-' }}
                                </span>


                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][detail_id]"
                                    value="{{ $detail->id }}"
                                    class="detail-id-input"
                                >

                            </td>


                            {{-- NAMA BARANG --}}
                            <td>

                                <select
                                    name="items[{{ $index }}][item_id]"
                                    class="item-select"
                                    data-index="{{ $index }}"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Barang --
                                    </option>


                                    @foreach ($items as $item)

                                        <option
                                            value="{{ $item->id }}"
                                            data-code="{{ $item->code }}"
                                            data-unit="{{ $item->unit }}"

                                            {{ old(
                                                'items.' . $index . '.item_id',
                                                $detail->item_id
                                            ) == $item->id
                                                ? 'selected'
                                                : '' }}
                                        >

                                            {{ $item->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </td>


                            {{-- QUANTITY --}}
                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    name="items[{{ $index }}][quantity]"
                                    class="quantity-input"
                                    data-index="{{ $index }}"
                                    value="{{ old(
                                        'items.' . $index . '.quantity',
                                        $detail->quantity
                                    ) }}"
                                    required
                                >

                            </td>


                            {{-- HARGA --}}
                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="items[{{ $index }}][unit_price]"
                                    class="price-input"
                                    data-index="{{ $index }}"
                                    value="{{ old(
                                        'items.' . $index . '.unit_price',
                                        $detail->unit_price
                                    ) }}"
                                    required
                                >

                            </td>


                            {{-- SUBTOTAL --}}
                            <td class="number">

                                <span
                                    class="subtotal"
                                    id="subtotal-{{ $index }}"
                                >
                                    Rp 0
                                </span>

                            </td>


                            {{-- AKSI --}}
                            <td style="text-align: center;">

                                <button
                                    type="button"
                                    class="btn btn-danger delete-item"
                                >
                                    🗑️ Hapus
                                </button>

                            </td>

                        </tr>

                    @endforeach


                    {{-- TOTAL --}}
                    <tr>

                        <td
                            colspan="5"
                            class="total"
                        >
                            Total
                        </td>

                        <td
                            class="total"
                            id="grand-total"
                        >
                            Rp 0
                        </td>

                        <td class="total">
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        {{-- CATATAN --}}
        <div
            class="form-group"
            style="margin-top: 25px;"
        >

            <label for="notes">
                Catatan
            </label>

            <textarea
                id="notes"
                name="notes"
                rows="4"
            >{{ old(
                'notes',
                $stockTransaction->notes
            ) }}</textarea>

        </div>


        {{-- BUTTON --}}
        <div class="actions">

            <button
                type="submit"
                class="btn btn-primary"
                onclick="
                    return confirm(
                        'Apakah Anda yakin ingin menyimpan perubahan Barang Keluar? Stok akan disesuaikan otomatis.'
                    );
                "
            >
                💾 Simpan Perubahan
            </button>


            <a
                href="{{ route(
                    'stock-transactions.out'
                ) }}"
                class="btn btn-secondary"
            >
                ← Batal
            </a>

        </div>

    </form>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Format Rupiah
|--------------------------------------------------------------------------
*/

function formatRupiah(number) {

    return new Intl.NumberFormat(
        'id-ID'
    ).format(number);

}


/*
|--------------------------------------------------------------------------
| Update Nomor Baris
|--------------------------------------------------------------------------
*/

function updateRowNumbers() {

    const rows =
        document.querySelectorAll(
            '#itemsBody .item-row'
        );

    rows.forEach(function(row, index) {

        const number =
            row.querySelector('.row-number');

        if (number) {

            number.innerText =
                index + 1;

        }

    });

}


/*
|--------------------------------------------------------------------------
| Update Informasi Barang
|--------------------------------------------------------------------------
*/

function updateItemInformation(select) {

    const row =
        select.closest('.item-row');

    if (!row) {
        return;
    }


    const selectedOption =
        select.options[
            select.selectedIndex
        ];


    const code =
        selectedOption?.dataset.code || '-';


    const unit =
        selectedOption?.dataset.unit || '-';


    const codeElement =
        row.querySelector('.item-code');


    const unitElement =
        row.querySelector('.item-unit');


    if (codeElement) {

        codeElement.innerText =
            code;

    }


    if (unitElement) {

        unitElement.innerText =
            'Satuan: ' + unit;

    }

}


/*
|--------------------------------------------------------------------------
| Hitung Subtotal dan Total
|--------------------------------------------------------------------------
*/

function calculateTotal() {

    let total = 0;


    document
        .querySelectorAll(
            '#itemsBody .item-row'
        )
        .forEach(function(row) {

            const quantityInput =
                row.querySelector(
                    '.quantity-input'
                );


            const priceInput =
                row.querySelector(
                    '.price-input'
                );


            const quantity =
                parseFloat(
                    quantityInput?.value
                ) || 0;


            const price =
                parseFloat(
                    priceInput?.value
                ) || 0;


            const subtotal =
                quantity * price;


            const subtotalElement =
                row.querySelector(
                    '.subtotal'
                );


            if (subtotalElement) {

                subtotalElement.innerText =
                    'Rp ' +
                    formatRupiah(
                        subtotal
                    );

            }


            total += subtotal;

        });


    const grandTotal =
        document.getElementById(
            'grand-total'
        );


    if (grandTotal) {

        grandTotal.innerText =
            'Rp ' +
            formatRupiah(total);

    }

}


/*
|--------------------------------------------------------------------------
| Cek Barang Duplikat
|--------------------------------------------------------------------------
*/

function hasDuplicateItems() {

    const selectedItems = [];

    let duplicate = false;


    document
        .querySelectorAll(
            '.item-select'
        )
        .forEach(function(select) {

            const value =
                select.value;


            if (!value) {
                return;
            }


            if (
                selectedItems.includes(
                    value
                )
            ) {

                duplicate = true;

            }


            selectedItems.push(value);

        });


    return duplicate;

}


/*
|--------------------------------------------------------------------------
| Buat Baris Barang Baru
|--------------------------------------------------------------------------
*/

function createItemRow(index) {

    const tr =
        document.createElement('tr');

    tr.className =
        'item-row';


    let options =
        '<option value="">-- Pilih Barang --</option>';


    @foreach ($items as $item)

        options +=
            `<option
                value="{{ $item->id }}"
                data-code="{{ $item->code }}"
                data-unit="{{ $item->unit }}"
            >
                {{ addslashes($item->name) }}
            </option>`;

    @endforeach


    tr.innerHTML = `

        <td class="row-number">
            ${index + 1}
        </td>


        <td>

            <span class="item-code">
                -
            </span>

            <span class="item-unit">
                Satuan: -
            </span>

            <input
                type="hidden"
                name="items[${index}][detail_id]"
                value=""
                class="detail-id-input"
            >

        </td>


        <td>

            <select
                name="items[${index}][item_id]"
                class="item-select"
                data-index="${index}"
                required
            >

                ${options}

            </select>

        </td>


        <td>

            <input
                type="number"
                step="0.01"
                min="0.01"
                name="items[${index}][quantity]"
                class="quantity-input"
                data-index="${index}"
                value="1"
                required
            >

        </td>


        <td>

            <input
                type="number"
                step="0.01"
                min="0"
                name="items[${index}][unit_price]"
                class="price-input"
                data-index="${index}"
                value="0"
                required
            >

        </td>


        <td class="number">

            <span class="subtotal">
                Rp 0
            </span>

        </td>


        <td style="text-align: center;">

            <button
                type="button"
                class="btn btn-danger delete-item"
            >
                🗑️ Hapus
            </button>

        </td>

    `;


    return tr;

}


/*
|--------------------------------------------------------------------------
| Tombol Tambah Barang
|--------------------------------------------------------------------------
*/

document
    .getElementById('addItemBtn')
    .addEventListener(
        'click',
        function() {

            const tbody =
                document.getElementById(
                    'itemsBody'
                );


            const totalRow =
                tbody.querySelector(
                    'tr:last-child'
                );


            const currentRows =
                tbody.querySelectorAll(
                    '.item-row'
                ).length;


            const newRow =
                createItemRow(
                    currentRows
                );


            tbody.insertBefore(
                newRow,
                totalRow
            );


            updateRowNumbers();

            calculateTotal();

        }
    );


/*
|--------------------------------------------------------------------------
| Tombol Hapus Barang
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function(event) {

        const button =
            event.target.closest(
                '.delete-item'
            );


        if (!button) {
            return;
        }


        const rows =
            document.querySelectorAll(
                '#itemsBody .item-row'
            );


        if (rows.length <= 1) {

            alert(
                'Minimal harus ada 1 barang.'
            );

            return;

        }


        const row =
            button.closest(
                '.item-row'
            );


        const select =
            row.querySelector(
                '.item-select'
            );


        const itemName =
            select?.options[
                select.selectedIndex
            ]?.text || 'barang ini';


        const confirmed =
            confirm(
                'Apakah Anda yakin ingin menghapus ' +
                itemName +
                '? Stok barang akan dikembalikan saat perubahan disimpan.'
            );


        if (!confirmed) {
            return;
        }


        row.remove();


        updateRowNumbers();

        calculateTotal();

    }
);


/*
|--------------------------------------------------------------------------
| Event Input Quantity dan Harga
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'input',
    function(event) {

        if (
            event.target.classList.contains(
                'quantity-input'
            ) ||
            event.target.classList.contains(
                'price-input'
            )
        ) {

            calculateTotal();

        }

    }
);


/*
|--------------------------------------------------------------------------
| Event Dropdown Barang
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'change',
    function(event) {

        if (
            event.target.classList.contains(
                'item-select'
            )
        ) {

            updateItemInformation(
                event.target
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| Validasi Sebelum Submit
|--------------------------------------------------------------------------
*/

document
    .getElementById('editForm')
    .addEventListener(
        'submit',
        function(event) {

            const rows =
                document.querySelectorAll(
                    '#itemsBody .item-row'
                );


            if (rows.length === 0) {

                event.preventDefault();

                alert(
                    'Minimal harus ada 1 barang.'
                );

                return;

            }


            if (hasDuplicateItems()) {

                event.preventDefault();

                alert(
                    'Tidak boleh memilih barang yang sama lebih dari satu kali.'
                );

                return;

            }

        }
    );


/*
|--------------------------------------------------------------------------
| Jalankan Saat Halaman Dibuka
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.item-select')
    .forEach(function(select) {

        updateItemInformation(
            select
        );

    });


calculateTotal();

updateRowNumbers();

</script>


</body>

</html>