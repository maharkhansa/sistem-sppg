<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Belanja Program MBG</title>

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
            background: white;
            padding: 25px 30px;
            border: 1px solid #d1d5db;
        }

        /* ACTIONS BAR (HANYA DI TAMPILAN MONITOR) */
        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        /* ALERT MESSAGES */
        .alert {
            padding: 10px 14px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* HEADER */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .header-left h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-left .sppg-name {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .header-left .date {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            text-transform: uppercase;
        }

        /* KOTAK DAPUR / KOTA (HIJAU TERCETAK DI PRINTER) */
        .kitchen-box {
            background-color: #1e7e34 !important;
            color: #ffffff !important;
            padding: 10px 20px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            min-width: 140px;
            display: inline-block;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* TABEL INVOICE */
        .supplier-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 11px;
        }

        .invoice-table th {
            background: #e5e7eb !important;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* SUPPLIER FOOTER / APPROVAL BAR */
        .supplier-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
            background: #fff;
        }

        .approval-text {
            font-family: monospace;
        }

        .subtotal-val {
            font-weight: bold;
            font-size: 12px;
        }

        /* TOTAL KESELURUHAN */
        .grand-total-container {
            display: flex;
            justify-content: flex-end;
            margin: 15px 0 30px;
        }

        .grand-total-box {
            display: flex;
            gap: 20px;
            align-items: center;
            font-weight: bold;
            font-size: 13px;
            padding-right: 10px;
        }

        /* SIGNATURE SECTION */
        .signature-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-grid-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .signature-box {
            text-align: center;
            width: 260px;
        }

        .signature-space {
            height: 60px;
        }

        .signature-line {
            border-bottom: 1px dashed #000;
            margin: 0 auto;
            width: 80%;
        }

        .signature-name-bold {
            font-weight: bold;
            margin-top: 10px;
        }

        .knowing-title {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .signature-grid-bottom {
            display: flex;
            justify-content: space-between;
        }

        /* PENGATURAN CETAK (PRINT MEDIA) */
        @media print {
            /* MENGHILANGKAN HEADER & FOOTER BAWAAN BROWSER (TANGGAL, JAM, JUDUL DOKUMEN) */
            @page {
                margin: 10mm;
                size: auto;
            }

            body {
                background: white;
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .top-actions,
            .alert {
                display: none !important;
            }

            .invoice-card {
                border: none;
                padding: 0;
            }

            .kitchen-box {
                background-color: #1e7e34 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .invoice-table th {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">

    {{-- Tombol Navigasi --}}
    <div class="top-actions">
        <a href="{{ route('stock-transactions.out') }}" class="btn btn-back">
            ← Kembali ke OUT
        </a>
        <button onclick="window.print()" class="btn btn-print">
            🖨 Cetak Invoice
        </button>
    </div>

    {{-- Pesan Alert --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @forelse($invoices as $invoice)
        @php
            /* 
             * Membersihkan nama dapur/kota agar yang tampil hanya nama daerah singkat.
             * Contoh: "KOTA MAGELANG MAGELANG UTARA KEDUNGSARI 2" -> "KEDUNGSARI 2"
             */
            $rawKitchenName = $invoice->kitchen?->name ?? 'Adikarto';
            
            // Bersihkan kata-kata umum jika ada
            $cleanedName = trim(str_ireplace(['SPPG', 'DAPUR', 'KITCHEN'], '', $rawKitchenName));
            
            // Ambil maksimal 2 kata terakhir dari nama daerah
            $words = explode(' ', $cleanedName);
            if (count($words) > 2) {
                $kitchenRegion = implode(' ', array_slice($words, -2));
            } else {
                $kitchenRegion = $cleanedName;
            }
        @endphp

        <div class="invoice-card">

            {{-- HEADER --}}
            <div class="invoice-header">
                <div class="header-left">
                    <h1>INVOICE BELANJA PROGRAM MBG</h1>
                    <div class="sppg-name">SPPG AGHITS STAR {{ strtoupper($kitchenRegion) }}</div>
                    <div class="date">
                        TANGGAL : {{ strtoupper(\Carbon\Carbon::parse($invoice->invoice_date)->translatedFormat('l, d F Y')) }}
                    </div>
                </div>

                <div class="header-right">
                    {{-- KOTAK DAPUR BERWARNA HIJAU DI KANAN ATAS --}}
                    <div class="kitchen-box">
                        {{ strtoupper($kitchenRegion) }}
                    </div>
                </div>
            </div>

            @php
                $groupedDetails = $invoice->details->groupBy(function ($detail) {
                    return $detail->supplier_id ?? 0;
                });
                $rowNumber = 1;
            @endphp

            {{-- TABEL PER SUPPLIER --}}
            @foreach($groupedDetails as $supplierId => $details)
                @php
                    $supplierSubtotal = $details->sum('subtotal');
                @endphp

                <div class="supplier-section">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">NO</th>
                                <th style="width: 180px;">SUPPLIER</th>
                                <th style="width: 110px;">KODE BARANG</th>
                                <th>JENIS BARANG</th>
                                <th style="width: 50px;">QTY</th>
                                <th style="width: 60px;">SATUAN</th>
                                <th style="width: 90px;">HARGA</th>
                                <th style="width: 110px;">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                                <tr>
                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                    <td>{{ strtoupper($detail->supplier?->name ?? '-') }}</td>
                                    <td class="text-center">{{ $detail->item?->code ?? '-' }}</td>
                                    <td>{{ $detail->item?->name ?? '-' }}</td>
                                    <td class="text-center">{{ rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td class="text-center">{{ $detail->unit }}</td>
                                    <td class="text-right">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- STATUS APPROVAL & SUBTOTAL BAR --}}
                    <div class="supplier-footer">
                        <div class="approval-text">
                            ☐ approved &nbsp; ☐ not approved &nbsp; ☐ pen | Date approval : ______________________
                        </div>
                        <div class="subtotal-val">
                            Rp {{ number_format($supplierSubtotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- TOTAL KESELURUHAN --}}
            <div class="grand-total-container">
                <div class="grand-total-box">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- TANDA TANGAN --}}
            <div class="signature-section">
                {{-- Baris Atas --}}
                <div class="signature-grid-top">
                    <div class="signature-box">
                        <div>Sales Manager</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-box">
                        <div>Asisten Lapangan</div>
                        <div>SPPG Aghits Star {{ ucfirst(strtolower($kitchenRegion)) }}</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>
                </div>

                {{-- Mengetahui --}}
                <div class="knowing-title">Mengetahui,</div>

                {{-- Baris Bawah --}}
                <div class="signature-grid-bottom">
                    <div class="signature-box">
                        <div>Ketua Yayasan Aghits Star</div>
                        <div>International</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                        <div class="signature-name-bold">Teguh Hadi Susilo</div>
                    </div>

                    <div class="signature-box">
                        <div>Akuntan SPPG Aghits Star</div>
                        <div>{{ ucfirst(strtolower($kitchenRegion)) }}</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-box">
                        <div>Ka. SPPG Aghits Star</div>
                        <div>{{ ucfirst(strtolower($kitchenRegion)) }}</div>
                        <div class="signature-space"></div>
                        <div class="signature-line"></div>
                    </div>
                </div>
            </div>

        </div>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @empty
        <div class="invoice-card">
            <div class="text-center" style="padding: 40px 0;">
                <h3>Belum Ada Invoice</h3>
                <p>Invoice akan muncul setelah transaksi OUT diproses menjadi Invoice.</p>
                <a href="{{ route('stock-transactions.out') }}" class="btn btn-back">← Kembali ke OUT</a>
            </div>
        </div>
    @endforelse

</div>

</body>
</html>