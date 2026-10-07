<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>LPDH - {{ $lpdh->kitchen->name }}</title>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 20px;
        background: #f3f4f6;
        font-family: Arial, Helvetica, sans-serif;
        color: #111827;
        font-size: 13px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    .top-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .btn {
        display: inline-block;
        padding: 9px 14px;
        border-radius: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 13px;
    }

    .btn-back {
        background: #6b7280;
        color: white;
    }

    .btn-edit {
        background: #f59e0b;
        color: white;
    }

    .btn-print {
        background: #2563eb;
        color: white;
    }

    .action-group {
        display: flex;
        gap: 8px;
    }

    .document {
        background: white;
        border: 1px solid #d1d5db;
        padding: 35px 40px;
    }

    .header {
        text-align: center;
        margin-bottom: 25px;
    }

    .header-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .header-subtitle {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .header-year {
        font-size: 14px;
        font-weight: bold;
    }

    /* DETAIL LAPORAN */

    .section-title {
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 8px;
    }

    .detail-list {
        margin-bottom: 20px;
    }

    .detail-row {
        display: flex;
        padding: 4px 0;
        line-height: 1.5;
    }

    .detail-label {
        width: 180px;
        flex-shrink: 0;
    }

    .detail-separator {
        width: 15px;
        flex-shrink: 0;
    }

    .detail-value {
        flex: 1;
    }

    /* RINGKASAN LAPORAN */

    .summary-list {
        margin-top: 5px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding: 7px 0 4px 0;
        border-bottom: 1px solid #000;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .summary-label {
        font-weight: normal;
        padding-right: 20px;
    }

    .summary-value {
        text-align: right;
        font-weight: normal;
        white-space: nowrap;
    }

    /* PERNYATAAN */

    .legal {
        margin-top: 20px;
        text-align: justify;
        line-height: 1.6;
    }

    /* TANDA TANGAN */

    .signature-location {
    text-align: right;
    margin-top: 40px;
    margin-bottom: 8px;
    font-size: 13px;
}

.signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
}

.signature-table td {
    width: 33.33%;
    text-align: center;
    vertical-align: top;
    padding: 5px;
}

.signature-title {
    height: 45px;
}

.signature-space {
    height: 95px;
}

.signature-name {
    font-weight: bold;
    text-decoration: underline;
}

.signature-number {
    margin-top: 4px;
}

    .empty {
        color: #6b7280;
        font-style: italic;
    }

    @media print {

        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            padding: 0;
            background: white;
            font-size: 11px;
        }

        .top-actions {
            display: none;
        }

        .container {
            max-width: none;
        }

        .document {
            border: none;
            padding: 0;
        }

        .header-title {
            font-size: 16px;
        }

        .header-subtitle {
            font-size: 14px;
        }

        .header-year {
            font-size: 12px;
        }

        .detail-row {
            padding: 3px 0;
        }

        .summary-row {
            padding: 5px 0 2px 0;
            margin-bottom: 7px;
        }

        .signature-table {
            margin-top: 35px;
        }

        .signature-space {
            height: 75px;
        }
    }
</style>

</head>

<body>

<div class="container">

<div class="top-actions">

    <a href="{{ route('lpdhs.index') }}"
       class="btn btn-back">
        ← Kembali
    </a>

    <div class="action-group">

        <a href="{{ route('lpdhs.edit', $lpdh) }}"
           class="btn btn-edit">
            Edit
        </a>

        <button onclick="window.print()"
                class="btn btn-print">
            🖨 Cetak LPDH
        </button>

    </div>

</div>


