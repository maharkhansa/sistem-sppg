@extends('layouts.app')

@section('title', 'Nota Keluar - ' . ($notaKeluar->nota_number ?? 'Detail'))

@push('styles')
<style>
    .nota-detail-page {
        padding: 10px 0 25px;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
    }

    .no-print-actions {
        max-width: 900px;
        margin: 0 auto 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .nota-detail-page .btn-nota {
        display: inline-block;
        padding: 9px 14px;
        border-radius: 5px;
        border: none;
        text-decoration: none;
        cursor: pointer;
        font-size: 13px;
        font-family: Arial, Helvetica, sans-serif;
    }

    .btn-nota-back {
        background: #6c757d;
        color: #fff;
    }

    .btn-nota-back:hover {
        background: #565e64;
        color: #fff;
    }

    .btn-nota-print {
        background: #198754;
        color: #fff;
    }

    .btn-nota-print:hover {
        background: #146c43;
        color: #fff;
    }

    .nota-wrapper {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        padding: 25px 35px;
        border: 1px solid #ccc;
        box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
    }

    .nota-wrapper *,
    .nota-wrapper *::before,
    .nota-wrapper *::after {
        box-sizing: border-box;
    }

    .nota-wrapper .font-bold {
        font-weight: bold;
    }

    .nota-wrapper .text-center {
        text-align: center;
    }

    .nota-wrapper .text-right {
        text-align: right;
    }

    .nota-wrapper .text-left {
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
        width: 15%;
        flex-shrink: 0;
    }

    .supplier-kop img {
        display: block;
        width: 100%;
        max-width: 120px;
        height: auto;
        object-fit: contain;
    }

    .supplier-info {
        flex: 1;
        padding-left: 8px;
        padding-top: 1px;
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
        max-width: 100%;
        text-align: center;
    }

    .barcode-img {
        display: block;
        width: 180px;
        max-width: 100%;
        height: 35px;
        margin: 0 auto;
    }

    .barcode-number {
        font-size: 10px;
        font-family: "Courier New", monospace;
        font-weight: bold;
        letter-spacing: 1px;
        margin-top: 3px;
        overflow-wrap: anywhere;
    }

    .zenzie-payment {
        font-size: 16px;
        color: #000;
    }

    .nota-wrapper table.meta-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 10px;
    }

    .nota-wrapper table.meta-table td {
        border: none;
        padding: 2px 4px;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .nota-wrapper table.items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 15px;
        font-size: 11px;
    }

    .nota-wrapper table.items-table th,
    .nota-wrapper table.items-table td {
        border: 1px solid #000;
        padding: 6px 7px;
        overflow-wrap: anywhere;
    }

    .nota-wrapper table.items-table th {
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
        text-transform: uppercase;
    }

    .nota-wrapper table.items-table.zenzie-table th {
        background: transparent;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        border-left: none;
        border-right: none;
    }

    .nota-wrapper table.items-table.zenzie-table td {
        border: none;
        border-bottom: 1px solid #ddd;
    }

    .footer-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-top: 15px;
        font-size: 11px;
    }

    .footer-left {
        width: 57%;
    }

    .footer-right {
        width: 40%;
        min-width: 0;
    }

    .signatures {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-top: 15px;
        font-size: 10px;
    }

    .signature-box {
        flex: 1;
        min-width: 0;
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
        width: 100%;
        max-width: 200px;
        height: auto;
        max-height: 55px;
        object-fit: contain;
        opacity: .68;
        filter: blur(.35px) contrast(1.15);
        mix-blend-mode: multiply;
        transform: rotate(-1deg);
    }

    .terbilang {
        margin-top: 12px;
        font-size: 11px;
        font-style: italic;
        font-weight: bold;
        overflow-wrap: anywhere;
    }

    .nota-wrapper table.summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .nota-wrapper table.summary-table td {
        border: none;
        padding: 4px 0;
    }

    .summary-total {
        border-top: 1px dashed #000;
        border-bottom: 1px dashed #000;
    }

    .nota-empty {
        text-align: center;
        padding: 15px !important;
    }

    @media (max-width: 700px) {
        .nota-wrapper {
            padding: 18px 12px;
            overflow-x: auto;
        }

        .supplier-header {
            gap: 8px;
        }

        .supplier-kop {
            width: 18%;
        }

        .header-right {
            width: 38%;
        }

        .document-title {
            font-size: 14px;
            white-space: normal;
        }

        .nota-wrapper table.items-table {
            min-width: 600px;
        }

        .footer-container {
            flex-wrap: wrap;
        }

        .footer-left,
        .footer-right {
            width: 100%;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body * {
            visibility: hidden !important;
        }

        .nota-wrapper,
        .nota-wrapper * {
            visibility: visible !important;
        }

        .no-print-actions {
            display: none !important;
        }

        .nota-detail-page {
            padding: 0 !important;
            margin: 0 !important;
        }

        .nota-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
            background: #fff;
            overflow: visible;
        }

        .nota-wrapper table.items-table {
            min-width: 0;
        }

        .nota-wrapper table.items-table thead {
            display: table-header-group;
        }

        .nota-wrapper table.items-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .footer-container,
        .signatures {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .nota-wrapper table.items-table th {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endpush

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | INVOICE SUMBER NOTA
    |--------------------------------------------------------------------------
    | Invoice adalah sumber utama detail barang dan nilai nota.
    */

    $invoiceForNota = $invoice ?? null;

    if (!$invoiceForNota) {
        $transaction = $notaKeluar->stockTransaction;

        if ($transaction) {
            $invoiceForNota = $transaction->invoice;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL INVOICE DAN SUPPLIER
    |--------------------------------------------------------------------------
    */

    $allInvoiceDetails = collect(
        $invoiceDetails
            ?? $invoiceForNota?->details
            ?? []
    );

    $supplier = $notaKeluar->supplier
        ?? $allInvoiceDetails
            ->firstWhere('supplier_id', $notaKeluar->supplier_id)
            ?->supplier
        ?? $allInvoiceDetails->first()?->supplier;

    $supplierId = $notaKeluar->supplier_id
        ?? $supplier?->id;

    /*
     * Nota supplier hanya menampilkan detail Invoice milik supplier
     * yang sesuai. Jika nota belum memiliki supplier_id, detail
     * tidak difilter berdasarkan supplier.
     */
    $invoiceDetails = $supplierId
        ? $allInvoiceDetails->filter(
            fn ($detail) => (string) $detail->supplier_id === (string) $supplierId
        )->values()
        : $allInvoiceDetails->values();

    /*
    |--------------------------------------------------------------------------
    | TOTAL NOTA DARI DETAIL INVOICE
    |--------------------------------------------------------------------------
    */

    $notaTotalAmount = (float) $invoiceDetails->sum(
        fn ($detail) => (float) (
            $detail->subtotal
            ?? (
                (float) ($detail->quantity ?? 0)
                * (float) ($detail->unit_price ?? 0)
            )
        )
    );

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE SUPPLIER
    |--------------------------------------------------------------------------
    */

    $templateType = strtolower(trim((string) ($supplier?->nota_template ?? 'gemilang')));

    $templateAliases = [
        'gemilang-mart' => 'gemilang',
        'gemilang_mart' => 'gemilang',
        'zenzie' => 'zenzi',
        'sumber-rejeki' => 'sumber_rejeki',
        'sumber-rejeki-mart' => 'sumber_rejeki',
        'top-fast' => 'top_fast',
        'top-fast-mart' => 'top_fast',
    ];

    $templateType = $templateAliases[$templateType] ?? $templateType;

    if (!in_array($templateType, [
        'gemilang',
        'zenzi',
        'sumber_rejeki',
        'top_fast',
    ], true)) {
        $templateType = 'gemilang';
    }

    /*
    |--------------------------------------------------------------------------
    | KOP SUPPLIER
    |--------------------------------------------------------------------------
    | Nama file harus sesuai dengan file di storage/app/public.
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
    | BARCODE CODE 128
    |--------------------------------------------------------------------------
    */

    $barcodeRaw = $notaKeluar->barcode_number
        ?? $notaKeluar->nota_number
        ?? '';

    $barcodeSvg = '';

    if ($barcodeRaw !== '') {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();

            $barcodeSvg = base64_encode(
                $generator->getBarcode(
                    (string) $barcodeRaw,
                    $generator::TYPE_CODE_128,
                    2,
                    35
                )
            );
        } catch (\Throwable $e) {
            $barcodeSvg = '';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INFORMASI DAPUR
    |--------------------------------------------------------------------------
    */

    $kitchen = $notaKeluar->kitchen;

    $kitchenName = $kitchen?->name ?? '-';

    $kitchenAddress = $kitchen?->alamat
        ?? $kitchen?->address
        ?? '-';

    $kitchenPhone = $kitchen?->phone ?? '-';

    $customerOrderNumber = $notaKeluar->customer_order_number
        ?? $notaKeluar->nota_number
        ?? '-';

    /*
    |--------------------------------------------------------------------------
    | TANGGAL INVOICE
    |--------------------------------------------------------------------------
    */

    $deliveryDate = $invoiceForNota?->invoice_date
        ?? $invoiceForNota?->date
        ?? $notaKeluar->nota_date
        ?? $notaKeluar->created_at;

    $formattedDeliveryDate = $deliveryDate
        ? \Carbon\Carbon::parse($deliveryDate)->translatedFormat('j F Y')
        : '-';
@endphp

<div class="container-fluid nota-detail-page">

    <div class="no-print-actions">
        <a href="{{ route('nota-keluars.index') }}"
           class="btn-nota btn-nota-back">
            ← Kembali ke Daftar Nota
        </a>

        <button type="button"
                onclick="window.print()"
                class="btn-nota btn-nota-print">
            🖨 Cetak Nota
        </button>
    </div>

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
                    <img src="{{ asset('storage/' . $kopSupplier) }}"
                         alt="Kop Supplier"
                         onerror="this.style.display='none'">
                </div>

                <div class="header-right">
                    <div class="document-title black">NOTA KONTAN</div>

                    <div class="barcode-container">
                        <div class="payment-no">NO. PAYMENT</div>

                        @if ($barcodeSvg !== '')
                            <img src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                 class="barcode-img"
                                 alt="Barcode">
                        @endif

                        <div class="barcode-number">{{ $barcodeRaw }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="supplier-header">
                <div class="supplier-kop">
                    <img src="{{ asset('storage/' . $kopSupplier) }}"
                         alt="Kop Supplier"
                         onerror="this.style.display='none'">
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
                @elseif ($templateType === 'sumber_rejeki')
                    <div class="supplier-info">
                        <div class="supplier-name">
                            {{ $supplier?->name ?? 'Koperasi Sumber Rejeki' }}
                        </div>
                        <div class="supplier-address">
                            {{ $supplier?->address ?? '' }}
                        </div>
                        @if ($supplier?->phone)
                            <div class="supplier-contact">
                                Telp: {{ $supplier->phone }}
                            </div>
                        @endif
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
                            <img src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                 class="barcode-img"
                                 alt="Barcode">
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
                        <span style="margin-left: 30px;">
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

        {{-- TABEL BARANG: SUMBER DATA INVOICE --}}
        <table class="items-table {{ $templateType === 'zenzi' ? 'zenzie-table' : '' }}">
            <thead>
                <tr>
                    @if ($templateType === 'zenzi')
                        <th width="15%">Kode Barang</th>
                        <th width="37%">Nama Barang</th>
                        <th width="10%">Jumlah</th>
                        <th width="10%">Satuan</th>
                        <th width="13%">Harga</th>
                        <th width="15%">Total</th>
                    @else
                        <th width="15%">Kode</th>
                        <th width="38%">Jenis Barang</th>
                        <th width="8%">QTY</th>
                        <th width="10%">Satuan</th>
                        <th width="14%">Harga</th>
                        <th width="15%">Jumlah</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse ($invoiceDetails as $detail)
                    @php
                        $quantity = (float) ($detail->quantity ?? 0);
                        $unitPrice = (float) ($detail->unit_price ?? 0);
                        $subtotal = (float) (
                            $detail->subtotal
                            ?? ($quantity * $unitPrice)
                        );
                    @endphp

                    <tr>
                        <td class="text-center">
                            {{ $detail->item?->code ?? '-' }}
                        </td>

                        <td>{{ $detail->item?->name ?? '-' }}</td>

                        <td class="text-center">
                            {{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}
                        </td>

                        <td class="text-center">
                            {{ $detail->unit ?? $detail->item?->unit ?? '-' }}
                        </td>

                        <td class="text-right">
                            @if ($templateType !== 'zenzi')
                                <span style="float:left">Rp</span>
                            @endif
                            {{ number_format($unitPrice, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            @if ($templateType !== 'zenzi')
                                <span style="float:left">Rp</span>
                            @endif
                            {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="nota-empty">
                            Belum ada detail barang pada Invoice untuk supplier ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TANDA TANGAN DAN TOTAL --}}
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
                            (________________)
                        </div>

                        <div class="signature-box">
                            Diterima oleh:
                            <div class="lunas-stamp">
                                @if ($capLunas)
                                    <img src="{{ asset('storage/' . $capLunas) }}"
                                         alt="Cap Lunas"
                                         onerror="this.style.display='none'">
                                @endif
                            </div>
                            (________________)
                        </div>
                    @else
                        <div class="signature-box">
                            Diserahkan oleh:
                            <div class="signature-space"></div>
                            (________________)
                        </div>

                        <div class="signature-box">
                            <div style="height:17px"></div>
                            <div class="lunas-stamp">
                                @if ($capLunas)
                                    <img src="{{ asset('storage/' . $capLunas) }}"
                                         alt="Cap Lunas"
                                         onerror="this.style.display='none'">
                                @endif
                            </div>
                            <div style="height:17px"></div>
                        </div>

                        <div class="signature-box">
                            Diterima oleh:
                            <div class="signature-space"></div>
                            (________________)
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
                    {{ ucwords(\Illuminate\Support\Number::spell((int) round($notaTotalAmount), 'id')) }}
                    Rupiah
                </div>
            </div>
        </div>

    </div>
</div>
@endsection