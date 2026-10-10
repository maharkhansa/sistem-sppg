<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $invoice->invoice_number }} - Invoice Belanja Program MBG</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #000;
            font-size: 12px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .invoice-card {
            background: #fff;
            padding: 25px 30px;
            border: 1px solid #d1d5db;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .btn-back {
            background: #6b7280;
            color: #fff;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }

        .btn-manage-nota {
            background: #198754;
            color: #fff;
        }

        .btn-back:hover {
            background: #4b5563;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-manage-nota:hover {
            background: #146c43;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .header-left {
            flex: 1;
            min-width: 0;
        }

        .header-left h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sppg-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .date {
            font-size: 11px;
            font-weight: bold;
            margin-top: 7px;
            text-transform: uppercase;
        }

        .header-right {
            flex-shrink: 0;
        }

        .kitchen-box {
            background-color: #1e7e34 !important;
            color: #fff !important;
            padding: 10px 18px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            min-width: 125px;
            display: inline-block;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .supplier-section {
            margin-bottom: 10px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .invoice-table th {
            background: #e5e7eb !important;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .supplier-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 7px;
            font-size: 9px;
        }

        .approval-text {
            white-space: nowrap;
        }

        .subtotal-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .subtotal-label,
        .subtotal-val {
            font-weight: bold;
        }

        .subtotal-val {
            font-size: 10px;
        }

        .grand-total-container {
            display: flex;
            justify-content: flex-end;
            margin: 12px 0 22px;
        }

        .grand-total-box {
            display: flex;
            gap: 18px;
            align-items: center;
            font-weight: bold;
            font-size: 12px;
        }

        .empty-data {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 18px;
        }

        .signature-grid-top {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 15px;
        }

        .signature-grid-top .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-grid-bottom {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .signature-grid-bottom .signature-box {
            display: table-cell;
            width: 33.333%;
            text-align: center;
            vertical-align: top;
        }

        .signature-box {
            text-align: center;
        }

        .signature-space {
            height: 48px;
        }

        .signature-line {
            border-bottom: 1px dashed #000;
            margin: 0 auto;
            width: 80%;
            height: 1px;
        }

        .signature-name-bold {
            font-weight: bold;
            margin-top: 7px;
        }

        .knowing-title {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            html,
            body {
                width: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .top-actions {
                display: none !important;
            }

            .invoice-container {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-card {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            .invoice-header {
                margin-bottom: 10px !important;
            }

            .header-left h1 {
                font-size: 15px !important;
            }

            .sppg-name {
                font-size: 12px !important;
            }

            .date {
                font-size: 10px !important;
            }

            .kitchen-box {
                font-size: 13px !important;
                padding: 9px 14px !important;
            }

            .supplier-section {
                margin-bottom: 7px !important;
            }

            .invoice-table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }

            .invoice-table thead {
                display: table-header-group;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 4px !important;
                font-size: 9px !important;
                line-height: 1.15 !important;
            }

            .invoice-table th {
                padding: 5px 2px !important;
                font-size: 8px !important;
            }

            .invoice-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .supplier-footer {
                padding: 5px 6px !important;
                font-size: 8px !important;
            }

            .subtotal-val {
                font-size: 9px !important;
            }

            .grand-total-container {
                margin: 9px 0 15px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .grand-total-box {
                font-size: 12px !important;
            }

            .signature-section {
                margin-top: 12px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .signature-grid-top {
                margin-bottom: 10px !important;
            }

            .signature-space {
                height: 36px !important;
            }

            .knowing-title {
                margin-bottom: 7px !important;
            }

            .signature-name-bold {
                margin-top: 5px !important;
            }

            .invoice-card.fit-compact-1 .invoice-table th,
            .invoice-card.fit-compact-1 .invoice-table td {
                padding: 3px !important;
                font-size: 8px !important;
            }

            .invoice-card.fit-compact-1 .signature-space {
                height: 28px !important;
            }

            .invoice-card.fit-compact-1 .supplier-section {
                margin-bottom: 4px !important;
            }

            .invoice-card.fit-compact-1 .grand-total-container {
                margin: 5px 0 8px !important;
            }

            .invoice-card.fit-compact-2 .invoice-table th,
            .invoice-card.fit-compact-2 .invoice-table td {
                padding: 2px !important;
                font-size: 7px !important;
                line-height: 1.05 !important;
            }

            .invoice-card.fit-compact-2 .invoice-table th {
                font-size: 6.5px !important;
            }

            .invoice-card.fit-compact-2 .signature-space {
                height: 20px !important;
            }

            .invoice-card.fit-compact-2 .signature-section {
                margin-top: 5px !important;
            }

            .invoice-card.fit-compact-2 .signature-grid-top {
                margin-bottom: 5px !important;
            }

            .invoice-card.fit-compact-2 .grand-total-container {
                margin: 3px 0 5px !important;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">

        {{-- TOMBOL AKSI --}}
        <div class="top-actions">
            <a href="{{ route('stock-transactions.out') }}" class="btn btn-back">
                ← Kembali ke Barang Keluar
            </a>

            <div class="action-buttons">
                <a href="{{ route('invoices.nota-allocation', $invoice->id) }}"
                   class="btn btn-manage-nota">
                    📋 Kelola Pembagian Nota
                </a>

                <button type="button" onclick="window.print()" class="btn btn-print">
                    🖨 Cetak Invoice
                </button>
            </div>
        </div>

        <div class="invoice-card">

            {{-- DATA KITCHEN DAN YAYASAN --}}
            @php
                $rawKitchenName = $invoice->kitchen?->name ?? '-';

                $cleanedName = trim(str_ireplace(
                    ['SPPG', 'DAPUR', 'KITCHEN'],
                    '',
                    $rawKitchenName
                ));

                $words = preg_split('/\s+/', $cleanedName);

                $kitchenRegion = count($words) > 2
                    ? implode(' ', array_slice($words, -2))
                    : $cleanedName;

                $normalizedKitchen = strtoupper(
                    trim(preg_replace('/\s+/', ' ', $rawKitchenName))
                );

                $yayasan = 'Yayasan Aghits Star International';

                if (
                    str_contains($normalizedKitchen, 'MUNGKID') ||
                    str_contains($normalizedKitchen, 'POLANHARJO') ||
                    str_contains($normalizedKitchen, 'KEBONARUM') ||
                    str_contains($normalizedKitchen, 'PALEMBANG')
                ) {
                    $yayasan = 'Yayasan La Tahzan Indonesia';
                }
            @endphp

            {{-- HEADER --}}
            <div class="invoice-header">
                <div class="header-left">
                    <h1>INVOICE BELANJA PROGRAM MBG</h1>

                    <div class="sppg-name">
                        {{ strtoupper($rawKitchenName) }}
                    </div>

                    <div class="date">
                        TANGGAL:
                        {{ strtoupper(
                            \Carbon\Carbon::parse($invoice->invoice_date)
                                ->translatedFormat('l, d F Y')
                        ) }}
                    </div>
                </div>

                <div class="header-right">
                    <div class="kitchen-box">
                        {{ strtoupper($kitchenRegion) }}
                    </div>
                </div>
            </div>

            {{-- DETAIL INVOICE --}}
            @if($invoice->details->count() > 0)

                @php
                    /*
                    * Kelompokkan berdasarkan supplier DAN bagian.
                    * Bagian kosong tetap menjadi kelompok tanpa pembagian.
                    */
                    $groupedDetails = $invoice->details
                        ->groupBy(function ($detail) {
                            $section = trim((string) ($detail->section_name ?? ''));

                            return ($detail->supplier_id ?? 0)
                                . '|'
                                . $section;
                        })
                        ->sortBy(function ($details) {
                            $first = $details->first();

                            $supplierName = strtoupper(
                                trim($first?->supplier?->name ?? '')
                            );

                            if (
                                str_contains($supplierName, 'KOPERASI') ||
                                str_contains($supplierName, 'SUMBER REJEKI')
                            ) {
                                $supplierOrder = 1;
                            } elseif (
                                str_contains($supplierName, 'ZENZI') ||
                                str_contains($supplierName, 'ZENZIE')
                            ) {
                                $supplierOrder = 2;
                            } elseif (str_contains($supplierName, 'GEMILANG')) {
                                $supplierOrder = 3;
                            } elseif (
                                str_contains($supplierName, 'TOPFAST') ||
                                str_contains($supplierName, 'TOP FAST')
                            ) {
                                $supplierOrder = 4;
                            } else {
                                $supplierOrder = 5;
                            }

                            return sprintf(
                                '%02d-%06d-%06d',
                                $supplierOrder,
                                $first?->supplier_id ?? 0,
                                $first?->section_order ?? 0
                            );
                        });

                    $rowNumber = 1;
                @endphp

                @foreach($groupedDetails as $details)

                    @php
                        $firstDetail = $details->first();

                        $sectionName = trim(
                            $firstDetail?->section_name ?? ''
                        );

                        $supplierSubtotal = $details->sum('subtotal');
                    @endphp

                    <div class="supplier-section">

                        <table class="invoice-table">
                            <colgroup>
                                <col style="width: 4%;">
                                <col style="width: 17%;">
                                <col style="width: 12%;">
                                <col style="width: 24%;">
                                <col style="width: 6%;">
                                <col style="width: 7%;">
                                <col style="width: 14%;">
                                <col style="width: 16%;">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>SUPPLIER</th>
                                    <th>KODE BARANG</th>
                                    <th>JENIS BARANG</th>
                                    <th>QTY</th>
                                    <th>SATUAN</th>
                                    <th>HARGA</th>
                                    <th>JUMLAH</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($details as $detail)
                                    <tr>
                                        <td class="text-center">
                                            {{ $rowNumber++ }}
                                        </td>

                                        <td>
                                            {{ strtoupper($detail->supplier?->name ?? '-') }}
                                        </td>

                                        <td class="text-center">
                                            {{ $detail->item?->code ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $detail->item?->name ?? '-' }}
                                        </td>

                                        <td class="text-center">
                                            {{ rtrim(rtrim(number_format((float) $detail->quantity, 2, ',', '.'), '0'), ',') }}
                                        </td>

                                        <td class="text-center">
                                            {{ $detail->unit ?? '-' }}
                                        </td>

                                        <td class="text-right">
                                            Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                        </td>

                                        <td class="text-right">
                                            Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="supplier-footer">
                            <div class="approval-text">
                                ☐ Approved
                                &nbsp; ☐ Not Approved
                                &nbsp; ☐ Pen
                                &nbsp; | &nbsp;
                                Date Approval: ______________
                            </div>

                            <div class="subtotal-wrapper">
                                <span class="subtotal-label">SUBTOTAL</span>

                                <span class="subtotal-val">
                                    Rp {{ number_format((float) $supplierSubtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                @endforeach

            @else
                <div class="empty-data">
                    DETAIL BARANG INVOICE TIDAK DITEMUKAN
                </div>
            @endif

            {{-- TOTAL --}}
            <div class="grand-total-container">
                <div class="grand-total-box">
                    <span>TOTAL</span>

                    <span>
                        Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- TANDA TANGAN --}}
            <div class="signature-section">

                <div class="signature-grid-top">
                    <div class="signature-box">
                        <div>Sales Manager</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-box">
                        <div>Asisten Lapangan</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>
                </div>

                <div class="knowing-title">
                    Mengetahui,
                </div>

                <div class="signature-grid-bottom">

                    <div class="signature-box">
                        <div>Ketua {{ $yayasan }}</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                        <div class="signature-name-bold">
                            Teguh Hadi Susilo
                        </div>
                    </div>

                    <div class="signature-box">
                        <div>Akuntan SPPG</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-box">
                        <div>Ka. SPPG</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function () {
            const invoice = document.querySelector('.invoice-card');

            if (!invoice) {
                return;
            }

            invoice.classList.remove(
                'fit-compact-1',
                'fit-compact-2'
            );

            const maxHeight = (297 - 16) * 96 / 25.4;

            if (invoice.getBoundingClientRect().height > maxHeight) {
                invoice.classList.add('fit-compact-1');
            }

            if (invoice.getBoundingClientRect().height > maxHeight) {
                invoice.classList.add('fit-compact-2');
            }
        });

        window.addEventListener('afterprint', function () {
            const invoice = document.querySelector('.invoice-card');

            if (invoice) {
                invoice.classList.remove(
                    'fit-compact-1',
                    'fit-compact-2'
                );
            }
        });
    </script>
</body>

</html>