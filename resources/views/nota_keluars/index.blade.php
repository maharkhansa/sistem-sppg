<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Nota Keluar</title>

    <style>

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
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
            background: #6c757d;
            color: white;
        }

        .btn-view {
            background: #198754;
            color: white;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
        }

        th {
            background: #f1f3f5;
            text-align: center;
        }

        td {
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-draft {
            background: #fff3cd;
            color: #664d03;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>📝 Nota Keluar</h1>

        <a
            href="{{ route('stock-transactions.out') }}"
            class="btn btn-back"
        >
            ← Kembali ke Barang Keluar
        </a>

    </div>


    @if (session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if (session('error'))

        <div class="alert alert-error">
            {{ session('error') }}
        </div>

    @endif


    <div class="card">

        <table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>Nomor Nota</th>

                    <th>Tanggal</th>

                    <th>SPPG / Dapur</th>

                    <th>Nomor OUT</th>

                    <th>Total</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($notaKeluars as $index => $nota)

                    <tr>

                        <td class="text-center">
                            {{ $index + 1 }}
                        </td>

                        <td>
                            {{ $nota->nota_number }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse(
                                $nota->nota_date
                            )->translatedFormat('d F Y') }}
                        </td>

                        <td>
                            {{ $nota->kitchen?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $nota->stockTransaction?->transaction_number ?? '-' }}
                        </td>

                        <td class="text-right">

                            Rp
                            {{ number_format(
                                $nota->total_amount,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>

                        <td class="text-center">

                            @if ($nota->status === 'APPROVED')

                                <span class="status status-approved">
                                    APPROVED
                                </span>

                            @else

                                <span class="status status-draft">
                                    {{ $nota->status }}
                                </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a
                                href="{{ route(
                                    'nota-keluars.show',
                                    $nota->id
                                ) }}"
                                class="btn btn-view"
                            >
                                👁 Lihat
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="text-center"
                        >
                            Belum ada Nota Keluar.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>

</html>