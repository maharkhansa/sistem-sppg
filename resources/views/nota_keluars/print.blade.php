<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Cetak Nota {{ $notaKeluar->nota_number }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
            background: #f1f3f5;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 0 auto 20px;
        }

        .toolbar button,
        .toolbar a {
            border: none;
            border-radius: 5px;
            padding: 10px 18px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }

        .btn-print {
            background: #198754;
        }

        .btn-back {
            background: #495057;
        }

        .paper {
            width: 210mm;
            min-height: 270mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
        }

        .supplier-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            min-height: 90px;
            padding-bottom: 12px;
            border-bottom: 2px solid #222;
        }

        .supplier-logo {
            max-width: 180px;
            max-height: 90px;
            object-fit: contain;
        }

        .supplier-name {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .document-title {
            margin: 22px 0 18px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .meta-box {
            border: 1px solid #444;
            padding: 10px;
            min-height: 110px;
        }

        .meta-box h3 {
            margin: 0 0 9px;
            padding-bottom: 6px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }

        .meta-row {
            display: flex;
            gap: 6px;
            margin: 5px 0;
            line-height: 1.5;
        }

        .meta-label {
            min-width: 105px;
            font-weight: bold;
        }

        .meta-value {
            flex: 1;
            overflow-wrap: anywhere;
        }

        .section-title {
            margin: 16px 0 8px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px 6px;
            vertical-align: top;
        }

        th {
            text-align: center;
            background: #f0f0f0;
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .item-name {
            min-width: 120px;
        }

        .total-row td {
            font-weight: bold;
            font-size: 12px;
        }

        .amount-words {
            margin-top: 14px;
            padding: 10px;
            border: 1px solid #ddd;
            line-height: 1.6;
        }

        .barcode-section {
            margin-top: 16px;
            text-align: center;
        }

        #nota-barcode {
            max-width: 250px;
            height: 65px;
        }

        .barcode-number {
            margin-top: 4px;
            font-size: 10px;
            letter-spacing: 1px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
            margin-top: 35px;
            text-align: center;
        }

        .signature-title {
            min-height: 20px;
            font-weight: bold;
        }

        .signature-space {
            height: 75px;
        }

        .signature-name {
            padding-top: 5px;
            border-top: 1px solid #333;
            font-weight: bold;
        }

        .empty-row {
            padding: 20px;
            text-align: center;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                width: 100%;
                min-height: initial;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            tr,
            .meta-box,
            .signatures {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 800px) {
            body {
                padding: 8px;
            }

            .paper {
                width: 100%;
                min-height: auto;
                padding: 15px;
            }

            .meta {
                grid-template-columns: 1fr;
            }

            .supplier-logo {
                max-width: 120px;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            table {
                min-width: 650px;
            }
        }
    </style>
</head>

<body>

@php
    $supplier = $notaKeluar->supplier;
    $kitchen = $notaKeluar->kitchen;

    $supplierName = $supplier?->name ?? 'Supplier';

    $templateName = strtolower(
        trim((string) ($supplier?->nota_template ?? ''))
    );

    $supplierSlug = strtolower(
        preg_replace('/[^a-z0-9]+/i', '-', $supplierName)
    );

    if (
        str_contains($templateName, 'sumber')
        || str_contains($supplierSlug, 'sumber-rejeki')
    ) {
        $logoFile = 'sumber-rejeki.png';
    } elseif (
        str_contains($templateName, 'top')
        || str_contains($supplierSlug, 'top-fast')
        || str_contains($supplierSlug, 'topfast')
    ) {
        $logoFile = 'topfast.png';
    } elseif (
        str_contains($templateName, 'zenzi')
        || str_contains($supplierSlug, 'zenzi')
    ) {
        $logoFile = 'zenzie.png';
    } elseif (
        str_contains($templateName, 'gemilang')
        || str_contains($supplierSlug, 'gemilang')
    ) {
        $logoFile = 'gemilang-mart.png';
    } else {
        $logoFile = null;
    }

    $logoRelativePath = $logoFile
        ? 'kop-supplier/' . $logoFile
        : null;

    $logoExists = $logoRelativePath
        && file_exists(storage_path('app/public/' . $logoRelativePath));

    $notaDate = $deliveryDate
        ? \Carbon\Carbon::parse($deliveryDate)->locale('id')->translatedFormat('d F Y')
        : '-';

    $kitchenName = $kitchen?->name ?? '-';

    $kitchenLocation = collect([
        $kitchen?->kabupaten_kota,
        $kitchen?->provinsi,
    ])->filter()->implode(', ');

    $deliveryAddress = $notaKeluar->delivery_address
        ?? $kitchenLocation
        ?? '';

    $barcodeValue = $barcodeRaw
        ?: $notaKeluar->nota_number;
@endphp

<div class="toolbar">
    <button
        type="button"
        class="btn-print"
        onclick="window.print()"
    >
        Cetak Nota
    </button>

    <button
        type="button"
        class="btn-back"
        onclick="window.close()"
    >
        Tutup
    </button>
</div>

<main class="paper">

    {{-- KOP SUPPLIER --}}
    <header class="supplier-header">
        @if($logoExists)
            <img
                src="{{ asset('storage/' . $logoRelativePath) }}"
                alt="Logo {{ $supplierName }}"
                class="supplier-logo"
            >
        @else
            <div class="supplier-name">
                {{ $supplierName }}
            </div>
        @endif
    </header>

    <h1 class="document-title">
        NOTA PENJUALAN
    </h1>

    {{-- INFORMASI NOTA DAN DAPUR --}}
    <section class="meta">

        <div class="meta-box">
            <h3>INFORMASI NOTA</h3>

            <div class="meta-row">
                <span class="meta-label">Nomor Nota</span>
                <span class="meta-value">
                    : {{ $notaKeluar->nota_number ?? '-' }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Tanggal</span>
                <span class="meta-value">
                    : {{ $notaDate }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">ID Order</span>
                <span class="meta-value">
                    : {{ $notaKeluar->customer_order_number ?? '-' }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Supplier</span>
                <span class="meta-value">
                    : {{ $supplierName }}
                </span>
            </div>
        </div>

        <div class="meta-box">
            <h3>INFORMASI PELANGGAN / DAPUR</h3>

            <div class="meta-row">
                <span class="meta-label">Nama Dapur</span>
                <span class="meta-value">
                    : {{ $kitchenName }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">ID SPPG</span>
                <span class="meta-value">
                    : {{ $kitchen?->id_sppg ?? '-' }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Kabupaten/Kota</span>
                <span class="meta-value">
                    : {{ $kitchen?->kabupaten_kota ?? '-' }}
                </span>
            </div>

            <div class="meta-row">
                <span class="meta-label">Provinsi</span>
                <span class="meta-value">
                    : {{ $kitchen?->provinsi ?? '-' }}
                </span>
            </div>
        </div>

    </section>

    {{-- ALAMAT PENGIRIMAN --}}
    <section class="meta-box" style="min-height: auto; margin-bottom: 18px;">
        <h3>ALAMAT PENGIRIMAN</h3>

        <div style="line-height: 1.7; overflow-wrap: anywhere;">
            {{ $notaKeluar->delivery_address ?: ($kitchenLocation ?: '-') }}
        </div>
    </section>

    {{-- DETAIL BARANG --}}
    <div class="section-title">
        RINCIAN BARANG
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 35px;">No.</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th style="width: 65px;">Qty</th>
                    <th style="width: 65px;">Satuan</th>
                    <th style="width: 105px;">Harga</th>
                    <th style="width: 115px;">Jumlah</th>
                </tr>
            </thead>

            <tbody>
                @forelse($details as $index => $detail)
                    @php
                        $item = $detail->item;

                        $quantity = (float) ($detail->quantity ?? 0);
                        $unitPrice = (float) ($detail->unit_price ?? 0);

                        $subtotal = (float) (
                            $detail->subtotal
                            ?? ($quantity * $unitPrice)
                        );
                    @endphp

                    <tr>
                        <td class="center">
                            {{ $index + 1 }}
                        </td>

                        <td>
                            {{ $item?->code ?? $item?->item_code ?? '-' }}
                        </td>

                        <td class="item-name">
                            {{ $item?->name ?? '-' }}
                        </td>

                        <td class="right">
                            {{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}
                        </td>

                        <td class="center">
                            {{ $detail->unit ?? $item?->unit ?? '-' }}
                        </td>

                        <td class="right">
                            {{ 'Rp ' . number_format($unitPrice, 0, ',', '.') }}
                        </td>

                        <td class="right">
                            {{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-row">
                            Belum ada detail barang untuk nota ini.
                        </td>
                    </tr>
                @endforelse

                <tr class="total-row">
                    <td colspan="6" class="right">
                        TOTAL
                    </td>

                    <td class="right">
                        {{ 'Rp ' . number_format($total, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- BARCODE --}}
    <section class="barcode-section">
        <svg id="nota-barcode"></svg>

        <div class="barcode-number">
            {{ $barcodeValue }}
        </div>
    </section>

    {{-- TANDA TANGAN --}}
    <section class="signatures">

        <div>
            <div class="signature-title">
                Supplier,
            </div>

            <div class="signature-space"></div>

            <div class="signature-name">
                {{ $supplierName }}
            </div>
        </div>

        <div>
            <div class="signature-title">
                Penerima,
            </div>

            <div class="signature-space"></div>

            <div class="signature-name">
                {{ $kitchenName }}
            </div>
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const barcodeValue = @json($barcodeValue);
        const barcodeElement = document.getElementById('nota-barcode');

        if (barcodeElement && barcodeValue && typeof JsBarcode !== 'undefined') {
            try {
                JsBarcode(barcodeElement, String(barcodeValue), {
                    format: 'CODE128',
                    displayValue: false,
                    height: 55,
                    width: 1.5,
                    margin: 4
                });
            } catch (error) {
                barcodeElement.style.display = 'none';
            }
        } else if (barcodeElement) {
            barcodeElement.style.display = 'none';
        }
    });
</script>

</body>
</html>