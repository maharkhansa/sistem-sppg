@php
    use Illuminate\Support\Carbon;

    $tanggalDelivery = $purchaseOrder->created_at
        ? Carbon::parse($purchaseOrder->created_at)
            ->locale('id')
            ->translatedFormat('l, d/m/Y')
        : '-';

    $namaDapur = $purchaseOrder->kitchen->name ?? '-';

    // Format tanggal: TANGGAL-BULAN-TAHUN
    $tanggalKode = now()->format('dmY');

    // Nomor acak untuk setiap sesi tampilan.
    // Catatan: kode ini berubah jika halaman dimuat ulang.
    $nomorAcak = str_pad(
        (string) random_int(1000, 9999),
        4,
        '0',
        STR_PAD_LEFT
    );

    $nomorDokumen = 'DN-' . $tanggalKode . '-' . $nomorAcak;

    // Barcode menggunakan angka tanggal + nomor acak.
    $barcodeRaw = $tanggalKode . $nomorAcak;

    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();

    $barcodeSvg = base64_encode(
        $barcodeGenerator->getBarcode(
            $barcodeRaw,
            $barcodeGenerator::TYPE_CODE_128,
            1.5,
            32
        )
    );

    $detailsBySupplier = $purchaseOrder->details
        ->sortBy(function ($detail) {
            return mb_strtolower(
                $detail->supplier?->name ?? 'Tanpa Supplier'
            );
        })
        ->groupBy(function ($detail) {
            return $detail->supplier_id
                ?? $detail->supplier?->id
                ?? 'tanpa-supplier';
        })
        ->values();

    $nomorBaris = 1;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Delivery Note - {{ $nomorDokumen }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            background: #fff;
        }

        .page {
            width: 100%;
            margin: 0 auto;
        }

        /* HEADER */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 12px;
        }

        .header-left {
            flex: 1;
        }

        .header-left h1 {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin: 0 0 4px;
        }

        .header-left p {
            font-size: 10px;
            margin: 0;
        }

        /* NOMOR DAN BARCODE */

        .barcode-area {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .barcode-number {
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
        }

        .barcode-image {
            text-align: center;
        }

        .barcode-image img {
            display: block;
            width: 110px;
            height: 28px;
            object-fit: fill;
        }

        .barcode-serial {
            font-size: 8px;
            margin-top: 2px;
            letter-spacing: 1px;
            text-align: center;
        }

        /* KOTAK SHIPMENT */

        .shipment-box {
            display: grid;
            grid-template-columns: 30px 1.2fr 1fr 1fr;
            border: 1px solid #555;
            margin: 8px 0 10px;
            min-height: 0;
            height: 65px;
            width: 100%;
        }

        .shipment-label {
            background: #c6d9f1;
            color: #1f1f1f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 9px;
            letter-spacing: 1px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            border-right: 1px solid #555;
            padding: 5px;
        }

        .shipment-column {
            padding: 4px 7px;
            border-right: 1px solid #999;
            line-height: 1.3;
            min-width: 0;
            overflow: hidden;
        }

        .shipment-column:last-child {
            border-right: none;
        }

        .shipment-heading {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .shipment-value {
            font-size: 10px;
            overflow-wrap: anywhere;
            min-height: 15px;
        }

        /* TABEL BARANG */

        .delivery-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            table-layout: fixed;
        }

        .delivery-table th,
        .delivery-table td {
            border: 1px solid #555;
            padding: 6px 5px;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .delivery-table th {
            background: #e9edf5;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }

        .delivery-table td {
            font-size: 9px;
        }

        .delivery-table .col-no {
            width: 6%;
        }

        .delivery-table .col-name {
            width: 25%;
        }

        .delivery-table .col-ket {
            width: 12%;
        }

        .delivery-table .col-qty {
            width: 9%;
        }

        .delivery-table .col-unit {
            width: 8%;
        }

        .delivery-table .col-delivery {
            width: 40%;
        }

        .center {
            text-align: center;
        }

        .item-name {
            font-weight: normal;
        }

        /* JARAK ANTAR SUPPLIER */

        .supplier-gap td {
            height: 12px;
            padding: 0;
            border: none;
            background: #fff;
        }

        /* STATUS PENGIRIMAN */

        .delivery-options {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .delivery-option {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            font-size: 8px;
        }

        .delivery-checkbox {
            width: 11px;
            height: 11px;
            border: 1px solid #333;
            display: inline-block;
            flex-shrink: 0;
        }

        /* TANDA TANGAN */

        .received-section {
            margin-top: 25px;
            width: 220px;
            margin-left: 0;
            margin-right: auto;
            text-align: Left;
            font-size: 10px;
            page-break-inside: avoid;
        }

        .received-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .signature-line {
            margin-top: 30px;
            border-bottom: 1px dotted #333;
            width: 180px; 
            margin-right: auto;
        }

        /* TOMBOL CETAK */

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 15px;
        }

        .toolbar button {
            border: none;
            border-radius: 4px;
            padding: 8px 14px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-print {
            background: #0d6efd;
            color: #fff;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page {
                width: 100%;
                max-width: none;
                min-height: 0;
                padding: 0;
                box-shadow: none;
            }

            .delivery-table tr {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                padding: 20px;
                background: #f4f4f4;
            }

            .page {
                max-width: 190mm;
                min-height: 250mm;
                padding: 10mm;
                background: #fff;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
            }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button
            type="button"
            class="btn-back"
            onclick="window.close()"
        >
            Tutup
        </button>

        <button
            type="button"
            class="btn-print"
            onclick="window.print()"
        >
            Cetak Delivery Note
        </button>
    </div>

    <main class="page">

        {{-- HEADER DELIVERY NOTE --}}
        <div class="header">
            <div class="header-left">
                <h1>DELIVERY NOTE</h1>
                <p>Mitra Logistik Bahan Pangan Program MBG</p>
            </div>

            <div class="barcode-area">
                <div class="barcode-number">
                    NO :
                </div>

                <div class="barcode-image">
                    <img
                        src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                        alt="Barcode Delivery Note"
                    >

                    <div class="barcode-serial">
                        {{ $barcodeRaw }}
                    </div>
                </div>
            </div>
        </div>

        {{-- KOTAK SHIPMENT: 4 KOLOM --}}
        <section class="shipment-box">

            <div class="shipment-label">
                SHIPMENT
            </div>

            <div class="shipment-column">
                <div class="shipment-heading">To :</div>

                <div class="shipment-value">
                    {{ $namaDapur }}
                </div>
                
            </div>

            <div class="shipment-column">
                <div class="shipment-heading">Date :</div>

                <div class="shipment-value">
                    {{ $tanggalDelivery }}
                </div>
            </div>

            <div class="shipment-column">
                <div class="shipment-heading">Delivery by :</div>
                <div class="shipment-value">&nbsp;</div>
                <div class="shipment-value">&nbsp;</div>
            </div>

        </section>

        {{-- TABEL BARANG --}}
        <table class="delivery-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-name">NAMA BARANG</th>
                    <th class="col-ket">KET</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-unit">SAT</th>
                    <th class="col-delivery">
                        CATATAN PENGIRIMAN
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse ($detailsBySupplier as $supplierIndex => $supplierDetails)

                    @if ($supplierIndex > 0)
                        <tr class="supplier-gap">
                            <td colspan="6"></td>
                        </tr>
                    @endif

                    @foreach ($supplierDetails as $detail)
                        <tr>
                            <td class="center">
                                {{ $nomorBaris++ }}
                            </td>

                            <td class="item-name">
                                {{ $detail->item->name ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->notes ?? '' }}
                            </td>

                            <td class="center">
                                {{ number_format((float) $detail->quantity, 0, ',', '.') }}
                            </td>

                            <td class="center">
                                {{ $detail->unit ?? $detail->item->unit ?? '-' }}
                            </td>

                            <td>
                                <div class="delivery-options">
                                    <span class="delivery-option">
                                        <span class="delivery-checkbox"></span>
                                        Delivered
                                    </span>

                                    <span class="delivery-option">
                                        <span class="delivery-checkbox"></span>
                                        Not Delivered
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                @empty
                    <tr>
                        <td colspan="6" class="center">
                            Tidak ada detail barang pada Purchase Order ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TANDA TANGAN DI LUAR TABEL --}}
        <section class="received-section">
            <div class="received-title">
                Received by
            </div>

            <div class="signature-line"></div>
        </section>

    </main>
</body>
</html>