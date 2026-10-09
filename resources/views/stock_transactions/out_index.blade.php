<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar (OUT)</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            font-family: inherit;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5c636a;
        }

        .filter-box {
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filter-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 15px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr 1fr;
            gap: 12px;
            align-items: end;
        }

        .form-group {
            min-width: 0;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            height: 39px;
            padding: 8px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background: #fff;
            font-size: 13px;
            color: #333;
        }

        .form-control:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .15);
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .btn-filter {
            background: #0d6efd;
            color: #fff;
        }

        .btn-filter:hover {
            background: #0b5ed7;
        }

        .btn-reset {
            background: #6c757d;
            color: #fff;
        }

        .btn-reset:hover {
            background: #5c636a;
        }

        .result-info {
            font-size: 13px;
            color: #666;
            margin: 15px 0;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
        }

        .out-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 25px;
            overflow: hidden;
            background: #fff;
        }

        .out-header {
            background: #f1f3f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .out-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .out-header td {
            padding: 7px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
        }

        .detail-table th {
            background: #f8f9fa;
            color: #495057;
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .total {
            font-weight: bold;
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-out {
            background: #f8d7da;
            color: #842029;
        }

        .empty {
            text-align: center;
            padding: 40px 15px;
            color: #777;
        }

        .action-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-wrapper form {
            margin: 0;
        }

        .btn-edit {
            background: #ffc107;
            color: #212529;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #bb2d3b;
        }

        .btn-invoice {
            background: #fd7e14;
            color: white;
        }

        .btn-invoice:hover {
            background: #e96b02;
        }

        .btn-invoice-view {
            background: #0d6efd;
            color: white;
        }

        .btn-invoice-view:hover {
            background: #0b5ed7;
        }

        .btn-nota {
            background: #6f42c1;
            color: white;
        }

        .btn-nota:hover {
            background: #59359a;
        }

        .btn-nota-view {
            background: #198754;
            color: white;
        }

        .btn-nota-view:hover {
            background: #157347;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 25px;
        }

        .pagination-info {
            color: #666;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            body {
                margin: 10px;
            }

            .container {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            h1 {
                font-size: 21px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .out-header {
                overflow-x: auto;
            }

            .out-header table {
                min-width: 650px;
            }

            .pagination-wrapper {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<div class="container">

    {{-- HEADER --}}
    <div class="header">
        <h1>Barang Keluar (OUT)</h1>

        <a href="{{ route('purchase-orders.index') }}" class="btn btn-back">
            ← Kembali
        </a>
    </div>

    {{-- NOTIFIKASI --}}
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

    {{-- FILTER --}}
    <div class="filter-box">
        <h2 class="filter-title">Cari dan Filter Barang Keluar</h2>

        <form method="GET" action="{{ route('stock-transactions.out') }}">
            <div class="filter-grid">

                {{-- PENCARIAN --}}
                <div class="form-group">
                    <label for="search">Pencarian</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Nomor OUT, nomor PO, kode SPPG, nama dapur..."
                        value="{{ request('search') }}"
                    >
                </div>

                {{-- FILTER DAPUR --}}
                <div class="form-group">
                    <label for="kitchen_id">Dapur / SPPG</label>

                    <select
                        name="kitchen_id"
                        id="kitchen_id"
                        class="form-control"
                    >
                        <option value="">Semua Dapur / SPPG</option>

                        @foreach ($kitchens as $kitchen)
                            <option
                                value="{{ $kitchen->id }}"
                                {{ (string) request('kitchen_id') === (string) $kitchen->id ? 'selected' : '' }}
                            >
                                {{ $kitchen->id_sppg ?? '-' }} - {{ $kitchen->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TANGGAL MULAI --}}
                <div class="form-group">
                    <label for="date_from">Tanggal Mulai</label>
                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        class="form-control"
                        value="{{ request('date_from') }}"
                    >
                </div>

                {{-- TANGGAL SELESAI --}}
                <div class="form-group">
                    <label for="date_to">Tanggal Selesai</label>
                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        class="form-control"
                        value="{{ request('date_to') }}"
                    >
                </div>

            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-filter">
                    🔎 Terapkan Filter
                </button>

                <a
                    href="{{ route('stock-transactions.out') }}"
                    class="btn btn-reset"
                >
                    ↻ Reset Filter
                </a>
            </div>
        </form>
    </div>

    {{-- INFORMASI HASIL --}}
    <div class="result-info">
        @if ($transactions->total() > 0)
            Menampilkan {{ $transactions->firstItem() }}
            - {{ $transactions->lastItem() }}
            dari {{ $transactions->total() }} transaksi Barang Keluar.
        @else
            Tidak ada transaksi yang sesuai dengan filter.
        @endif
    </div>

    {{-- DAFTAR TRANSAKSI --}}
    @forelse ($transactions as $transaction)
        <div class="out-card">

            {{-- INFORMASI TRANSAKSI --}}
            <div class="out-header">
                <table>
                    <tbody>
                        <tr>
                            <td class="label">No. OUT</td>
                            <td>
                                <strong>
                                    {{ $transaction->transaction_number }}
                                </strong>
                            </td>

                            <td class="label">Tanggal</td>
                            <td>
                                {{ $transaction->transaction_date
                                    ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y')
                                    : '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">No. PO</td>
                            <td>
                                {{ $transaction->purchaseOrder?->po_number ?? '-' }}
                            </td>

                            <td class="label">Status</td>
                            <td>
                                <span class="badge badge-out">
                                    BARANG KELUAR
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="label">SPPG / Dapur</td>
                            <td colspan="3">
                                {{ $transaction->kitchen?->id_sppg ?? '-' }}
                                - {{ $transaction->kitchen?->name ?? '-' }}
                            </td>
                        </tr>

                        {{-- AKSI --}}
                        <tr>
                            <td class="label">Aksi</td>
                            <td colspan="3">
                                <div class="action-wrapper">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('stock-transactions.edit', $transaction->id) }}"
                                        class="btn btn-edit"
                                    >
                                        ✏️ Edit Barang Keluar
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        action="{{ route('stock-transactions.destroy', $transaction->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus transaksi {{ $transaction->transaction_number }}? Pastikan dampak penghapusan terhadap stok, invoice, dan nota sudah diperiksa.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-delete">
                                            🗑️ Hapus
                                        </button>
                                    </form>

                                    {{-- INVOICE --}}
                                    @if ($transaction->invoice)
                                        <a
                                            href="{{ route('invoices.show', $transaction->invoice->id) }}"
                                            class="btn btn-invoice-view"
                                        >
                                            👁 Lihat Invoice
                                        </a>
                                    @else
                                        <form
                                            action="{{ route('stock-transactions.create-invoice', $transaction->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Buat Invoice otomatis dari transaksi OUT ini?');"
                                        >
                                            @csrf

                                            <button type="submit" class="btn btn-invoice">
                                                🧾 Buat Invoice
                                            </button>
                                        </form>
                                    @endif

                                    {{-- NOTA KELUAR --}}
                                    @if ($transaction->notaKeluars->isNotEmpty())
                                        @foreach ($transaction->notaKeluars as $nota)
                                            <a
                                                href="{{ route('nota-keluars.show', $nota->id) }}"
                                                class="btn btn-nota-view"
                                            >
                                                👁 Lihat Nota
                                                @if ($nota->supplier?->name)
                                                    - {{ $nota->supplier->name }}
                                                @endif
                                            </a>
                                        @endforeach
                                    @else
                                        <form
                                            action="{{ route('stock-transactions.create-nota', $transaction->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Buat Nota Keluar berdasarkan supplier dari transaksi OUT ini?');"
                                        >
                                            @csrf

                                            <button type="submit" class="btn btn-nota">
                                                📝 Buat Nota
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>

                        @if ($transaction->notes)
                            <tr>
                                <td class="label">Catatan</td>
                                <td colspan="3">
                                    {{ $transaction->notes }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- DETAIL BARANG --}}
            <div class="table-wrapper">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th width="100">Qty</th>
                            <th width="100">Satuan</th>
                            <th width="140">Harga</th>
                            <th width="160">Jumlah</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($transaction->details as $index => $detail)
                            <tr>
                                <td class="text-center">
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    {{ $detail->item?->code ?? '-' }}
                                </td>

                                <td>
                                    {{ $detail->item?->name ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ rtrim(rtrim(number_format((float) $detail->quantity, 2, ',', '.'), '0'), ',') }}
                                </td>

                                <td class="text-center">
                                    {{ $detail->unit ?? '-' }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty">
                                    Tidak ada detail barang.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="total">
                            <td colspan="6" class="text-right">
                                Total
                            </td>

                            <td class="text-right">
                                Rp {{ number_format((float) $transaction->details->sum('subtotal'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    @empty
        <div class="empty">
            Belum ada transaksi Barang Keluar (OUT) yang sesuai dengan pencarian atau filter.
        </div>
    @endforelse

    {{-- PAGINATION --}}
    @if ($transactions->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Halaman {{ $transactions->currentPage() }}
                dari {{ $transactions->lastPage() }}
            </div>

            <div>
                {{ $transactions->onEachSide(1)->links() }}
            </div>
        </div>
    @endif

</div>
</body>
</html>
