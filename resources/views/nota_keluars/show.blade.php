
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nota Keluar - {{ $notaKeluar->nota_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e5e5e5;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .no-print-actions {
            max-width: 900px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 9px 14px;
            border-radius: 5px;
            border: none;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }

        .btn-print {
            background: #198754;
            color: #fff;
        }

        .nota-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 35px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        }

        .font-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .supplier-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            gap: 20px;
        }

        .supplier-kop {
            width: 10%;
            flex-shrink: 0;
        }

        .supplier-kop img {
            display: block;
            width: 120%;
            max-width: 120px;
            margin-bottom: 5px;
            height: auto;
            object-fit: contain;
        }

        .supplier-info {
            flex: 1;
            padding-left: 12px;
            padding-top: 15px;
        }

        .supplier-name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .supplier-address,
        .supplier-contact {
            font-size: 10px;
            line-height: 1.4;
        }

        .header-right {
            width: 32%;
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .document-title {
            font-size: 19px;
            font-weight: bold;
            color: #1a0dab;
            margin-bottom: 6px;
            white-space: nowrap;
        }

        .document-title.black {
            color: #000;
        }

        .payment-no {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .topfast-header {
            width: 100%;
            text-align: center;
            margin-bottom: 12px;
        }

        .topfast-name {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .topfast-address {
            font-size: 10px;
            line-height: 1.4;
        }

        .barcode-container {
            width: 180px;
            text-align: center;
        }

        .barcode-img {
            display: block;
            width: 180px;
            height: 35px;
            margin: 0 auto;
        }

        .barcode-number {
            font-size: 10px;
            font-family: "Courier New", monospace;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 3px;
            white-space: nowrap;
        }

        .zenzie-payment {
            font-size: 16px;
            color: #000;
        }

        table.meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        table.meta-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        table.items-table th,
        table.items-table td {
            border: 1px solid #000;
            padding: 6px 7px;
        }

        table.items-table th {
            background: #d9d9d9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.items-table.zenzie-table th {
            background: transparent;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            border-left: none;
            border-right: none;
        }

        table.items-table.zenzie-table td {
            border: none;
            border-bottom: 1px solid #ddd;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 15px;
            font-size: 11px;
        }

        .footer-left {
            width: 57%;
        }

        .footer-right {
            width: 40%;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 15px;
            font-size: 11px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-space {
            height: 55px;
        }

        .lunas-stamp {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lunas-stamp img {
            width: 200px;
            height: auto;
            max-height: 55px;
            object-fit: contain;
            opacity: 0.68;
            filter: blur(0.35px) contrast(1.15);
            mix-blend-mode: multiply;
            transform: rotate(-1deg);
        }

        .terbilang {
            margin-top: 12px;
            font-size: 11px;
            font-style: italic;
            font-weight: bold;
        }

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table.summary-table td {
            border: none;
            padding: 4px 0;
        }

        .summary-total {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print-actions {
                display: none;
            }

            .nota-wrapper {
                width: 100%;
                max-width: none;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>

<body>

    <div class="no-print-actions">
        <a href="{{ route('stock-transactions.out') }}" class="btn btn-back">
            ← Kembali ke Barang Keluar
        </a>

        <button onclick="window.print()" class="btn btn-print">
            🖨 Cetak Nota
        </button>
    </div>

    @php
        /*
        |--------------------------------------------------------------------------
        | SUPPLIER DAN TEMPLATE NOTA
        |--------------------------------------------------------------------------
        */

        $supplier = $notaKeluar->supplier
            ?? $notaKeluar->details->first()?->supplier;

        $templateType = $supplier?->nota_template ?? 'gemilang';

        if (!in_array($templateType, [
            'gemilang',
            'zenzi',
            'sumber_rejeki',
            'top_fast',
        ])) {
            $templateType = 'gemilang';
        }

        /*
        |--------------------------------------------------------------------------
        | KOP SUPPLIER
        |--------------------------------------------------------------------------
        */

        $kopSupplier = match ($templateType) {
            'gemilang' => 'kop-supplier/gemilang-mart.jpeg',
            'zenzi' => 'kop-supplier/zenzie.jpeg',
            'sumber_rejeki' => 'kop-supplier/sumber-rejeki.jpeg',
            'top_fast' => 'kop-supplier/topfast.jpeg',
            default => 'kop-supplier/gemilang-mart.jpeg',
        };

        /*
        |--------------------------------------------------------------------------
        | CAP LUNAS
        |--------------------------------------------------------------------------
        */

        $capLunas = match ($templateType) {
            'gemilang' => 'cap-supplier/lunas-gemilang.jpeg',
            'zenzi' => 'cap-supplier/lunas-zenzi.jpeg',
            'sumber_rejeki' => 'cap-supplier/lunas-sumber-rezeki.jpeg',
            'top_fast' => 'cap-supplier/lunas-topfast.jpeg',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        */

        $barcodeRaw = $barcodeRaw
            ?? $notaKeluar->barcode_number
            ?? $notaKeluar->nota_number
            ?? '';

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();

        $barcodeSvg = $barcodeRaw !== ''
            ? base64_encode(
                $generator->getBarcode(
                    $barcodeRaw,
                    $generator::TYPE_CODE_128,
                    2,
                    35
                )
            )
            : '';

        /*
        |--------------------------------------------------------------------------
        | INFORMASI DAPUR
        |--------------------------------------------------------------------------
        */

        $kitchen = $notaKeluar->kitchen;

        $kitchenName = $kitchen?->name ?? '-';

        // Ambil alamat lengkap SPPG saja.
        $kitchenAddress = $kitchen?->alamat ?: '-';

        $kitchenPhone = $kitchen?->phone ?? '-';

        $customerOrderNumber = $notaKeluar->customer_order_number ?? '-';

        /*
        |--------------------------------------------------------------------------
        | TANGGAL NOTA MENGIKUTI TANGGAL INVOICE
        |--------------------------------------------------------------------------
        |
        | Prioritas:
        | 1. Invoice yang dikirim oleh controller.
        | 2. Invoice melalui relasi transaksi, jika tersedia.
        | 3. Tanggal Nota sebagai fallback.
        |
        | Pastikan controller mengirim variabel $invoice agar tanggal
        | Invoice menjadi sumber utama yang pasti.
        */

        $invoiceForDate = $invoice ?? null;

        if (!$invoiceForDate && $notaKeluar->relationLoaded('stockTransaction')) {
            $stockTransaction = $notaKeluar->stockTransaction;

            if ($stockTransaction) {
                if ($stockTransaction->relationLoaded('invoice')) {
                    $invoiceForDate = $stockTransaction->invoice;
                }
            }
        }

        $deliveryDate = $invoiceForDate?->invoice_date
            ?? $notaKeluar->nota_date
            ?? $notaKeluar->created_at;

        $formattedDeliveryDate = $deliveryDate
            ? \Carbon\Carbon::parse($deliveryDate)->translatedFormat('j F Y')
            : '-';

    @endphp

    <div class="nota-wrapper">

        {{-- HEADER TOP FAST --}}
        @if ($templateType === 'top_fast')

            <div class="topfast-header">
                <div class="topfast-name">PT. TOP FAST NUSANTARA</div>

                <div class="topfast-address">
                    Jl. Mayor Unus, Km. 05, Honggosari,
                    Jogonegoro, Mertoyudan, Magelang
                </div>

                <div class="topfast-address">
                    Telp: 082220338007
                    &nbsp;&nbsp;&nbsp;
                    Website: www.topfastmart.store
                </div>
            </div>

            <div class="supplier-header">
                <div class="supplier-kop">
                    <img src="{{ asset('storage/' . $kopSupplier) }}" alt="Kop Supplier">
                </div>

                <div class="header-right">
                    <div class="document-title black">NOTA KONTAN</div>

                    <div class="barcode-container">
                        <div class="payment-no">NO. PAYMENT</div>

                        @if ($barcodeSvg !== '')
                            <img
                                src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                class="barcode-img"
                                alt="Barcode"
                            >
                        @endif

                        <div class="barcode-number">{{ $barcodeRaw }}</div>
                    </div>
                </div>
            </div>

        @else

            {{-- HEADER SUPPLIER LAIN --}}
            <div class="supplier-header">
                <div class="supplier-kop">
                    <img src="{{ asset('storage/' . $kopSupplier) }}" alt="Kop Supplier">
                </div>

                @if ($templateType === 'zenzi')
                    <div class="supplier-info">
                        <div class="supplier-name">CV. Zenzie Production</div>
                        <div class="supplier-address">
                            Ruko HSL, Jogonegoro, Mertoyudan, Magelang
                        </div>
                    </div>
                @elseif ($templateType === 'gemilang')
                    <div class="supplier-info">
                        <div class="supplier-name">PT Gemilang Mart Nusantara</div>
                        <div class="supplier-address">
                            Honggosari, Jogonegoro, Mertoyudan, Magelang
                        </div>
                    </div>
                @endif

                <div class="header-right">
                    @if ($templateType === 'gemilang')
                        <div class="document-title">NOTA PENJUALAN</div>
                    @elseif (in_array($templateType, ['zenzi', 'sumber_rejeki']))
                        <div class="document-title black zenzie-payment">
                            PAYMENT RECEIPT
                        </div>
                    @endif

                    <div class="barcode-container">
                        <div class="payment-no">NO. PAYMENT</div>

                        @if ($barcodeSvg !== '')
                            <img
                                src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                class="barcode-img"
                                alt="Barcode"
                            >
                        @endif

                        <div class="barcode-number">{{ $barcodeRaw }}</div>
                    </div>
                </div>
            </div>

        @endif

        {{-- INFORMASI NOTA --}}
        @if ($templateType === 'top_fast')

            <table class="meta-table">
                <tr>
                    <td width="12%">Kepada</td>
                    <td width="1%">:</td>
                    <td width="47%">{{ $kitchenName }}</td>
                    <td width="12%">Alamat</td>
                    <td width="1%">:</td>
                    <td width="27%">{{ $kitchenAddress }}</td>
                </tr>
                <tr>
                    <td>ID Order</td>
                    <td>:</td>
                    <td>{{ $customerOrderNumber }}</td>
                    <td>Tgl Kirim</td>
                    <td>:</td>
                    <td>{{ $formattedDeliveryDate }}</td>
                </tr>
                <tr>
                    <td>No Telepon</td>
                    <td>:</td>
                    <td>{{ $kitchenPhone }}</td>
                    <td colspan="3"></td>
                </tr>
            </table>

        @elseif ($templateType === 'gemilang')

            <table class="meta-table">
                <tr>
                    <td width="12%">Pelanggan</td>
                    <td width="1%">:</td>
                    <td width="47%">{{ $kitchenName }}</td>
                    <td width="15%">ID Pelanggan</td>
                    <td width="1%">:</td>
                    <td width="24%">{{ $customerOrderNumber }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $kitchenAddress }}</td>
                    <td>Tanggal Pengiriman</td>
                    <td>:</td>
                    <td>{{ $formattedDeliveryDate }}</td>
                </tr>
                <tr>
                    <td>No Tlp</td>
                    <td>:</td>
                    <td>{{ $kitchenPhone }}</td>
                    <td colspan="3"></td>
                </tr>
            </table>

        @elseif ($templateType === 'zenzi')

            <table class="meta-table">
                <tr>
                    <td width="12%">Kepada</td>
                    <td width="1%">:</td>
                    <td width="87%">{{ $kitchenName }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $kitchenAddress }}</td>
                </tr>
                <tr>
                    <td>Nomor TLP</td>
                    <td>:</td>
                    <td>
                        {{ $kitchenPhone }}

                        <span style="margin-left: 150px;">
                            Tanggal: {{ $formattedDeliveryDate }}
                        </span>
                    </td>
                </tr>
            </table>

        @elseif ($templateType === 'sumber_rejeki')

            <table class="meta-table">
                <tr>
                    <td width="12%">Kepada</td>
                    <td width="1%">:</td>
                    <td width="47%">{{ $kitchenName }}</td>
                    <td width="12%">ID Order</td>
                    <td width="1%">:</td>
                    <td width="27%">{{ $customerOrderNumber }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $kitchenAddress }}</td>
                    <td>No Telepon</td>
                    <td>:</td>
                    <td>{{ $kitchenPhone }}</td>
                </tr>
                <tr>
                    <td>Tgl Kirim</td>
                    <td>:</td>
                    <td>{{ $formattedDeliveryDate }}</td>
                    <td colspan="3"></td>
                </tr>
            </table>

        @endif

        {{-- TABEL BARANG DARI INVOICE --}}
        <table class="items-table {{ $templateType === 'zenzi' ? 'zenzie-table' : '' }}">
            <thead>
                <tr>
                    @if ($templateType === 'zenzi')
                        <th width="15%">KODE BARANG</th>
                        <th width="37%">NAMA BARANG</th>
                        <th width="10%">JUMLAH</th>
                        <th width="10%">SATUAN</th>
                        <th width="13%">HARGA</th>
                        <th width="15%">TOTAL</th>
                    @else
                        <th width="15%">KODE</th>
                        <th width="38%">JENIS BARANG</th>
                        <th width="8%">QTY</th>
                        <th width="10%">SATUAN</th>
                        <th width="14%">HARGA</th>
                        <th width="15%">JUMLAH</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse ($invoiceDetails as $index => $detail)
                    <tr>
                        <td class="text-center">
                            {{ $detail->item?->code ?? '-' }}
                        </td>

                        <td>{{ $detail->item?->name ?? '-' }}</td>

                        <td class="text-center">
                            {{ rtrim(rtrim(number_format((float) $detail->quantity, 2, ',', '.'), '0'), ',') }}
                        </td>

                        <td class="text-center">{{ $detail->unit ?? '-' }}</td>

                        <td class="text-right">
                            @if ($templateType !== 'zenzi')
                                <span style="float:left;">Rp</span>
                            @endif

                            {{ number_format((float) ($detail->unit_price ?? 0), 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            @if ($templateType !== 'zenzi')
                                <span style="float:left;">Rp</span>
                            @endif

                            {{ number_format((float) ($detail->subtotal ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            Belum ada detail barang pada Invoice.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="footer-container">

            <div class="footer-left">
                <div class="signatures">

                    @if ($templateType === 'zenzi')

                        <div class="signature-box">
                            Dibuat oleh:
                            <div class="signature-space"></div>
                            Admin
                        </div>

                        <div class="signature-box">
                            Diserahkan oleh:
                            <div class="signature-space"></div>
                            ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                        </div>

                        <div class="signature-box">
                            Diterima oleh:
                            <div class="lunas-stamp">
                                @if ($capLunas)
                                    <img src="{{ asset('storage/' . $capLunas) }}" alt="Cap Lunas">
                                @endif
                            </div>
                            ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                        </div>

                    @else

                        <div class="signature-box">
                            Diserahkan oleh:
                            <div class="signature-space"></div>
                            ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                        </div>

                        <div class="signature-box">
                            <div style="height: 17px;"></div>
                            <div class="lunas-stamp">
                                @if ($capLunas)
                                    <img src="{{ asset('storage/' . $capLunas) }}" alt="Cap Lunas">
                                @endif
                            </div>
                            <div style="height: 17px;"></div>
                        </div>

                        <div class="signature-box">
                            Diterima oleh:
                            <div class="signature-space"></div>
                            ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                        </div>

                    @endif

                </div>
            </div>

            <div class="footer-right">
                <table class="summary-table">
                    <tr>
                        <td class="font-bold text-right">TOTAL</td>
                        <td class="text-center">Rp</td>
                        <td class="text-right font-bold">
                            {{ number_format($notaTotalAmount, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="font-bold text-right">DISKON</td>
                        <td class="text-center">Rp</td>
                        <td class="text-right">-</td>
                    </tr>

                    <tr class="summary-total">
                        <td class="font-bold text-right">SUB TOTAL</td>
                        <td class="text-center font-bold">Rp</td>
                        <td class="text-right font-bold">
                            {{ number_format($notaTotalAmount, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                <div class="terbilang">
                    <strong>Terbilang:</strong>

                    {{ ucwords(\Illuminate\Support\Number::spell($notaTotalAmount, 'id')) }}
                    Rupiah
                </div>
            </div>

        </div>
    </div>

</body>
</html>