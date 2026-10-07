<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LPDH - SPPG</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111827;
            font-size: 13px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title h1 {
            margin: 0;
            font-size: 20px;
        }

        .page-title p {
            margin: 5px 0 0;
            color: #6b7280;
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

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-info {
            background: #0891b2;
            color: white;
        }

        .btn-warning {
            background: #d97706;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .alert {
            padding: 10px 14px;
            margin-bottom: 15px;
            border-radius: 5px;
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

        .card {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: bold;
            font-size: 14px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 8px;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap;
        }

        td {
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .sppg-name {
            font-weight: bold;
        }

        .sppg-id {
            display: block;
            color: #6b7280;
            font-size: 11px;
            margin-top: 3px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .badge-info {
            background: #cffafe;
            color: #155e75;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .action-buttons .btn {
            padding: 6px 9px;
            font-size: 11px;
        }

        .empty {
            padding: 50px 20px;
            text-align: center;
            color: #6b7280;
        }

        .empty-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #374151;
        }

        @media print {
            .top-actions,
            .alert,
            .action-column {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .card {
                border: none;
            }
        }
    </style>
</head>

<body>

<div class="container">

    {{-- HEADER --}}
    <div class="top-actions">

        <div class="page-title">
            <h1>LPDH</h1>
            <p>Laporan Penggunaan Dana Harian SPPG</p>
        </div>

        <a href="{{ route('lpdhs.create') }}"
           class="btn btn-primary">
            + Buat LPDH
        </a>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- ERROR --}}
    @if(session('error'))

        <div class="alert alert-error">
            {{ session('error') }}
        </div>

    @endif


    {{-- VALIDATION ERROR --}}
    @if($errors->any())

        <div class="alert alert-error">

            <strong>Terjadi kesalahan:</strong>

            <ul style="margin-bottom: 0;">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- DATA LPDH --}}
    <div class="card">

        <div class="card-header">
            Daftar Laporan Penggunaan Dana Harian
        </div>


        @if($lpdhs->count())

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th style="width: 45px;">
                                No
                            </th>

                            <th>
                                SPPG
                            </th>

                            <th>
                                Tanggal Pelayanan
                            </th>

                            <th>
                                Status Hari
                            </th>

                            <th>
                                Penerima Manfaat
                            </th>

                            <th>
                                Belanja Bahan Baku
                            </th>

                            <th>
                                Operasional
                            </th>

                            <th>
                                Saldo Akhir VA
                            </th>

                            <th class="action-column"
                                style="width: 150px;">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($lpdhs as $lpdh)

                            <tr>

                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>


                                {{-- SPPG --}}
                                <td>

                                    <span class="sppg-name">
                                        {{ $lpdh->kitchen?->name ?? '-' }}
                                    </span>

                                    <span class="sppg-id">
                                        ID SPPG:
                                        {{ $lpdh->kitchen?->id_sppg ?? '-' }}
                                    </span>

                                </td>


                                {{-- TANGGAL --}}
                                <td class="text-center">

                                    {{ $lpdh->service_date?->format('d/m/Y') ?? '-' }}

                                </td>


                                {{-- STATUS --}}
                                <td class="text-center">

                                    @if($lpdh->day_status === 'Hari Pelayanan')

                                        <span class="badge badge-success">
                                            Hari Pelayanan
                                        </span>

                                    @elseif($lpdh->day_status === 'Hari Libur')

                                        <span class="badge badge-secondary">
                                            Hari Libur
                                        </span>

                                    @else

                                        <span class="badge badge-info">
                                            {{ $lpdh->day_status ?? '-' }}
                                        </span>

                                    @endif

                                </td>


                                {{-- PENERIMA --}}
                                <td class="text-right">

                                    {{ number_format(
                                        $lpdh->beneficiaries_count,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                {{-- BAHAN BAKU --}}
                                <td class="text-right">

                                    Rp
                                    {{ number_format(
                                        $lpdh->raw_material_expenses,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                {{-- OPERASIONAL --}}
                                <td class="text-right">

                                    Rp
                                    {{ number_format(
                                        $lpdh->operational_expenses,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                {{-- SALDO VA --}}
                                <td class="text-right">

                                    Rp
                                    {{ number_format(
                                        $lpdh->va_final_balance,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                {{-- AKSI --}}
                                <td class="action-column">

                                    <div class="action-buttons">

                                        <a href="{{ route(
                                            'lpdhs.show',
                                            $lpdh->id
                                        ) }}"
                                           class="btn btn-info"
                                           title="Lihat">

                                            Lihat

                                        </a>


                                        <a href="{{ route(
                                            'lpdhs.edit',
                                            $lpdh->id
                                        ) }}"
                                           class="btn btn-warning"
                                           title="Edit">

                                            Edit

                                        </a>


                                        <form action="{{ route(
                                            'lpdhs.destroy',
                                            $lpdh->id
                                        ) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm(
                                                  'Yakin ingin menghapus LPDH ini?'
                                              );">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger">

                                                Hapus

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">

                <div class="empty-title">
                    Belum Ada LPDH
                </div>

                <div>
                    Belum terdapat laporan penggunaan dana harian.
                </div>

                <br>

                <a href="{{ route('lpdhs.create') }}"
                   class="btn btn-primary">

                    + Buat LPDH Pertama

                </a>

            </div>

        @endif

    </div>

</div>

</body>
</html>