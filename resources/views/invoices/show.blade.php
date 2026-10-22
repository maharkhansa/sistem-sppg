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

        /* HEADER INVOICE */

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            width: 100%;
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

        /* AREA TABEL */

        .supplier-section {
            width: 100%;
            margin-bottom: 10px;
        }

        .invoice-table-wrap {
            position: relative;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: visible;
        }

        .invoice-table {
            width: 92%;
            margin: 0;
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

        /* HEADER KOLOM HIJAU, TEKS PUTIH */

        .invoice-table thead th {
            background-color: #6dad8f !important;
            color: #ffffff !important;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000000;
            padding: 8px 5px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .invoice-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* HEADER KOLOM TUNGGAL */

        .invoice-column-header {
            width: 100%;
            margin-bottom: 0;
        }

        .invoice-column-header .invoice-table {
            width: 92%;
        }

        .invoice-column-header .invoice-table th {
            padding: 5px 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .item-name-cell {
            position: relative;
        }

        .item-name-with-reimburse {
            display: block;
            position: relative;
        }

        .item-name-text {
            display: inline;
        }

        .item-name-text small {
            font-size: 8px;
            font-weight: normal;
        }

        /* CATATAN REIMBURSE DI LUAR TABEL */

        .reimburse-outside {
            display: none;
            position: absolute;
            left: 92%;
            width: 8%;
            padding-left: 5px;
            font-size: 9px;
            font-weight: 700;
            color: #000;
            line-height: 1.2;
            white-space: normal;
            text-align: left;
            overflow-wrap: anywhere;
            pointer-events: none;
            z-index: 2;
        }

        .has-reimburse .reimburse-outside {
            display: block;
        }

        /* FOOTER DAN SUBTOTAL */

        .supplier-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            width: 92%;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 7px;
            font-size: 9px;
            box-sizing: border-box;
        }

        .approval-text {
            white-space: nowrap;
        }

        .subtotal-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            margin-left: auto;
        }

        .subtotal-label,
        .subtotal-val {
            font-weight: bold;
        }

        .subtotal-val {
            font-size: 10px;
        }

        /* TOTAL INVOICE */

        .grand-total-container {
            display: flex;
            justify-content: flex-end;
            width: 92%;
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

        /* TANDA TANGAN */

        .signature-section {
            width: 92%;
            margin-top: 18px;
            break-inside: avoid;
            page-break-inside: avoid;
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
            display: block !important;
            height: 75px !important;
            min-height: 75px !important;
            max-height: none !important;
        }

        .signature-line {
            border-bottom: 1px dashed #000;
            margin: 0 auto;
            width: 55%;
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

        /* CETAK A4 */

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
                width: 100% !important;
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
                width: 100% !important;
                margin-bottom: 7px !important;
            }

            .invoice-table-wrap {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                position: relative !important;
                overflow: visible !important;
            }

            .invoice-table,
            .invoice-column-header .invoice-table {
                width: 92% !important;
                margin: 0 !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
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

            .invoice-column-header .invoice-table thead th {
                background-color: #6dad8f !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .reimburse-outside {
                left: 92% !important;
                width: 8% !important;
                padding-left: 4px !important;
                font-size: 8px !important;
                font-weight: 700 !important;
                white-space: normal !important;
                overflow-wrap: anywhere !important;
            }

            .supplier-footer {
                width: 92% !important;
                padding: 5px 6px !important;
                font-size: 8px !important;
            }

            .subtotal-val {
                font-size: 9px !important;
            }

            .grand-total-container {
                width: 92% !important;
                margin: 9px 0 15px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .grand-total-box {
                font-size: 12px !important;
            }

            
            .signature-section {
                width: 92% !important;
                margin-top: 12px !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .signature-grid-top {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
                margin-bottom: 15px !important;
            }

            .signature-grid-top .signature-box {
                display: table-cell !important;
                width: 50% !important;
                vertical-align: top !important;
            }

            .signature-grid-bottom {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            .signature-grid-bottom .signature-box {
                display: table-cell !important;
                width: 33.333% !important;
                vertical-align: top !important;
            }

            .signature-space {
                display: block !important;
                height: 75px !important;
                min-height: 75px !important;
                max-height: none !important;
            }

            .signature-line {
                width: 55% !important;
            }

            .knowing-title {
                margin-bottom: 10px !important;
            }

            .signature-name-bold {
                margin-top: 7px !important;
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

            .invoice-card.fit-compact-2 .reimburse-outside {
                font-size: 7px !important;
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
            <a href="{{ route('invoices.index') }}" class="btn btn-back">
                ← Kembali ke Daftar Invoice
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
                $ketuaYayasan = 'Teguh Hadi Susilo';

                if (
                    str_contains($normalizedKitchen, 'MUNGKID') ||
                    str_contains($normalizedKitchen, 'POLANHARJO') ||
                    str_contains($normalizedKitchen, 'KEBONARUM') ||
                    str_contains($normalizedKitchen, 'PALEMBANG')
                ) {
                    $yayasan = 'Yayasan La Tahzan Indonesia';
                    $ketuaYayasan = 'Dwi Haryanti';
                }

                /*
                 * Detail stok dikelompokkan berdasarkan supplier dan bagian.
                 * Biaya non-stok dikelompokkan berdasarkan jenis biaya dan tanggal.
                 */

                $stockDetails = $invoice->details
                    ->filter(function ($detail) {
                        return ($detail->detail_type ?? 'stock') !== 'expense';
                    });

                $expenseDetails = $invoice->details
                    ->filter(function ($detail) {
                        return ($detail->detail_type ?? 'stock') === 'expense';
                    });

                $groupedStockDetails = $stockDetails
                    ->groupBy(function ($detail) {
                        $section = trim(
                            (string) ($detail->section_name ?? '')
                        );

                        return ($detail->supplier_id ?? 0) . '|' . $section;
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
                            (int) ($first?->supplier_id ?? 0),
                            (int) ($first?->section_order ?? 0)
                        );
                    });

                /*
                 * Kunci pengelompokan biaya:
                 * NAMA JENIS BIAYA + TANGGAL BIAYA.
                 *
                 * Contoh:
                 * Insentif + 2026-10-01 = satu tabel.
                 * Insentif + 2026-10-02 = tabel berbeda.
                 * BPJS Relawan + 2026-10-01 = tabel berbeda.
                 */

                $groupedExpenseDetails = $expenseDetails
                    ->groupBy(function ($detail) {
                        $expenseType = strtoupper(trim(
                            (string) ($detail->expenseType?->name ?? 'Biaya Non-Stok')
                        ));

                        $expenseDate = empty($detail->expense_date)
                            ? 'tanpa-tanggal'
                            : \Carbon\Carbon::parse($detail->expense_date)->format('Y-m-d');

                        return $expenseType . '|' . $expenseDate;
                    })
                    ->sortKeys();

                $rowNumber = 1;
            @endphp

            {{-- HEADER INVOICE --}}
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

                {{-- HEADER KOLOM: SATU KALI SAJA --}}
                <div class="invoice-column-header">
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
                    </table>
                </div>

                {{-- TABEL BARANG STOK --}}
                @foreach($groupedStockDetails as $groupKey => $details)

                    @php
                        $supplierSubtotal = $details->sum('subtotal');

                        $hasReimburse = $details->contains(function ($detail) {
                            return trim((string) ($detail->notes ?? '')) === '*Reimburse';
                        });
                    @endphp

                    <div class="supplier-section {{ $hasReimburse ? 'has-reimburse' : '' }}">

                        <div class="invoice-table-wrap">
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

                                <tbody>
                                    @foreach($details as $detail)

                                        @php
                                            $supplierDisplay = $detail->supplier?->name ?? '-';
                                            $itemDisplay = $detail->item?->name ?? '-';
                                            $itemCode = $detail->item?->code ?? '-';

                                            $isReimburse = trim(
                                                (string) ($detail->notes ?? '')
                                            ) === '*Reimburse';
                                        @endphp

                                        <tr
                                            data-detail-id="{{ $detail->id }}"
                                            @if($isReimburse)
                                                data-reimburse="1"
                                            @endif
                                        >
                                            <td class="text-center">
                                                {{ $rowNumber++ }}
                                            </td>

                                            <td>
                                                {{ strtoupper($supplierDisplay) }}
                                            </td>

                                            <td class="text-center">
                                                {{ $itemCode }}
                                            </td>

                                            <td class="item-name-cell">
                                                <div class="item-name-with-reimburse">
                                                    <span class="item-name-text">
                                                        {{ $itemDisplay }}
                                                    </span>
                                                </div>
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

                            @if($hasReimburse)
                                @foreach($details as $detail)
                                    @if(trim((string) ($detail->notes ?? '')) === '*Reimburse')
                                        <div
                                            class="reimburse-outside"
                                            data-reimburse-for="{{ $detail->id }}"
                                        >*Reimburse</div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

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

                {{-- TABEL BIAYA NON-STOK: PER JENIS BIAYA DAN TANGGAL --}}
                @foreach($groupedExpenseDetails as $expenseGroupKey => $details)

                    @php
                        $expenseSubtotal = $details->sum('subtotal');

                        $hasReimburse = $details->contains(function ($detail) {
                            return trim((string) ($detail->notes ?? '')) === '*Reimburse';
                        });
                    @endphp

                    <div class="supplier-section {{ $hasReimburse ? 'has-reimburse' : '' }}">

                        <div class="invoice-table-wrap">
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

                                <tbody>
                                    @foreach($details as $detail)

                                        @php
                                            $supplierDisplay = $detail->supplier_name ?: '-';
                                            $itemDisplay = $detail->expenseType?->name ?? 'Biaya Non-Stok';

                                            $isReimburse = trim(
                                                (string) ($detail->notes ?? '')
                                            ) === '*Reimburse';

                                            $itemCode = !empty($detail->expense_date)
                                                ? \Carbon\Carbon::parse($detail->expense_date)->format('d/m/Y')
                                                : '-';
                                        @endphp

                                        <tr
                                            data-detail-id="{{ $detail->id }}"
                                            @if($isReimburse)
                                                data-reimburse="1"
                                            @endif
                                        >
                                            <td class="text-center">
                                                {{ $rowNumber++ }}
                                            </td>

                                            <td>
                                                {{ strtoupper($supplierDisplay) }}
                                            </td>

                                            <td class="text-center">
                                                {{ $itemCode }}
                                            </td>

                                            <td class="item-name-cell">
                                                <div class="item-name-with-reimburse">
                                                    <span class="item-name-text">
                                                        {{ $itemDisplay }}

                                                        @if(!empty($detail->notes) && !$isReimburse)
                                                            <br>
                                                            <small>{{ $detail->notes }}</small>
                                                        @endif
                                                    </span>
                                                </div>
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

                            @if($hasReimburse)
                                @foreach($details as $detail)
                                    @if(trim((string) ($detail->notes ?? '')) === '*Reimburse')
                                        <div
                                            class="reimburse-outside"
                                            data-reimburse-for="{{ $detail->id }}"
                                        >*Reimburse</div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

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
                                    Rp {{ number_format((float) $expenseSubtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                @endforeach

            @else
                <div class="empty-data">
                    DETAIL BARANG DAN BIAYA INVOICE TIDAK DITEMUKAN
                </div>
            @endif

            {{-- TOTAL INVOICE --}}
            <div class="grand-total-container">
                <div class="grand-total-box">
                    <span>TOTAL</span>
                    <span>
                        Rp {{ number_format(
                            (float) $invoice->details->sum('subtotal'),
                            0,
                            ',',
                            '.'
                        ) }}
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
                            {{ $ketuaYayasan }}
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
        /*
        |--------------------------------------------------------------------------
        | POSISI CATATAN REIMBURSE SESUAI BARIS
        |--------------------------------------------------------------------------
        */

        function positionReimburseNotes() {
            document.querySelectorAll('.invoice-table-wrap').forEach(function (wrapper) {
                const table = wrapper.querySelector('.invoice-table');

                if (!table) {
                    return;
                }

                const wrapperRect = wrapper.getBoundingClientRect();

                wrapper.querySelectorAll('.reimburse-outside').forEach(function (note) {
                    const detailId = note.dataset.reimburseFor;

                    const row = table.querySelector(
                        'tbody tr[data-detail-id="' + detailId + '"]'
                    );

                    if (!row) {
                        return;
                    }

                    const rowRect = row.getBoundingClientRect();

                    note.style.top = (
                        rowRect.top -
                        wrapperRect.top +
                        (rowRect.height - note.offsetHeight) / 2
                    ) + 'px';
                });
            });
        }

        
        function fitInvoiceForPrint() {
            const invoice = document.querySelector('.invoice-card');

            if (!invoice) {
                return;
            }

            // Pastikan mode pemadatan tidak mengecilkan tanda tangan.
            invoice.classList.remove(
                'fit-compact-1',
                'fit-compact-2'
            );

            // Atur ulang posisi catatan reimburse sebelum cetak.
            positionReimburseNotes();
        }


        window.addEventListener('load', positionReimburseNotes);
        window.addEventListener('resize', positionReimburseNotes);

        window.addEventListener('beforeprint', function () {
            fitInvoiceForPrint();
        });

        window.addEventListener('afterprint', function () {
            const invoice = document.querySelector('.invoice-card');

            if (invoice) {
                invoice.classList.remove(
                    'fit-compact-1',
                    'fit-compact-2'
                );
            }

            positionReimburseNotes();
        });
    </script>
</body>

</html>