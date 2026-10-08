<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Barang Masuk</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    >

    <style>
        .price-display {
            font-size: 0.78rem;
            color: #198754;
            font-weight: 600;
            margin-top: 3px;
        }

        .table th {
            white-space: nowrap;
        }

        .supplier-card {
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-light">

<div class="container my-5">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4">

        <div class="d-flex align-items-center">

            <a
                href="{{ route('stock-transactions.index') }}"
                class="btn btn-outline-secondary btn-sm me-3"
                title="Kembali"
            >
                <i class="bi bi-arrow-left"></i>
            </a>

            <div>

                <h2 class="h4 fw-bold text-dark mb-1">
                    <i class="bi bi-box-arrow-in-down me-2 text-success"></i>
                    Tambah Stok Barang Masuk
                </h2>

                <div class="text-muted small">
                    Semua barang akan otomatis menggunakan jumlah
                    <strong>10.000</strong> sesuai supplier masing-masing.
                </div>

            </div>

        </div>


        <!-- BUTTON IMPORT HARGA -->
        <button
            type="button"
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#importHargaModal"
        >
            <i class="bi bi-file-earmark-excel me-1"></i>
            Import Harga Excel
        </button>

    </div>


    <!-- ERROR -->
    @if ($errors->any())

        <div
            class="alert alert-danger alert-dismissible fade show mb-4"
            role="alert"
        >

            <div class="d-flex align-items-center mb-1">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <strong>Terjadi kesalahan:</strong>

            </div>

            <ul class="mb-0 ps-4">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    <!-- SUCCESS -->
    @if (session('success'))

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    <!-- ERROR SESSION -->
    @if (session('error'))

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-x-circle-fill me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    <!-- INFO -->
    <div class="alert alert-info border-0 shadow-sm mb-4">

        <div class="d-flex">

            <i class="bi bi-info-circle-fill fs-5 me-2"></i>

            <div>

                <strong>Informasi Barang Masuk</strong>

                <div class="small mt-1">

                    Barang dikelompokkan berdasarkan supplier.
                    Jumlah awal setiap barang adalah
                    <strong>10.000</strong>.

                    <br>

                    Harga satuan akan otomatis mengambil harga
                    dari master barang setelah import Excel.

                    <br>

                    Format kode barang:
                    <strong>GM</strong> untuk Gemilang,
                    <strong>SRN</strong> untuk Sumber Rejeki,
                    <strong>TF</strong> untuk Topfast,
                    dan <strong>ZP</strong> untuk Zenzie.

                </div>

            </div>

        </div>

    </div>


    <!-- DAFTAR SUPPLIER -->
    @forelse ($suppliers as $supplier)

        @php

            $supplierItems = $items->filter(function ($item) use ($supplier) {
                return (int) $item->supplier_id === (int) $supplier->id;
            });

        @endphp


        @if ($supplierItems->count() > 0)

            <div class="card shadow-sm border-0 mb-4 supplier-card">

                <!-- SUPPLIER HEADER -->
                <div class="card-header bg-white border-bottom p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div class="d-flex align-items-center">

                            <div
                                class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 42px; height: 42px;"
                            >

                                <i class="bi bi-truck text-success fs-5"></i>

                            </div>

                            <div>

                                <div class="fw-bold text-dark">
                                    {{ $supplier->name }}
                                </div>

                                <div class="small text-muted">

                                    Kode Supplier:
                                    {{ $supplier->code ?? '-' }}

                                </div>

                            </div>

                        </div>


                        <span class="badge bg-success">

                            {{ $supplierItems->count() }} Barang

                        </span>

                    </div>

                </div>


                <!-- FORM -->
                <form
                    action="{{ route('stock-transactions.store') }}"
                    method="POST"
                >

                    @csrf


                    <div class="card-body">

                        <!-- TRANSACTION INFORMATION -->
                        <div class="row g-3 mb-4">

                            <!-- TANGGAL -->
                            <div class="col-md-6">

                                <label
                                    for="transaction_date_{{ $supplier->id }}"
                                    class="form-label fw-semibold"
                                >
                                    Tanggal Barang Masuk
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>

                                    <input
                                        type="date"
                                        id="transaction_date_{{ $supplier->id }}"
                                        name="transaction_date"
                                        class="form-control"
                                        value="{{ old('transaction_date', date('Y-m-d')) }}"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- SUPPLIER -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Supplier
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-truck"></i>
                                    </span>

                                    <input
                                        type="text"
                                        class="form-control bg-light"
                                        value="{{ $supplier->name }}"
                                        readonly
                                    >

                                </div>

                                <input
                                    type="hidden"
                                    name="supplier_id"
                                    value="{{ $supplier->id }}"
                                >

                            </div>

                        </div>


                        <!-- DETAIL BARANG -->
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h3 class="h5 text-dark m-0 fw-bold">

                                <i class="bi bi-list-check me-2 text-primary"></i>

                                Detail Barang

                            </h3>

                            <span class="badge bg-primary-subtle text-primary">

                                Jumlah otomatis: 10.000

                            </span>

                        </div>


                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th
                                            class="text-center"
                                            style="width: 60px;"
                                        >
                                            No
                                        </th>

                                        <th style="width: 160px;">
                                            Kode
                                        </th>

                                        <th>
                                            Barang
                                        </th>

                                        <th
                                            class="text-center"
                                            style="width: 140px;"
                                        >
                                            Jumlah
                                        </th>

                                        <th
                                            class="text-center"
                                            style="width: 110px;"
                                        >
                                            Satuan
                                        </th>

                                        <th
                                            class="text-end"
                                            style="width: 190px;"
                                        >
                                            Harga Satuan
                                        </th>

                                        <th
                                            class="text-end"
                                            style="width: 190px;"
                                        >
                                            Subtotal
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($supplierItems as $index => $item)

                                        @php
                                            $itemPrice = (float) ($item->price ?? 0);
                                        @endphp

                                        <tr>

                                            <!-- NOMOR -->
                                            <td class="text-center fw-semibold text-muted">

                                                {{ $loop->iteration }}

                                            </td>


                                            <!-- KODE -->
                                            <td>

                                                <span class="badge text-bg-light border">

                                                    {{ $item->code }}

                                                </span>

                                            </td>


                                            <!-- BARANG -->
                                            <td>

                                                <div class="fw-semibold">

                                                    {{ $item->name }}

                                                </div>

                                            </td>


                                            <!-- ITEM ID -->
                                            <input
                                                type="hidden"
                                                name="items[{{ $index }}][item_id]"
                                                value="{{ $item->id }}"
                                            >


                                            <!-- JUMLAH -->
                                            <td>

                                                <input
                                                    type="number"
                                                    name="items[{{ $index }}][quantity]"
                                                    class="form-control text-end quantity-input"
                                                    value="10000"
                                                    min="0.01"
                                                    step="0.01"
                                                    required
                                                >

                                            </td>


                                            <!-- SATUAN -->
                                            <td>

                                                <input
                                                    type="text"
                                                    name="items[{{ $index }}][unit]"
                                                    class="form-control bg-light text-center"
                                                    value="{{ $item->unit }}"
                                                    readonly
                                                >

                                            </td>


                                            <!-- HARGA -->
                                            <td>

                                                <input
                                                    type="number"
                                                    name="items[{{ $index }}][unit_price]"
                                                    class="form-control text-end unit-price"
                                                    value="{{ $itemPrice }}"
                                                    min="0"
                                                    step="0.01"
                                                    required
                                                >

                                                <div class="price-display">

                                                    Rp
                                                    {{ number_format($itemPrice, 0, ',', '.') }}

                                                </div>

                                            </td>


                                            <!-- SUBTOTAL -->
                                            <td>

                                                <input
                                                    type="text"
                                                    class="form-control bg-light text-end fw-semibold subtotal"
                                                    value="0"
                                                    readonly
                                                >

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>


                                <tfoot class="table-light">

                                    <tr>

                                        <td
                                            colspan="6"
                                            class="text-end fw-bold"
                                        >

                                            Total

                                        </td>

                                        <td>

                                            <input
                                                type="text"
                                                class="form-control bg-white text-end fw-bold supplier-total"
                                                value="0"
                                                readonly
                                            >

                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>


                        <!-- CATATAN -->
                        <div class="mt-4">

                            <label
                                for="notes_{{ $supplier->id }}"
                                class="form-label fw-semibold"
                            >
                                Catatan
                            </label>

                            <textarea
                                name="notes"
                                id="notes_{{ $supplier->id }}"
                                class="form-control"
                                rows="2"
                                placeholder="Contoh: Stok awal barang {{ date('d-m-Y') }}"
                            >{{ old('notes') }}</textarea>

                        </div>

                    </div>


                    <!-- FOOTER -->
                    <div class="card-footer bg-white border-top p-3">

                        <div class="d-flex justify-content-between align-items-center">

                            <div class="small text-muted">

                                <i class="bi bi-box-seam me-1"></i>

                                {{ $supplierItems->count() }}
                                barang × 10.000

                            </div>


                            <button
                                type="submit"
                                class="btn btn-success px-4"
                            >

                                <i class="bi bi-save me-1"></i>

                                Simpan Stok {{ $supplier->name }}

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        @endif

    @empty

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i class="bi bi-truck fs-1 text-muted"></i>

                <h5 class="mt-3">
                    Belum ada supplier
                </h5>

                <p class="text-muted mb-0">
                    Silakan tambahkan supplier terlebih dahulu.
                </p>

            </div>

        </div>

    @endforelse


    <!-- SUPPLIER TANPA BARANG -->
    @php

        $supplierIdsWithItems = $items
            ->pluck('supplier_id')
            ->filter()
            ->unique();

        $suppliersWithoutItems = $suppliers->filter(
            function ($supplier) use ($supplierIdsWithItems) {
                return !$supplierIdsWithItems->contains($supplier->id);
            }
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

                <div class="small text-muted mb-2">

                    Supplier berikut belum memiliki barang
                    pada master barang:

                </div>

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


    <!-- KEMBALI -->
    <div class="text-end mb-5">

        <a
            href="{{ route('stock-transactions.index') }}"
            class="btn btn-light border"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Kembali ke Barang Masuk

        </a>

    </div>

</div>


<!-- ========================================================= -->
<!-- MODAL IMPORT HARGA EXCEL -->
<!-- ========================================================= -->

<div
    class="modal fade"
    id="importHargaModal"
    tabindex="-1"
    aria-labelledby="importHargaModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="importHargaModalLabel"
                >

                    <i class="bi bi-file-earmark-excel text-success me-2"></i>

                    Import Harga dari Excel

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <form
                action="{{ route('items.import') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf

                <div class="modal-body">

                    <div class="alert alert-info small">

                        <i class="bi bi-info-circle me-1"></i>

                        Import ini akan memperbarui:

                        <ul class="mb-0 mt-2">

                            <li>Harga barang</li>

                            <li>Supplier barang</li>

                            <li>Kode barang berdasarkan supplier</li>

                        </ul>

                    </div>


                    <label
                        for="file"
                        class="form-label fw-semibold"
                    >

                        File Excel

                    </label>


                    <input
                        type="file"
                        name="file"
                        id="file"
                        class="form-control"
                        accept=".xlsx,.xls,.csv"
                        required
                    >


                    <div class="form-text mt-2">

                        Format kolom Excel:

                        <strong>NAMA BARANG</strong>,
                        <strong>HARGA</strong>,
                        <strong>SUPPLIER</strong>.

                    </div>


                    <div class="alert alert-warning small mt-3 mb-0">

                        <i class="bi bi-exclamation-triangle me-1"></i>

                        Barang dengan harga kosong akan disimpan
                        dengan harga <strong>Rp0</strong>.

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal"
                    >

                        Batal

                    </button>


                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        <i class="bi bi-upload me-1"></i>

                        Import Sekarang

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ========================================================= -->
<!-- SCRIPT -->
<!-- ========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Format Rupiah
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value) {

        const number = parseFloat(value) || 0;

        return 'Rp ' + number.toLocaleString('id-ID', {
            maximumFractionDigits: 0
        });

    }


    /*
    |--------------------------------------------------------------------------
    | Hitung total supplier
    |--------------------------------------------------------------------------
    */

    function calculateSupplierTotal(form) {

        let total = 0;

        const rows =
            form.querySelectorAll('tbody tr');

        rows.forEach(function (row) {

            const quantityInput =
                row.querySelector('.quantity-input');

            const priceInput =
                row.querySelector('.unit-price');

            const subtotalInput =
                row.querySelector('.subtotal');

            const priceDisplay =
                row.querySelector('.price-display');


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


            /*
            |--------------------------------------------------------------------------
            | Subtotal
            |--------------------------------------------------------------------------
            */

            if (subtotalInput) {

                subtotalInput.value =
                    subtotal.toLocaleString(
                        'id-ID'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Tampilan harga Rupiah
            |--------------------------------------------------------------------------
            */

            if (priceDisplay) {

                priceDisplay.textContent =
                    formatRupiah(price);

            }


            total += subtotal;

        });


        /*
        |--------------------------------------------------------------------------
        | Total supplier
        |--------------------------------------------------------------------------
        */

        const totalInput =
            form.querySelector('.supplier-total');


        if (totalInput) {

            totalInput.value =
                total.toLocaleString(
                    'id-ID'
                );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Hitung saat halaman pertama kali dibuka
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('form')
        .forEach(function (form) {

            if (
                form.querySelector(
                    '.quantity-input'
                )
            ) {

                calculateSupplierTotal(form);

            }

        });


    /*
    |--------------------------------------------------------------------------
    | Hitung ketika quantity atau harga berubah
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains(
                    'quantity-input'
                ) ||
                event.target.classList.contains(
                    'unit-price'
                )
            ) {

                const form =
                    event.target.closest(
                        'form'
                    );


                if (form) {

                    calculateSupplierTotal(form);

                }

            }

        }
    );

});

</script>


<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>