<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Barang Keluar (OUT)
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
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }


        h1 {
            margin: 0;
            font-size: 24px;
        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }


        .btn-back {
            background: #6c757d;
            color: white;
        }


        .btn-back:hover {
            background: #5c636a;
        }


        /* =====================================================
           OUT CARD
        ===================================================== */

        .out-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 25px;
            overflow: hidden;
            background: #fff;
        }


        .out-header {
            background: #f1f3f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }


        .out-header table {
            width: 100%;
            border-collapse: collapse;
        }


        .out-header td {
            padding: 6px;
            vertical-align: top;
        }


        .label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }


        /* =====================================================
           DETAIL TABLE
        ===================================================== */

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }


        .detail-table th,
        .detail-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
        }


        .detail-table th {
            background: #f8f9fa;
            color: #495057;
        }


        .text-center {
            text-align: center;
        }


        .text-right {
            text-align: right;
        }


        .total {
            font-weight: bold;
            background: #f8f9fa;
        }


        /* =====================================================
           BADGE
        ===================================================== */

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }


        .badge-out {
            background: #f8d7da;
            color: #842029;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {
            text-align: center;
            padding: 50px;
            color: #777;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .action-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }


        /* =====================================================
           EDIT BUTTON
        ===================================================== */

        .btn-edit {
            background: #ffc107;
            color: #212529;
        }


        .btn-edit:hover {
            background: #e0a800;
        }


        /* =====================================================
           INVOICE BUTTON
        ===================================================== */

        .btn-invoice {
            background: #fd7e14;
            color: white;
        }


        .btn-invoice:hover {
            background: #e96b02;
        }


        .btn-invoice-view {
            background: #0d6efd;
            color: white;
        }


        .btn-invoice-view:hover {
            background: #0b5ed7;
        }


        /* =====================================================
           NOTA BUTTON
        ===================================================== */

        .btn-nota {
            background: #6f42c1;
            color: white;
        }


        .btn-nota:hover {
            background: #59359a;
        }


        .btn-nota-view {
            background: #198754;
            color: white;
        }


        .btn-nota-view:hover {
            background: #157347;
        }


        /* =====================================================
           ALERT
        ===================================================== */

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }


        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }


        .alert-error {
            background: #f8d7da;
            color: #842029;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 768px) {

            body {
                margin: 10px;
            }


            .container {
                padding: 15px;
            }


            .header {
                flex-direction: column;
                align-items: flex-start;
            }


            .out-card {
                overflow-x: auto;
            }


            .out-header {
                min-width: 700px;
            }


            .detail-table {
                min-width: 800px;
            }

        }

    </style>

</head>


<body>


