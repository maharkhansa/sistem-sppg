<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Purchase Order</title>

    <style>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-add {
            background: #198754;
            color: white;
        }

        .btn-print {
            background: #0d6efd;
            color: white;
            margin-left: 8px;
        }

        .btn-process {
            background: #fd7e14;
            color: white;
        }

        .btn-process:hover {
            opacity: 0.9;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #badbcc;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c2c7;
        }

        .po-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
            background: #fff;
        }

        .po-header {
            background: #f8f9fa;
            padding: 16px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .po-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .po-header td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .po-header td.label {
            width: 140px;
            font-weight: bold;
            color: #555;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            font-size: 14px;
        }

        .detail-table th {
            background: #f1f3f5;
            color: #495057;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Status */
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #664d03;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .status-processed {
            background: #cfe2ff;
            color: #084298;
        }

        /* Print */
        @media print {
            body {
                margin: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .po-card {
                page-break-inside: avoid;
                border: 1px solid #000;
            }

            .detail-table th,
            .detail-table td {
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>

<div class="container">

    {{-- HEADER HALAMAN --}}
    <div class="header-action">

        <h1>Purchase Order</h1>

        <div class="no-print">

            <a
                href="{{ route('purchase-orders.create') }}"
                class="btn btn-add"
            >
                + Tambah Purchase Order
            </a>

            <button
                onclick="window.print()"
                class="btn btn-print"
            >
                🖨️ Cetak
            </button>

        </div>

    </div>


    {{-- PESAN BERHASIL --}}
    @if (session('success'))

        <div class="success no-print">
            {{ session('success') }}
        </div>

    @endif


    {{-- PESAN ERROR --}}
    @if (session('error'))

        <div class="error no-print">
            {{ session('error') }}
        </div>

    @endif


    {{-- DAFTAR PO --}}
    @forelse ($purchaseOrders as $purchaseOrder)

        <div class="po-card">

            {{-- HEADER PO --}}
            <div class="po-header">

                <table>

                    {{-- NOMOR DAN TANGGAL PO --}}
                    <tr>

                        <td class="label">
                            No. PO
                        </td>

                        <td>
                            <strong>
                                {{ $purchaseOrder->po_number }}
                            </strong>
                        </td>

                        <td class="label">
                            Tanggal PO
                        </td>

                        <td>
                            {{ optional($purchaseOrder->po_date)->format('d-m-Y') ?? '-' }}
                        </td>

                    </tr>


                    {{-- SPPG --}}
                    <tr>

                        <td class="label">
                            SPPG / Dapur
                        </td>

                        <td colspan="3">

                            {{ $purchaseOrder->kitchen?->id_sppg ?? '-' }}

                            -

                            {{ $purchaseOrder->kitchen?->name ?? 'N/A' }}

                        </td>

                    </tr>


                    {{-- STATUS --}}
                    <tr>

                        <td class="label">
                            Status
                        </td>

                        <td>

                            @php

                                $statusClass = match(
                                    strtolower($purchaseOrder->status)
                                ) {

                                    'approved'
                                        => 'status-approved',

                                    'processed'
                                        => 'status-processed',

                                    'rejected',
                                    'canceled'
                                        => 'status-rejected',

                                    default
                                        => 'status-pending'

                                };

                            @endphp

                            <span class="status {{ $statusClass }}">

                                {{ $purchaseOrder->status }}

                            </span>

                        </td>


                        {{-- TANGGAL APPROVE --}}
                        <td class="label">
                            Tanggal Approve
                        </td>

                        <td>

                            {{
                                $purchaseOrder->approved_at
                                    ? $purchaseOrder->approved_at->format('d-m-Y H:i')
                                    : '-'
                            }}

                        </td>

                    </tr>


                    {{-- CATATAN --}}
                    @if ($purchaseOrder->notes)

                        <tr>

                            <td class="label">
                                Catatan
                            </td>

                            <td colspan="3">
                                {{ $purchaseOrder->notes }}
                            </td>

                        </tr>

                    @endif


                    {{-- AKSI --}}
                        <tr class="no-print">

                            <td class="label">
                                Aksi
                            </td>

                            <td colspan="3">

                                @if ($purchaseOrder->status !== 'PROCESSED')

                                    <form
                                        action="{{ route(
                                            'purchase-orders.process',
                                            $purchaseOrder->id
                                        ) }}"
                                        method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm(
                                            'Apakah PO ini akan diproses menjadi OUT? Stok akan berkurang dan Invoice akan dibuat otomatis.'
                                        );"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-process"
                                        >
                                            Proses PO → OUT
                                        </button>

                                    </form>

                                @else

                                    <span class="btn btn-success">
                                        ✓ Sudah Diproses menjadi OUT
                                    </span>

                                    @if ($purchaseOrder->invoice)

                                        <a
                                            href="{{ route(
                                                'invoices.show',
                                                $purchaseOrder->invoice->id
                                            ) }}"
                                            class="btn btn-print"
                                        >
                                            🧾 Lihat Invoice
                                        </a>

                                    @endif

                                @endif

                            </td>

                        </tr>

                </table>

            </div>


            {{-- DETAIL PO --}}
            <table class="detail-table">

                <thead>

                    <tr>

                        <th width="40">
                            No
                        </th>

                        <th>
                            Supplier
                        </th>

                        <th>
                            Kode Barang
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th width="80">
                            Qty
                        </th>

                        <th width="80">
                            Satuan
                        </th>

                        <th width="120">
                            Harga
                        </th>

                        <th width="140">
                            Jumlah
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse (
                        $purchaseOrder->details
                        as $index => $detail
                    )

                        <tr>

                            <td class="text-center">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                {{ $detail->supplier?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->item?->code ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->item?->name ?? '-' }}
                            </td>

                            <td class="text-right">
                                {{ number_format(
                                    $detail->quantity,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="text-center">
                                {{ $detail->unit }}
                            </td>

                            <td class="text-right">

                                Rp
                                {{ number_format(
                                    $detail->unit_price,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>

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
                                colspan="8"
                                class="text-center"
                            >
                                Tidak ada item detail untuk PO ini.
                            </td>

                        </tr>

                    @endforelse


                    {{-- TOTAL --}}
                    <tr>

                        <td
                            colspan="7"
                            class="text-right"
                        >
                            <strong>
                                Total
                            </strong>
                        </td>

                        <td class="text-right">

                            <strong>

                                Rp
                                {{ number_format(
                                    $purchaseOrder->details->sum('subtotal'),
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    @empty

        <p
            class="text-center"
            style="padding: 40px; color: #777;"
        >
            Belum ada Purchase Order.
        </p>

    @endforelse

</div>

</body>
</html>