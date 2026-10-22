@extends('layouts.app')

@section('title', 'Nota Keluar - ' . ($notaKeluar->nota_number ?? 'Detail'))

@push('styles')
<style>
    .nota-detail-page {
        padding: 5px 0 15px;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
    }

    .no-print-actions {
        max-width: 900px;
        margin: 0 auto 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-nota {
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
        padding: 18px 25px;
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

    /* HEADER UMUM: lebih pendek dan rapat */
    .supplier-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0;
        margin-bottom: 5px;
        gap: 12px;
    }

    .supplier-kop {
        width: 13%;
        max-width: 90px;
        flex-shrink: 0;
        margin: 0;
        padding: 0;
    }

    .supplier-kop img {
        display: block;
        width: 100%;
        max-width: 90px;
        max-height: 65px;
        height: auto;
        object-fit: contain;
        margin: 0;
    }

    .supplier-info {
        flex: 1;
        min-width: 0;
        padding: 0 0 0 5px;
    }

    .supplier-name {
        font-size: 13px;
        font-weight: bold;
        margin: 0 0 2px;
        line-height: 1.15;
    }

    .supplier-address,
    .supplier-contact {
        font-size: 9px;
        line-height: 1.25;
        margin: 0;
    }

    .header-right {
        width: 30%;
        flex-shrink: 0;
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .document-title {
        font-size: 15px;
        font-weight: bold;
        color: #1a0dab;
        margin: 0 0 3px;
        line-height: 1.1;
        white-space: normal;
    }

    .document-title.black {
        color: #000;
    }

    .payment-no {
        font-size: 9px;
        font-weight: bold;
        margin: 0 0 2px;
        line-height: 1.1;
    }

    /* HEADER TOP FAST: identitas perusahaan dipadatkan */
    .topfast-header {
        width: 100%;
        text-align: center;
        margin: 0 0 5px;
        padding: 0;
        line-height: 1.2;
    }

    .topfast-name {
        font-size: 14px;
        font-weight: bold;
        margin: 0 0 2px;
        line-height: 1.15;
    }

    .topfast-address {
        font-size: 9px;
        line-height: 1.2;
        margin: 0;
    }

    .barcode-container {
        width: 145px;
        max-width: 100%;
        text-align: center;
    }

    .barcode-img {
        display: block;
        width: 145px;
        max-width: 100%;
        height: 27px;
        margin: 0 auto;
    }

    .barcode-number {
        font-size: 9px;
        font-family: "Courier New", monospace;
        font-weight: bold;
        letter-spacing: .5px;
        margin-top: 1px;
        overflow-wrap: anywhere;
        line-height: 1.1;
    }

    .zenzie-payment {
        font-size: 14px;
        color: #000;
    }

    /* INFORMASI DAPUR: jarak antarbaris lebih rapat */
    .nota-wrapper table.meta-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        margin-top: 0;
        margin-bottom: 5px;
    }

    .nota-wrapper table.meta-table td {
        border: none;
        padding: 1px 3px;
        vertical-align: top;
        overflow-wrap: anywhere;
        line-height: 1.2;
    }

    /* TABEL DETAIL BARANG */
    .nota-wrapper table.items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 2px;
        margin-bottom: 4px;
        font-size: 11px;
    }

    .nota-wrapper table.items-table th,
    .nota-wrapper table.items-table td {
        border: none;
        padding: 1px 4px;
        overflow-wrap: anywhere;
        vertical-align: middle;
    }

    .nota-wrapper table.items-table thead th {
        padding: 3px 4px;
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.1;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        border-left: none;
        border-right: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .nota-wrapper table.items-table tbody td {
        padding: 1px 4px;
        line-height: 1.15;
        font-size: 11px;
        border: none;
    }

    .nota-wrapper table.items-table tbody tr + tr td {
        border-top: 1px solid #eee;
    }

    .nota-wrapper table.items-table.zenzie-table thead th {
        background: transparent;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        border-left: none;
        border-right: none;
    }

    .nota-wrapper table.items-table.zenzie-table tbody td {
        padding: 1px 4px;
        border: none;
    }

    .nota-wrapper table.items-table.zenzie-table tbody tr + tr td {
        border-top: 1px solid #eee;
    }

    .nota-wrapper table.items-table .nota-empty {
        text-align: center;
        padding: 5px 4px !important;
        border: none !important;
    }

    /* FOOTER DAN TANDA TANGAN */
    .footer-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-top: 9px;
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
        margin-top: 8px;
        font-size: 10px;
    }

    .signature-box {
        flex: 1;
        min-width: 0;
        text-align: center;
    }

    .signature-space {
        height: 45px;
    }

    /* Tetap menyediakan ruang untuk cap basah */
    .lunas-stamp {
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .terbilang {
        margin-top: 8px;
        font-size: 10px;
        font-style: italic;
        font-weight: bold;
        overflow-wrap: anywhere;
    }

    .nota-wrapper table.summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .nota-wrapper table.summary-table td {
        border: none;
        padding: 3px 0;
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
            padding: 12px 10px;
            overflow-x: auto;
        }

        .supplier-header {
            gap: 7px;
        }

        .supplier-kop {
            width: 17%;
            max-width: 75px;
        }

        .supplier-kop img {
            max-width: 75px;
            max-height: 55px;
        }

        .supplier-info {
            padding-left: 2px;
        }

        .supplier-name {
            font-size: 11px;
        }

        .supplier-address,
        .supplier-contact {
            font-size: 8px;
        }

        .header-right {
            width: 36%;
        }

        .document-title {
            font-size: 12px;
            white-space: normal;
        }

        .barcode-container {
            width: 110px;
        }

        .barcode-img {
            width: 110px;
            height: 24px;
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
            margin: 7mm;
        }

        body * {
            visibility: hidden !important;
        }

        aside,
        nav,
        .sidebar,
        .main-sidebar,
        .sidebar-wrapper,
        .navbar,
        .main-header,
        .main-footer,
        .app-sidebar,
        .no-print-actions {
            display: none !important;
        }

        .nota-wrapper,
        .nota-wrapper * {
            visibility: visible !important;
        }

        html,
        body {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            background: #fff !important;
        }

        .container,
        .container-fluid,
        .content,
        .content-wrapper,
        .main-content,
        .page-content,
        .nota-detail-page {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            transform: none !important;
            overflow: visible !important;
        }

        .nota-wrapper {
            display: block !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #fff !important;
            overflow: visible !important;
            font-size: 10px !important;
        }

        /* Semua header supplier dibuat lebih kecil saat dicetak */
        .supplier-header {
            margin-top: 0 !important;
            margin-bottom: 3px !important;
            gap: 8px !important;
            align-items: center !important;
        }

        .supplier-kop {
            width: 13% !important;
            max-width: 75px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .supplier-kop img {
            width: 100% !important;
            max-width: 75px !important;
            max-height: 48px !important;
            height: auto !important;
            object-fit: contain !important;
        }

        .supplier-info {
            padding: 0 0 0 3px !important;
        }

        .supplier-name {
            font-size: 11px !important;
            margin-bottom: 1px !important;
            line-height: 1.1 !important;
        }

        .supplier-address,
        .supplier-contact {
            font-size: 8px !important;
            line-height: 1.15 !important;
        }

        .header-right {
            width: 30% !important;
        }

        .document-title {
            font-size: 12px !important;
            margin-bottom: 2px !important;
            line-height: 1.1 !important;
        }

        .zenzie-payment {
            font-size: 12px !important;
        }

        .payment-no {
            font-size: 8px !important;
            margin-bottom: 1px !important;
        }

        .barcode-container {
            width: 120px !important;
        }

        .barcode-img {
            width: 120px !important;
            height: 23px !important;
        }

        .barcode-number {
            font-size: 8px !important;
            margin-top: 1px !important;
        }

        /* TOP FAST dirapatkan lagi khusus cetak */
        .topfast-header {
            margin: 0 0 3px !important;
            padding: 0 !important;
            line-height: 1.1 !important;
        }

        .topfast-name {
            font-size: 12px !important;
            margin: 0 0 1px !important;
            line-height: 1.1 !important;
        }

        .topfast-address {
            font-size: 8px !important;
            line-height: 1.1 !important;
            margin: 0 !important;
        }

        .nota-wrapper table.meta-table {
            font-size: 9px !important;
            margin-top: 0 !important;
            margin-bottom: 3px !important;
        }

        .nota-wrapper table.meta-table td {
            padding: 1px 3px !important;
            line-height: 1.1 !important;
        }

        .nota-wrapper table.items-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin-top: 2px !important;
            margin-bottom: 4px !important;
            font-size: 10px !important;
        }

        .nota-wrapper table.items-table thead {
            display: table-header-group !important;
        }

        .nota-wrapper table.items-table th {
            padding: 2px 3px !important;
            font-size: 9px !important;
            line-height: 1.1 !important;
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            border-left: none !important;
            border-right: none !important;
            background: #d9d9d9 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .nota-wrapper table.items-table td {
            padding: 1px 3px !important;
            font-size: 10px !important;
            line-height: 1.1 !important;
            border: none !important;
            overflow-wrap: anywhere !important;
        }

        .nota-wrapper table.items-table tbody tr + tr td {
            border-top: 1px solid #eee !important;
        }

        .nota-wrapper table.items-table.zenzie-table thead th {
            background: transparent !important;
        }

        .footer-container {
            margin-top: 7px !important;
            gap: 8px !important;
        }

        .signatures {
            margin-top: 6px !important;
        }

        .signature-space {
            height: 40px !important;
        }

        .lunas-stamp {
            height: 45px !important;
        }

        .terbilang {
            margin-top: 5px !important;
            font-size: 9px !important;
        }

        .nota-wrapper table.summary-table {
            font-size: 9px !important;
        }

        .nota-wrapper table.summary-table td {
            padding: 2px 0 !important;
        }

        .nota-wrapper table.items-table tr,
        .footer-container,
        .signatures {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | SUMBER INVOICE DAN DETAIL NOTA
    |--------------------------------------------------------------------------
    */
    $invoiceForNota = $invoice ?? null;

    if (!$invoiceForNota && $notaKeluar->stockTransaction) {
        $invoiceForNota = $notaKeluar->stockTransaction->invoice;
    }

    $allInvoiceDetails = collect(
        $invoiceDetails ?? $invoiceForNota?->details ?? []
    );

    $supplier = $notaKeluar->supplier
        ?? $allInvoiceDetails->firstWhere('supplier_id', $notaKeluar->supplier_id)?->supplier
        ?? $allInvoiceDetails->first()?->supplier;

    $supplierId = $notaKeluar->supplier_id ?? $supplier?->id;

    $notaType = strtolower((string) ($notaKeluar->nota_type ?? 'stock'));

    $isExpenseNota = in_array(
        $notaType,
        ['expense', 'non_stock', 'non-stock', 'biaya'],
        true
    );

    $sectionName = trim((string) ($notaKeluar->section_name ?? ''));

    $invoiceDetails = $allInvoiceDetails
        ->filter(function ($detail) use ($supplierId, $sectionName, $isExpenseNota) {
            if (
                $supplierId
                && (string) $detail->supplier_id !== (string) $supplierId
            ) {
                return false;
            }

            if (
                trim((string) ($detail->section_name ?? '')) !== $sectionName
            ) {
                return false;
            }

            $detailType = strtolower(
                trim((string) ($detail->detail_type ?? ''))
            );

            $hasExpenseType = !empty($detail->expense_type_id);
            $hasExpenseDate = !empty($detail->expense_date);

            $detailIsExpense = $hasExpenseType
                || in_array($detailType, [
                    'expense',
                    'biaya',
                    'non_stock',
                    'non-stock',
                    'operasional',
                    'operational',
                    'nonstok',
                    'non-stok',
                ], true)
                || (empty($detail->item_id) && $hasExpenseDate);

            return $detailIsExpense === $isExpenseNota;
        })
        ->values();

    $notaTotalAmount = (float) $invoiceDetails->sum(function ($detail) {
        return (float) (
            $detail->subtotal
            ?? (
                (float) ($detail->quantity ?? 0)
                * (float) ($detail->unit_price ?? 0)
            )
        );
    });

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE SUPPLIER
    |--------------------------------------------------------------------------
    */
    $templateType = strtolower(
        trim((string) ($supplier?->nota_template ?? 'gemilang'))
    );

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

    if (!in_array(
        $templateType,
        ['gemilang', 'zenzi', 'sumber_rejeki', 'top_fast'],
        true
    )) {
        $templateType = 'gemilang';
    }

    $kopSupplier = match ($templateType) {
        'gemilang' => 'kop-supplier/gemilang-mart.jpeg',
        'zenzi' => 'kop-supplier/zenzie.jpeg',
        'sumber_rejeki' => 'kop-supplier/sumber-rejeki.jpeg',
        'top_fast' => 'kop-supplier/topfast.jpeg',
        default => 'kop-supplier/gemilang-mart.jpeg',
    };

    /*
    |--------------------------------------------------------------------------
    | BARCODE
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
    | INFORMASI DAPUR DAN TANGGAL
    |--------------------------------------------------------------------------
    */
    $kitchen = $notaKeluar->kitchen;

    $kitchenName = $kitchen?->name ?? '-';
    $kitchenAddress = $kitchen?->alamat ?: ($kitchen?->address ?? '-');
    $kitchenPhone = $kitchen?->phone ?? '-';

    $customerOrderNumber = $notaKeluar->customer_order_number
        ?? $notaKeluar->nota_number
        ?? '-';

    $deliveryDate = $invoiceForNota?->invoice_date
        ?? $invoiceForNota?->date
        ?? $notaKeluar->nota_date
        ?? $notaKeluar->created_at;

    $formattedDeliveryDate = $deliveryDate
        ? \Carbon\Carbon::parse($deliveryDate)
            ->locale('id')
            ->translatedFormat('j F Y')
        : '-';

    $notaTitle = $isExpenseNota
        ? 'NOTA BIAYA NON-STOK'
        : 'NOTA PENJUALAN';
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
                    Jl. Mayor Unus, Km. 05, Honggosari, Jogonegoro, Mertoyudan, Magelang
                </div>
                <div class="topfast-address">
                    Telp: 082220338007 &nbsp;&nbsp;&nbsp;
                    Website: www.topfastmart.store
                </div>
            </div>

            <div class="supplier-header">
                <div class="supplier-kop">
                    <img
                        src="{{ asset('storage/' . $kopSupplier) }}"
                        alt="Kop Supplier"
                        onerror="this.style.display='none'">
                </div>

                <div class="header-right">
                    <div class="document-title black">{{ $notaTitle }}</div>

                    <div class="barcode-container">
                        <div class="payment-no">NO. PAYMENT</div>

                        @if ($barcodeSvg !== '')
                            <img
                                src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                class="barcode-img"
                                alt="Barcode">
                        @endif

                        <div class="barcode-number">{{ $barcodeRaw }}</div>
                    </div>
                </div>
            </div>
        @else
            {{-- HEADER SUPPLIER LAIN --}}
            <div class="supplier-header">
                <div class="supplier-kop">
                    <img
                        src="{{ asset('storage/' . $kopSupplier) }}"
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
                            {{ $supplier?->name ?? 'Koperasi Sumber Rezeki' }}
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
                        <div class="document-title">{{ $notaTitle }}</div>
                    @elseif (in_array($templateType, ['zenzi', 'sumber_rejeki']))
                        <div class="document-title black zenzie-payment">
                            {{ $notaTitle }}
                        </div>
                    @endif

                    <div class="barcode-container">
                        <div class="payment-no">NO. PAYMENT</div>

                        @if ($barcodeSvg !== '')
                            <img
                                src="data:image/svg+xml;base64,{{ $barcodeSvg }}"
                                class="barcode-img"
                                alt="Barcode">
                        @endif

                        <div class="barcode-number">{{ $barcodeRaw }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- INFORMASI DAPUR: TOP FAST --}}
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

        {{-- INFORMASI DAPUR: GEMILANG --}}
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

        {{-- INFORMASI DAPUR: ZENZI --}}
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
                        <span style="margin-left:30px;">
                            Tanggal: {{ $formattedDeliveryDate }}
                        </span>
                    </td>
                </tr>
            </table>

        {{-- INFORMASI DAPUR: SUMBER REZEKI --}}
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

        {{-- TABEL DETAIL --}}
        <table class="items-table {{ $templateType === 'zenzi' ? 'zenzie-table' : '' }}">
            <thead>
                <tr>
                    @if ($isExpenseNota)
                        <th width="10%">Tanggal</th>
                        <th width="35%">Jenis Biaya / Keterangan</th>
                        <th width="10%">Qty</th>
                        <th width="10%">Satuan</th>
                        <th width="17%">Harga</th>
                        <th width="18%">Jumlah</th>
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

                        $item = $detail->item ?? null;
                        $expenseType = $detail->expenseType ?? null;
                    @endphp

                    <tr>
                        @if ($isExpenseNota)
                            <td class="text-center">
                                {{ $detail->expense_date
                                    ? \Carbon\Carbon::parse($detail->expense_date)->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $expenseType?->name ?? 'Biaya Non-Stok' }}
                                </strong>

                                @if (trim((string) ($detail->notes ?? '')) !== '')
                                    <div style="margin-top:3px;color:#444;">
                                        {{ $detail->notes }}
                                    </div>
                                @endif
                            </td>
                        @else
                            <td class="text-center">
                                {{ $item?->code ?? $item?->item_code ?? '-' }}
                            </td>

                            <td>{{ $item?->name ?? '-' }}</td>
                        @endif

                        <td class="text-center">
                            {{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}
                        </td>

                        <td class="text-center">
                            {{ $detail->unit ?? $item?->unit ?? '-' }}
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
                            Belum ada detail
                            {{ $isExpenseNota ? 'biaya non-stok' : 'barang' }}
                            pada Invoice untuk supplier dan bagian nota ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER DAN TANDA TANGAN --}}
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
                            <div class="lunas-stamp"></div>
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
                            <div class="lunas-stamp"></div>
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