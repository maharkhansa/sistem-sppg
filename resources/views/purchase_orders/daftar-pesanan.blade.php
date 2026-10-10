<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Daftar Pesanan MBG</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            background: #f2f2f2;
        }

        .page {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }

        /* KOP SURAT */

        .kop {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .kop-kiri {
            flex: 1;
            min-width: 0;
        }

        .judul-utama {
            margin: 0 0 5px;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        .nama-koperasi {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 800;
        }

        .nama-sppg {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .alamat-koperasi {
            margin: 0;
            font-size: 9px;
            line-height: 1.4;
        }

        .kop-kanan {
            width: 100px;
            flex-shrink: 0;
            text-align: center;
        }

        .logo-koperasi {
            display: block;
            width: 85px;
            height: 85px;
            margin: 0 auto;
            object-fit: contain;
        }

        /* TANGGAL PESANAN */

        .tanggal-pesanan {
            margin: 10px 0 12px;
            font-size: 10px;
            font-weight: bold;
        }

        
        /* TABEL PESANAN */

        .tabel-pesanan {
            width: 100%;
            margin: 0 0 10px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .tabel-pesanan th,
        .tabel-pesanan td {
            border: 1px solid #555;
            padding: 4px 3px;
            font-size: 9px;
            line-height: 1.2;
            overflow-wrap: break-word;
            vertical-align: middle;
        }

        .tabel-pesanan thead th {
            background: #ffeb3b;
            color: #222;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        /* JUDUL KATEGORI DI TENGAH */

        .judul-kategori th {
            background: #ffeb3b;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            padding: 5px 3px;
            letter-spacing: 0.3px;
        }

        /* LEBAR KOLOM DIPERSEMPIT */

        .kolom-no {
            width: 5%;
            text-align: center;
        }

        .kolom-cek {
            width: 7%;
            text-align: center;
        }

        .kolom-nama {
            width: 43%;
            text-align: left;
        }

        .kolom-jumlah {
            width: 16%;
            text-align: center;
        }

        .kolom-satuan {
            width: 10%;
            text-align: center;
        }

        .kolom-status {
            width: 21%;
            text-align: center;
        }

        /* ISI KOLOM */

        .nama-barang {
            text-align: left;
        }

        .angka {
            text-align: center;
            white-space: nowrap;
        }

        .tengah {
            text-align: center;
        }

        .checkbox-kecil {
            width: 11px;
            height: 11px;
            margin: 0;
            vertical-align: middle;
            cursor: pointer;
            accent-color: #198754;
        }

        .status-pilihan {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            font-size: 8px;
        }

        .status-pilihan label {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            cursor: pointer;
        }

        .status-pilihan input {
            width: 10px;
            height: 10px;
            margin: 0;
            accent-color: #198754;
        }

        .baris-kosong td {
            height: 25px;
            color: #777;
            text-align: center;
        }

        /* JUDUL KATEGORI DI TENGAH */

        .judul-kategori th {
            background: #ffeb3b;
            font-size: 11px;
            font-weight: 800;
            text-align: center;
            padding: 6px 4px;
            letter-spacing: 0.3px;
        }

        .kolom-no {
            width: 7%;
            text-align: center;
        }

        .kolom-cek {
            width: 8%;
            text-align: center;
        }

        .kolom-nama {
            width: 35%;
        }

        .kolom-jumlah {
            width: 19%;
            text-align: center;
        }

        .kolom-satuan {
            width: 11%;
            text-align: center;
        }

        .kolom-status {
            width: 20%;
            text-align: center;
        }

        .nama-barang {
            text-align: left;
        }

        .angka,
        .tengah {
            text-align: center;
            vertical-align: middle;
        }

        .angka {
            white-space: nowrap;
        }

        .checkbox-kecil {
            width: 12px;
            height: 12px;
            margin: 0;
            vertical-align: middle;
            cursor: pointer;
            accent-color: #198754;
        }

        .status-pilihan {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            font-size: 8px;
        }

        .status-pilihan label {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            cursor: pointer;
        }

        .status-pilihan input {
            width: 10px;
            height: 10px;
            margin: 0;
            accent-color: #198754;
        }

        .baris-kosong td {
            height: 25px;
            color: #777;
            text-align: center;
        }

        /* TOMBOL LAYAR */

        .aksi {
            max-width: 800px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .tombol {
            display: inline-block;
            padding: 8px 13px;
            border: 0;
            border-radius: 4px;
            background: #198754;
            color: white;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }

        .tombol-kembali {
            background: #6c757d;
        }

        .catatan {
            margin-top: 5px;
            font-size: 9px;
            color: #555;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                max-width: none;
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .aksi,
            .no-print {
                display: none !important;
            }

            .kop,
            .tabel-pesanan,
            .tabel-pesanan tr {
                break-inside: avoid;
            }

            .tabel-pesanan th,
            .tabel-pesanan td {
                padding: 4px 3px;
                font-size: 8.5px;
            }

            .judul-kategori th {
                font-size: 10px;
            }

            input[type="checkbox"] {
                print-color-adjust: exact;
            }
        }

        @media screen and (max-width: 600px) {
            body {
                padding: 8px;
            }

            .page {
                padding: 10px;
                overflow-x: auto;
            }

            .judul-utama {
                font-size: 15px;
            }

            .nama-koperasi {
                font-size: 10px;
            }

            .kop-kanan {
                width: 70px;
            }

            .logo-koperasi {
                width: 65px;
                height: 65px;
            }

            .tabel-pesanan {
                min-width: 520px;
            }
        }
    </style>
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | Mengelompokkan detail PO ke dalam empat kategori tetap
    |--------------------------------------------------------------------------
    */

    $details = $purchaseOrder->details;

    $kategoriPesanan = [
        'Bahan Baku' => collect(),
        'Sayur' => collect(),
        'Buah' => collect(),
        'Operasional' => collect(),
    ];

    foreach ($details as $detail) {
        $namaSupplier = strtoupper(
            trim((string) ($detail->supplier?->name ?? ''))
        );

        $namaKategori = strtoupper(
            trim((string) ($detail->item?->category?->name ?? ''))
        );

        /*
         * Kategori utama berdasarkan supplier.
         * Barang dari supplier yang tidak dikenali akan
         * dicoba dikelompokkan berdasarkan kategori barang.
         */

        if (
            str_contains($namaSupplier, 'SUMBER REJEKI') ||
            str_contains($namaSupplier, 'KOPERASI')
        ) {
            $kategori = 'Bahan Baku';
        } elseif (
            str_contains($namaSupplier, 'ZENZI') ||
            str_contains($namaSupplier, 'ZENZIE')
        ) {
            $kategori = 'Sayur';
        } elseif (str_contains($namaSupplier, 'GEMILANG')) {
            $kategori = 'Buah';
        } elseif (
            str_contains($namaSupplier, 'TOP FAST') ||
            str_contains($namaSupplier, 'TOPFAST')
        ) {
            $kategori = 'Operasional';
        } elseif (
            str_contains($namaKategori, 'BAHAN BAKU') ||
            str_contains($namaKategori, 'BAHAN KERING') ||
            str_contains($namaKategori, 'BAHAN POKOK')
        ) {
            $kategori = 'Bahan Baku';
        } elseif (str_contains($namaKategori, 'SAYUR')) {
            $kategori = 'Sayur';
        } elseif (str_contains($namaKategori, 'BUAH')) {
            $kategori = 'Buah';
        } elseif (str_contains($namaKategori, 'OPERASIONAL')) {
            $kategori = 'Operasional';
        } else {
            /*
             * Barang dari supplier/kategori yang tidak dikenal
             * tidak dimasukkan ke kategori yang salah.
             */
            continue;
        }

        $kategoriPesanan[$kategori]->push($detail);
    }

    $tanggalPesanan = $purchaseOrder->po_date
        ? \Carbon\Carbon::parse($purchaseOrder->po_date)
            ->locale('id')
            ->translatedFormat('l, d/m/Y')
        : \Carbon\Carbon::parse($purchaseOrder->created_at)
            ->locale('id')
            ->translatedFormat('l, d/m/Y');

    $namaSppg = $purchaseOrder->kitchen?->name ?? '[Nama SPPG]';
@endphp

<div class="aksi no-print">
    <a
        href="{{ route('purchase-orders.index') }}"
        class="tombol tombol-kembali"
    >
        ← Kembali ke Daftar PO
    </a>

    <button
        type="button"
        class="tombol"
        onclick="window.print()"
    >
        🖨 Cetak Daftar Pesanan
    </button>
</div>

<div class="page">

    {{-- KOP DOKUMEN --}}

    <div class="kop">

        <div class="kop-kiri">
            <h1 class="judul-utama">
                DAFTAR PESANAN MBG
            </h1>

            <p class="nama-koperasi">
                KOPERASI SUMBER REJEKI NUSANTARA
            </p>

            <p class="nama-sppg">
                {{ $namaSppg }}
            </p>

            <p class="alamat-koperasi">
                Jln. Mayor Unus, Honggosari, Jogonegoro,
                Mertoyudan, Magelang
            </p>
        </div>

        <div class="kop-kanan">
            <img
                src="{{ asset('storage/kop-supplier/sumber-rejeki.jpeg') }}"
                alt="Logo Koperasi Sumber Rejeki Nusantara"
                class="logo-koperasi"
                onerror="this.style.display='none';"
            >
        </div>

    </div>

    {{-- TANGGAL PESANAN --}}

    <div class="tanggal-pesanan">
        Tanggal Pesanan : {{ $tanggalPesanan }}
    </div>

    {{-- TABEL PER KATEGORI --}}

    @foreach ($kategoriPesanan as $namaKategori => $items)

        <table class="tabel-pesanan">

            <colgroup>
                <col class="kolom-no">
                <col class="kolom-cek">
                <col class="kolom-nama">
                <col class="kolom-jumlah">
                <col class="kolom-satuan">
                <col class="kolom-status">
            </colgroup>

            <thead>

                {{-- JUDUL KATEGORI DI TENGAH --}}

                <tr class="judul-kategori">
                    <th colspan="6">
                        {{ strtoupper($namaKategori) }}
                    </th>
                </tr>

                {{-- HEADER KOLOM WARNA KUNING --}}

                <tr>
                    <th>No.</th>
                    <th>Cek</th>
                    <th>Nama Barang</th>
                    <th>Total Kebutuhan</th>
                    <th>Satuan</th>
                    <th>Ketersediaan</th>
                </tr>

            </thead>

            <tbody>

                @forelse ($items as $index => $detail)

                    <tr>
                        <td class="tengah">
                            {{ $index + 1 }}
                        </td>

                        <td class="tengah">
                            <input
                                type="checkbox"
                                class="checkbox-kecil"
                                aria-label="Tandai barang {{ $detail->item?->name ?? '' }}"
                            >
                        </td>

                        <td class="nama-barang">
                            {{ $detail->item?->name ?? 'Barang tidak ditemukan' }}
                        </td>

                        <td class="angka">
                            {{ number_format((float) $detail->quantity, 0, ',', '.') }}
                        </td>

                        <td class="tengah">
                            {{ $detail->unit ?? '-' }}
                        </td>

                        <td>
                            <div class="status-pilihan">

                                <label>
                                    <input
                                        type="checkbox"
                                        class="status-checkbox"
                                        aria-label="Sudah tersedia"
                                    >
                                    Sudah
                                </label>

                                <label>
                                    <input
                                        type="checkbox"
                                        class="status-checkbox"
                                        aria-label="Belum tersedia"
                                    >
                                    Belum
                                </label>

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr class="baris-kosong">
                        <td colspan="6">
                            Tidak ada barang pada kategori ini.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    @endforeach

    <p class="catatan">
        Catatan: Ceklis digunakan untuk pemeriksaan pesanan dan
        ketersediaan barang saat persiapan distribusi.
    </p>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /*
     * Pada setiap baris, pilihan Sudah dan Belum saling eksklusif.
     * Ceklis hanya untuk pemeriksaan di halaman/cetakan ini.
     */

    document.querySelectorAll('.status-pilihan').forEach(function (group) {
        const checkboxes = group.querySelectorAll('.status-checkbox');

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    checkboxes.forEach(function (other) {
                        if (other !== checkbox) {
                            other.checked = false;
                        }
                    });
                }
            });
        });
    });
});
</script>

</body>
</html>