<div class="document">

    <!-- HEADER -->

    <div class="header">

        <div class="header-title">
            LEMBAR PENGESAHAN
        </div>

        <div class="header-subtitle">
            LAPORAN PENGGUNAAN DANA HARIAN (LPDH) SPPG
        </div>

        <div class="header-year">
            PROGRAM MAKAN BERGIZI GRATIS TAHUN ANGGARAN 2026
        </div>

    </div>


    <!-- HITUNG HPE OTOMATIS -->

    @php
        $hpeStartDate = \Carbon\Carbon::create(2026, 10, 5)->startOfDay();
        $serviceDate = \Carbon\Carbon::parse($lpdh->service_date)->startOfDay();

        if ($serviceDate->lt($hpeStartDate)) {
            $hpeStatus = 'Belum HPE';
        } else {
            $hpeNumber = $hpeStartDate->diffInDays($serviceDate) + 1;
            $hpeStatus = 'HPE ke-' . $hpeNumber;
        }
    @endphp


    <!-- DETAIL LAPORAN -->

    <div class="detail-list">

        <div class="detail-row">
            <div class="detail-label">
                ID SPPG
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->kitchen->id_sppg }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Nama SPPG
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->kitchen->name }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Kabupaten/Kota
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->kitchen->kabupaten_kota }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Provinsi
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->kitchen->provinsi }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Mitra/Yayasan
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->kitchen->foundation_name ?: '-' }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Tanggal Pelayanan
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->service_date->translatedFormat('d F Y') }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Status Hari
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $hpeStatus }}
            </div>
        </div>


        <div class="detail-row">
            <div class="detail-label">
                Nama Berkas
            </div>

            <div class="detail-separator">
                :
            </div>

            <div class="detail-value">
                {{ $lpdh->file_name ?: 'LPDH-' . $lpdh->service_date->format('Ymd') }}
            </div>
        </div>

    </div>


    <!-- RINGKASAN LAPORAN -->

    <div class="section-title">
        RINGKASAN LAPORAN
    </div>

    <div class="summary-list">

        <div class="summary-row">
            <div class="summary-label">
                Jumlah Penerima Manfaat
            </div>

            <div class="summary-value">
                {{ number_format($lpdh->beneficiaries_count, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Belanja Bahan Baku Pangan
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->raw_material_expenses, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Biaya Operasional
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->operational_expenses, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Insentif Dihitung
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->incentive_calculated, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Insentif Dibayarkan ke Mitra/Yayasan
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->incentive_paid, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Saldo Akhir VA
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->va_final_balance, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Usulan Top Up
            </div>

            <div class="summary-value">
                Rp {{ number_format($lpdh->topup_proposal, 0, ',', '.') }}
            </div>
        </div>


        <div class="summary-row">
            <div class="summary-label">
                Hasil Daftar Periksa
            </div>

            <div class="summary-value">
                {{ $lpdh->inspection_result ?: '-' }}
            </div>
        </div>

    </div>


    <!-- PERNYATAAN -->

    <div class="legal">

        Kami yang bertanda tangan di bawah ini menyatakan bahwa data dan angka dalam Laporan Penggunaan Dana Harian ini
        benar, sesuai dengan kondisi rill, dan didukung bukti autentik yang sah. Apabila di kemudian hari ditemukan ketidaksesuaian,
        kami bersedia mempertanggungjawabkannya sesuai ketentuan peraturan perundang-undangan.

    </div>

    <!-- TANDA TANGAN -->

<div class="signature-location">
    {{ $lpdh->kitchen->kabupaten_kota ?: '........................' }},
    {{ $lpdh->service_date->translatedFormat('d F Y') }}
</div>

<table class="signature-table">

    <tr>

        <td>

            <div class="signature-title">
                Disusun oleh,<br>
                Pengawas Keuangan SPPG
            </div>

            <div class="signature-space"></div>

            <div class="signature-name">
                {{ $lpdh->kitchen->finance_officer_name ?: '........................................' }}
            </div>

            <div class="signature-number">
                NIK:
                {{ $lpdh->kitchen->finance_officer_nik ?: '........................................' }}
            </div>

        </td>


        <td>

            <div class="signature-title">
                Mengetahui,<br>
                Kepala SPPG
            </div>

            <div class="signature-space"></div>

            <div class="signature-name">
                {{ $lpdh->kitchen->head_sppg_name ?: '........................................' }}
            </div>

            <div class="signature-number">
                NIP:
                {{ $lpdh->kitchen->head_sppg_nip ?: '........................................' }}
            </div>

        </td>


        <td>

            <div class="signature-title">
                Menyetujui,<br>
                Perwakilan Mitra/Yayasan
            </div>

            <div class="signature-space"></div>

            <div class="signature-name">
                {{ $lpdh->kitchen->foundation_rep_name ?: '........................................' }}
            </div>

            <div class="signature-number">
                NIK:
                {{ $lpdh->kitchen->foundation_rep_nik ?: '........................................' }}
            </div>

        </td>

    </tr>

</table>

</div>

</div>

</body>
</html>
