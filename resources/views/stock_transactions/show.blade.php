<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    Detail {{ $stockTransaction->transaction_number }}
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet">

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>

    body {
        background-color: #f8f9fa;
        font-family: Arial, Helvetica, sans-serif;
    }

    .page-container {
        padding: 25px;
    }

    .card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .table th {
        background-color: #f8f9fa;
        font-size: 13px;
    }

    .table td {
        font-size: 13px;
        vertical-align: middle;
    }

    .transaction-number {
        font-size: 18px;
        font-weight: 600;
        color: #0d6efd;
    }

    .badge-in {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .total-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }

</style>

</head>

<body>

<div class="page-container">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="mb-1">

            <i class="bi bi-receipt me-2"></i>

            Detail Transaksi Barang Masuk

        </h4>

        <small class="text-muted">

            Informasi lengkap transaksi penerimaan barang

        </small>

    </div>


    <a
        href="{{ route('stock-transactions.index') }}"
        class="btn btn-outline-secondary">

        <i class="bi bi-arrow-left me-1"></i>

        Kembali

    </a>

</div>


{{-- INFORMASI TRANSAKSI --}}

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <div class="row g-4">

            {{-- NOMOR TRANSAKSI --}}

            <div class="col-md-3">

                <small class="text-muted">
                    Nomor Transaksi
                </small>

                <div class="transaction-number mt-1">

                    {{
                        $stockTransaction->transaction_number
                    }}

                </div>

            </div>


            {{-- TANGGAL --}}

            <div class="col-md-3">

                <small class="text-muted">
                    Tanggal
                </small>

                <div class="fw-semibold mt-1">

                    {{
                        $stockTransaction->transaction_date
                            ? $stockTransaction->transaction_date->format('d-m-Y')
                            : '-'
                    }}

                </div>

            </div>


            {{-- SUPPLIER --}}

            <div class="col-md-3">

                <small class="text-muted">
                    Supplier
                </small>

                <div class="fw-semibold mt-1">

                    {{
                        $stockTransaction->supplier->name
                        ?? '-'
                    }}

                </div>

            </div>


            {{-- JENIS --}}

            <div class="col-md-3">

                <small class="text-muted">
                    Jenis Transaksi
                </small>

                <div class="mt-1">

                    <span
                        class="badge rounded-pill badge-in px-3 py-2">

                        <i class="bi bi-box-arrow-in-down me-1"></i>

                        Barang Masuk

                    </span>

                </div>

            </div>


            {{-- DIBUAT OLEH --}}

            <div class="col-md-3">

                <small class="text-muted">
                    Dibuat Oleh
                </small>

                <div class="mt-1">

                    {{
                        $stockTransaction->creator->name
                        ?? '-'
                    }}

                </div>

            </div>


            {{-- CATATAN --}}

            <div class="col-md-9">

                <small class="text-muted">
                    Catatan
                </small>

                <div class="mt-1">

                    {{
                        $stockTransaction->notes
                        ?: '-'
                    }}

                </div>

            </div>

        </div>

    </div>

</div>


{{-- DETAIL BARANG --}}

<div class="card shadow-sm">

    <div class="card-header py-3">

        <h6 class="mb-0">

            <i class="bi bi-box-seam me-2"></i>

            Daftar Barang

        </h6>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th
                            width="50"
                            class="text-center">

                            No

                        </th>

                        <th>
                            Kode
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th>
                            Kategori
                        </th>

                        <th class="text-end">
                            Jumlah
                        </th>

                        <th>
                            Satuan
                        </th>

                        <th class="text-end">
                            Harga
                        </th>

                        <th class="text-end">
                            Subtotal
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @php

                        $totalQty = 0;

                        $totalAmount = 0;

                    @endphp


                    @forelse(
                        $stockTransaction->details
                        as $detail
                    )

                        @php

                            $totalQty +=
                                (float) $detail->quantity;

                            $totalAmount +=
                                (float) $detail->subtotal;

                        @endphp


                        <tr>

                            {{-- NO --}}

                            <td class="text-center">

                                {{ $loop->iteration }}

                            </td>


                            {{-- KODE --}}

                            <td>

                                {{
                                    $detail->item->code
                                    ?? '-'
                                }}

                            </td>


                            {{-- NAMA --}}

                            <td class="fw-semibold">

                                {{
                                    $detail->item->name
                                    ?? '-'
                                }}

                            </td>


                            {{-- KATEGORI --}}

                            <td>

                                {{
                                    $detail->item->category->name
                                    ?? '-'
                                }}

                            </td>


                            {{-- JUMLAH --}}

                            <td class="text-end">

                                {{
                                    number_format(
                                        (float) $detail->quantity,
                                        2,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>


                            {{-- SATUAN --}}

                            <td>

                                {{
                                    $detail->unit
                                    ?? $detail->item->unit
                                    ?? '-'
                                }}

                            </td>


                            {{-- HARGA --}}

                            <td class="text-end">

                                Rp
                                {{
                                    number_format(
                                        (float) $detail->unit_price,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>


                            {{-- SUBTOTAL --}}

                            <td class="text-end fw-semibold">

                                Rp
                                {{
                                    number_format(
                                        (float) $detail->subtotal,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center text-muted py-5">

                                <i class="bi bi-inbox fs-3"></i>

                                <div class="mt-2">

                                    Tidak ada detail barang.

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>


                @if($stockTransaction->details->count() > 0)

                    <tfoot>

                        <tr>

                            <th
                                colspan="4"
                                class="text-end">

                                TOTAL

                            </th>


                            <th class="text-end">

                                {{
                                    number_format(
                                        $totalQty,
                                        2,
                                        ',',
                                        '.'
                                    )
                                }}

                            </th>


                            <th></th>


                            <th></th>


                            <th class="text-end">

                                Rp
                                {{
                                    number_format(
                                        $totalAmount,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </th>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>

    </div>

</div>


{{-- RINGKASAN --}}

<div class="row justify-content-end mt-4">

    <div class="col-md-4">

        <div class="total-box">

            <div class="d-flex justify-content-between mb-2">

                <span>
                    Total Jenis Barang
                </span>

                <strong>
                    {{ $stockTransaction->details->count() }}
                    item
                </strong>

            </div>


            <div class="d-flex justify-content-between mb-2">

                <span>
                    Total Quantity
                </span>

                <strong>

                    {{
                        number_format(
                            $totalQty,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </strong>

            </div>


            <hr>


            <div class="d-flex justify-content-between">

                <strong>
                    Total Nilai
                </strong>

                <strong class="text-primary">

                    Rp
                    {{
                        number_format(
                            $totalAmount,
                            0,
                            ',',
                            '.'
                        )
                    }}

                </strong>

            </div>

        </div>

    </div>

</div>

</div>

</body>

</html>
