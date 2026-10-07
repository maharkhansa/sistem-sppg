<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tambah Purchase Order</title>

    <style>

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
            box-sizing: border-box;
        }

        table {
            width: 100%;
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
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
            text-decoration: none;
            display: inline-block;
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

        input[readonly] {
            background: #f8f9fa;
        }

    </style>

</head>

<body>

<div class="container">

    <a href="{{ route('purchase-orders.index') }}"
       class="btn btn-back">

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


    <form action="{{ route('purchase-orders.store') }}"
          method="POST">

        @csrf


        {{-- ==========================================================
             HEADER PO
        =========================================================== --}}

        <div class="form-group">

            <label for="po_date">
                Tanggal PO
            </label>

            <input
                type="date"
                name="po_date"
                id="po_date"
                value="{{ old('po_date', date('Y-m-d')) }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="kitchen_id">
                SPPG
            </label>

            <select
                name="kitchen_id"
                id="kitchen_id"
                required
            >

                <option value="">
                    -- Pilih SPPG --
                </option>

                @foreach ($kitchens as $kitchen)

                    <option
                        value="{{ $kitchen->id }}"
                        {{ old('kitchen_id') == $kitchen->id ? 'selected' : '' }}
                    >

                        {{ $kitchen->id_sppg }}
                        -
                        {{ $kitchen->name }}

                    </option>

                @endforeach

            </select>

        </div>


        <div class="form-group">

            <label for="notes">
                Catatan
            </label>

            <textarea
                name="notes"
                id="notes"
                rows="3"
                placeholder="Catatan PO (opsional)"
            >{{ old('notes') }}</textarea>

        </div>


        {{-- ==========================================================
             DETAIL PO
        =========================================================== --}}

        <h3>Detail Barang</h3>


        <table id="itemsTable">

            <thead>

                <tr>

                    <th width="50">
                        No
                    </th>

                    <th width="220">
                        Nama Barang
                    </th>

                    <th width="180">
                        Supplier
                    </th>

                    <th width="150">
                        Kode Barang
                    </th>

                    <th width="100">
                        Qty
                    </th>

                    <th width="100">
                        Satuan
                    </th>

                    <th width="150">
                        Harga
                    </th>

                    <th width="150">
                        Jumlah
                    </th>

                    <th width="80">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody id="itemsBody">


                {{-- ==================================================
                     BARIS PERTAMA
                =================================================== --}}

                <tr class="item-row">


                    {{-- NO --}}

                    <td class="row-number">
                        1
                    </td>


                    {{-- NAMA BARANG --}}

                    <td>

                        <select
                            name="items[0][item_id]"
                            class="item-select"
                            required
                        >

                            <option value="">
                                -- Pilih Barang --
                            </option>

                            @foreach ($items as $item)

                                <option
                                    value="{{ $item->id }}"

                                    data-code="{{ $item->code }}"

                                    data-name="{{ $item->name }}"

                                    data-unit="{{ $item->unit }}"

                                    data-supplier="{{ $item->supplier?->name ?? '-' }}"

                                    data-supplier-id="{{ $item->supplier_id }}"

                                    data-price="{{ $item->last_purchase_price ?? 0 }}"
                                >

                                    {{ $item->name }}

                                </option>

                            @endforeach

                        </select>

                    </td>


                    {{-- SUPPLIER --}}

                    <td>

                        <input
                            type="text"
                            class="supplier-name"
                            readonly
                        >

                        <input
                            type="hidden"
                            name="items[0][supplier_id]"
                            class="supplier-id"
                        >

                    </td>


                    {{-- KODE BARANG --}}

                    <td>

                        <input
                            type="text"
                            class="item-code"
                            readonly
                        >

                    </td>


                    {{-- QTY --}}

                    <td>

                        <input
                            type="number"
                            name="items[0][quantity]"
                            class="quantity"
                            min="0.01"
                            step="0.01"
                            required
                        >

                    </td>


                    {{-- SATUAN --}}

                    <td>

                        <input
                            type="text"
                            class="unit"
                            readonly
                        >

                    </td>


                    {{-- HARGA --}}

                    <td>

                        <input
                            type="number"
                            name="items[0][unit_price]"
                            class="unit-price"
                            min="0"
                            step="0.01"
                            required
                        >

                    </td>


                    {{-- JUMLAH --}}

                    <td>

                        <input
                            type="number"
                            class="subtotal"
                            readonly
                        >

                    </td>


                    {{-- AKSI --}}

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


        {{-- TAMBAH BARANG --}}

        <button
            type="button"
            class="btn btn-add"
            onclick="addRow()"
        >

            + Tambah Barang

        </button>


        <br>


        {{-- SIMPAN PO --}}

        <button
            type="submit"
            class="btn btn-save"
        >

            Simpan Purchase Order

        </button>


    </form>

</div>


<script>

let rowIndex = 1;


/*
|--------------------------------------------------------------------------
| Tambah Baris
|--------------------------------------------------------------------------
*/

function addRow()
{

    const tbody =
        document.getElementById('itemsBody');


    const row =
        document.createElement('tr');


    row.classList.add('item-row');


    row.innerHTML = `

        <td class="row-number"></td>


        {{-- NAMA BARANG --}}

        <td>

            <select
                name="items[${rowIndex}][item_id]"
                class="item-select"
                required
            >

                <option value="">
                    -- Pilih Barang --
                </option>

                @foreach ($items as $item)

                    <option
                        value="{{ $item->id }}"

                        data-code="{{ $item->code }}"

                        data-name="{{ $item->name }}"

                        data-unit="{{ $item->unit }}"

                        data-supplier="{{ $item->supplier?->name ?? '-' }}"

                        data-supplier-id="{{ $item->supplier_id }}"

                        data-price="{{ $item->last_purchase_price ?? 0 }}"
                    >

                        {{ $item->name }}

                    </option>

                @endforeach

            </select>

        </td>


        {{-- SUPPLIER --}}

        <td>

            <input
                type="text"
                class="supplier-name"
                readonly
            >

            <input
                type="hidden"
                name="items[${rowIndex}][supplier_id]"
                class="supplier-id"
            >

        </td>


        {{-- KODE BARANG --}}

        <td>

            <input
                type="text"
                class="item-code"
                readonly
            >

        </td>


        {{-- QTY --}}

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


        {{-- SATUAN --}}

        <td>

            <input
                type="text"
                class="unit"
                readonly
            >

        </td>


        {{-- HARGA --}}

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


        {{-- JUMLAH --}}

        <td>

            <input
                type="number"
                class="subtotal"
                readonly
            >

        </td>


        {{-- AKSI --}}

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


    rowIndex++;


    updateRowNumbers();

}


/*
|--------------------------------------------------------------------------
| Hapus Baris
|--------------------------------------------------------------------------
*/

function removeRow(button)
{

    const rows =
        document.querySelectorAll('.item-row');


    if (rows.length <= 1) {

        alert(
            'Minimal harus ada 1 barang.'
        );

        return;

    }


    button
        .closest('tr')
        .remove();


    updateRowNumbers();

}


/*
|--------------------------------------------------------------------------
| Nomor Baris
|--------------------------------------------------------------------------
*/

function updateRowNumbers()
{

    const rows =
        document.querySelectorAll('.item-row');


    rows.forEach(
        (row, index) => {

            row
                .querySelector('.row-number')
                .textContent = index + 1;

        }
    );

}


/*
|--------------------------------------------------------------------------
| Pilih Barang
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'change',
    function(event)
    {

        if (
            !event.target.classList.contains(
                'item-select'
            )
        ) {

            return;

        }


        const select =
            event.target;


        const row =
            select.closest('tr');


        const option =
            select.options[
                select.selectedIndex
            ];


        /*
        |--------------------------------------------------------------
        | Ambil data barang
        |--------------------------------------------------------------
        */

        const code =
            option.dataset.code || '';


        const supplier =
            option.dataset.supplier || '';


        const supplierId =
            option.dataset.supplierId || '';


        const unit =
            option.dataset.unit || '';


        const price =
            option.dataset.price || 0;


        /*
        |--------------------------------------------------------------
        | Isi otomatis
        |--------------------------------------------------------------
        */

        row.querySelector(
            '.item-code'
        ).value = code;


        row.querySelector(
            '.supplier-name'
        ).value = supplier;


        row.querySelector(
            '.supplier-id'
        ).value = supplierId;


        row.querySelector(
            '.unit'
        ).value = unit;


        row.querySelector(
            '.unit-price'
        ).value = price;


        /*
        |--------------------------------------------------------------
        | Hitung jumlah
        |--------------------------------------------------------------
        */

        calculateSubtotal(row);

    }
);


/*
|--------------------------------------------------------------------------
| Hitung Jumlah
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'input',
    function(event)
    {

        if (
            !event.target.classList.contains(
                'quantity'
            ) &&
            !event.target.classList.contains(
                'unit-price'
            )
        ) {

            return;

        }


        const row =
            event.target.closest('tr');


        calculateSubtotal(row);

    }
);


/*
|--------------------------------------------------------------------------
| Fungsi Hitung Subtotal
|--------------------------------------------------------------------------
*/

function calculateSubtotal(row)
{

    const quantity =
        parseFloat(
            row.querySelector(
                '.quantity'
            ).value
        ) || 0;


    const unitPrice =
        parseFloat(
            row.querySelector(
                '.unit-price'
            ).value
        ) || 0;


    const subtotal =
        quantity * unitPrice;


    row.querySelector(
        '.subtotal'
    ).value =
        subtotal.toFixed(2);

}

</script>


</body>

</html>