<div class="container">


    {{-- =====================================================
         HEADER HALAMAN
    ====================================================== --}}

    <div class="header">

        <h1>
            Barang Keluar (OUT)
        </h1>


        <div>

            <a
                href="{{ route('purchase-orders.index') }}"
                class="btn btn-back"
            >
                ← Kembali ke PO
            </a>

        </div>

    </div>



    {{-- =====================================================
         PESAN SUCCESS
    ====================================================== --}}

    @if (session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif



    {{-- =====================================================
         PESAN ERROR
    ====================================================== --}}

    @if (session('error'))

        <div class="alert alert-error">

            {{ session('error') }}

        </div>

    @endif



    {{-- =====================================================
         DAFTAR TRANSAKSI OUT
    ====================================================== --}}

    @forelse ($transactions as $transaction)


        <div class="out-card">


            {{-- =================================================
                 HEADER TRANSAKSI
            ================================================== --}}

            <div class="out-header">


                <table>


                    {{-- NO OUT + TANGGAL --}}

                    <tr>

                        <td class="label">
                            No. OUT
                        </td>


                        <td>

                            <strong>
                                {{ $transaction->transaction_number }}
                            </strong>

                        </td>


                        <td class="label">
                            Tanggal
                        </td>


                        <td>

                            {{ optional(
                                $transaction->transaction_date
                            )->format('d-m-Y') ?? '-' }}

                        </td>

                    </tr>



                    {{-- NO PO + STATUS --}}

                    <tr>

                        <td class="label">
                            No. PO
                        </td>


                        <td>

                            {{ $transaction->purchaseOrder?->po_number ?? '-' }}

                        </td>


                        <td class="label">
                            Status
                        </td>


                        <td>

                            <span class="badge badge-out">
                                BARANG KELUAR
                            </span>

                        </td>

                    </tr>



                    {{-- SPPG / DAPUR --}}

                    <tr>

                        <td class="label">
                            SPPG / Dapur
                        </td>


                        <td colspan="3">

                            {{ $transaction->kitchen?->id_sppg ?? '-' }}

                            -

                            {{ $transaction->kitchen?->name ?? '-' }}

                        </td>

                    </tr>



                    {{-- =================================================
                         AKSI
                    ================================================== --}}

                    <tr>

                        <td class="label">
                            Aksi
                        </td>


                        <td colspan="3">

                            <div class="action-wrapper">


                                {{-- =====================================
                                     EDIT BARANG KELUAR
                                ====================================== --}}

                                <a
                                    href="{{ route(
                                        'stock-transactions.edit',
                                        $transaction->id
                                    ) }}"
                                    class="btn btn-edit"
                                >
                                    ✏️ Edit Barang Keluar
                                </a>



                                {{-- =====================================
                                     INVOICE
                                ====================================== --}}

                                @if ($transaction->invoice)


                                    <a
                                        href="{{ route(
                                            'invoices.show',
                                            $transaction->invoice->id
                                        ) }}"
                                        class="btn btn-invoice-view"
                                    >
                                        👁 Lihat Invoice
                                    </a>


                                @else


                                    <form
                                        action="{{ route(
                                            'stock-transactions.create-invoice',
                                            $transaction->id
                                        ) }}"
                                        method="POST"
                                        style="display: inline;"
                                    >

                                        @csrf


                                        <button
                                            type="submit"
                                            class="btn btn-invoice"
                                            onclick="
                                                return confirm(
                                                    'Buat Invoice otomatis dari transaksi OUT ini?'
                                                );
                                            "
                                        >
                                            🧾 Buat Invoice
                                        </button>

                                    </form>


                                @endif



                                {{-- =====================================
                                     NOTA KELUAR
                                ====================================== --}}

                                @if (
                                    $transaction
                                        ->notaKeluars
                                        ->count() > 0
                                )


                                    @foreach (
                                        $transaction->notaKeluars
                                        as $nota
                                    )


                                        <a
                                            href="{{ route(
                                                'nota-keluars.show',
                                                $nota->id
                                            ) }}"
                                            class="btn btn-nota-view"
                                        >

                                            👁 Lihat Nota

                                            @if ($nota->supplier?->name)

                                                -
                                                {{ $nota->supplier->name }}

                                            @endif

                                        </a>


                                    @endforeach


                                @else


                                    <form
                                        action="{{ route(
                                            'stock-transactions.create-nota',
                                            $transaction->id
                                        ) }}"
                                        method="POST"
                                        style="display: inline;"
                                    >

                                        @csrf


                                        <button
                                            type="submit"
                                            class="btn btn-nota"
                                            onclick="
                                                return confirm(
                                                    'Buat Nota Keluar berdasarkan supplier dari transaksi OUT ini?'
                                                );
                                            "
                                        >
                                            📝 Buat Nota
                                        </button>

                                    </form>


                                @endif


                            </div>

                        </td>

                    </tr>



                    {{-- =================================================
                         CATATAN
                    ================================================== --}}

                    @if ($transaction->notes)


                        <tr>

                            <td class="label">
                                Catatan
                            </td>


                            <td colspan="3">

                                {{ $transaction->notes }}

                            </td>

                        </tr>


                    @endif


                </table>

            </div>



            {{-- =================================================
                 DETAIL BARANG
            ================================================== --}}

            <table class="detail-table">


                <thead>

                    <tr>

                        <th width="50">
                            No
                        </th>

                        <th>
                            Kode Barang
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th width="100">
                            Qty
                        </th>

                        <th width="100">
                            Satuan
                        </th>

                        <th width="140">
                            Harga
                        </th>

                        <th width="160">
                            Jumlah
                        </th>

                    </tr>

                </thead>



                <tbody>


                    @forelse (
                        $transaction->details
                        as $index => $detail
                    )


                        <tr>


                            {{-- NOMOR --}}

                            <td class="text-center">

                                {{ $index + 1 }}

                            </td>



                            {{-- KODE --}}

                            <td>

                                {{ $detail->item?->code ?? '-' }}

                            </td>



                            {{-- NAMA --}}

                            <td>

                                {{ $detail->item?->name ?? '-' }}

                            </td>



                            {{-- QUANTITY --}}

                            <td class="text-right">

                                {{
                                    rtrim(
                                        rtrim(
                                            number_format(
                                                $detail->quantity,
                                                2,
                                                ',',
                                                '.'
                                            ),
                                            '0'
                                        ),
                                        ','
                                    )
                                }}

                            </td>



                            {{-- SATUAN --}}

                            <td class="text-center">

                                {{ $detail->unit }}

                            </td>



                            {{-- HARGA --}}

                            <td class="text-right">

                                Rp
                                {{ number_format(
                                    $detail->unit_price,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>



                            {{-- SUBTOTAL --}}

                            <td class="text-right">

                                Rp
                                {{ number_format(
                                    $detail->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                        </tr>


                    @empty


                        <tr>

                            <td
                                colspan="7"
                                class="empty"
                            >
                                Tidak ada detail barang.
                            </td>

                        </tr>


                    @endforelse



                    {{-- =================================================
                         TOTAL
                    ================================================== --}}

                    <tr class="total">


                        <td
                            colspan="6"
                            class="text-right"
                        >
                            Total
                        </td>


                        <td class="text-right">

                            Rp
                            {{ number_format(
                                $transaction
                                    ->details
                                    ->sum('subtotal'),
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>


                    </tr>


                </tbody>

            </table>


        </div>


    @empty


        {{-- =================================================
             JIKA BELUM ADA TRANSAKSI
        ================================================== --}}

        <div class="empty">

            Belum ada transaksi Barang Keluar (OUT).

        </div>


    @endforelse


</div>


</body>

</html>
