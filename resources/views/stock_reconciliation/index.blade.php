@extends('layouts.app')

@section('title', 'Rekonsiliasi Stok')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Rekonsiliasi Stok</h1>
            <div class="text-muted small">
                Pemeriksaan saldo berdasarkan histori transaksi dan stok sistem.
            </div>
        </div>

        <button type="button" class="btn btn-outline-secondary"
            onclick="window.print()">
            Cetak Laporan
        </button>
    </div>

    <div class="alert alert-info">
        <strong>Informasi rekonsiliasi.</strong>
        Perhitungan saldo hanya mencakup barang yang menggunakan pelacakan stok.
        Saldo histori dihitung dari total Barang Masuk dikurangi Barang Keluar.
        Laporan ini tidak mengubah transaksi atau stok. Saldo histori belum
        memperhitungkan saldo awal yang tidak tercatat dalam riwayat transaksi.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Barang Dilacak Stoknya</div>
                    <div class="fs-3 fw-bold">
                        {{ number_format($summary['total_items']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Barang dengan Record Stok</div>
                    <div class="fs-3 fw-bold">
                        {{ number_format($summary['stock_items']) }}
                    </div>
                    @if ($summary['missing_stock_record'] > 0)
                        <div class="small text-warning mt-1">
                            {{ number_format($summary['missing_stock_record']) }}
                            barang belum memiliki record stok.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Saldo Berbeda</div>
                    <div class="fs-3 fw-bold
                        {{ $summary['mismatches'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($summary['mismatches']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Saldo Histori Negatif</div>
                    <div class="fs-3 fw-bold
                        {{ $summary['negative_simulation'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($summary['negative_simulation']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Filter Detail Transaksi</strong>
        </div>

        <div class="card-body">
            <form method="GET"
                action="{{ route('stock-reconciliation.index') }}"
                class="row g-3">

                <div class="col-md-4">
                    <label for="search" class="form-label">
                        Cari transaksi atau barang
                    </label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Nomor transaksi, kode, nama, vendor">
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Jenis</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Semua</option>
                        <option value="IN" @selected(request('type') === 'IN')>
                            Barang Masuk
                        </option>
                        <option value="OUT" @selected(request('type') === 'OUT')>
                            Barang Keluar
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">Dari tanggal</label>
                    <input
                        id="date_from"
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">Sampai tanggal</label>
                    <input
                        id="date_to"
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" type="submit">
                        Filter
                    </button>
                    <a href="{{ route('stock-reconciliation.index') }}"
                        class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <strong>Perbandingan Saldo dan Histori</strong>
            <span class="small text-muted">
                Barang dengan pelacakan stok aktif
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th class="text-end">Total Masuk</th>
                        <th class="text-end">Total Keluar</th>
                        <th class="text-end">Saldo Histori</th>
                        <th class="text-end">Saldo Saat Ini</th>
                        <th class="text-end">Selisih</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($stockRows as $row)
                        <tr>
                            <td>{{ $row->item_code }}</td>
                            <td>{{ $row->item_name }}</td>
                            <td>{{ $row->unit }}</td>

                            <td class="text-end">
                                {{ number_format($row->total_in, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->total_out, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->expected_stock, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->current_stock, 2, ',', '.') }}
                            </td>

                            <td class="text-end fw-bold
                                {{ abs($row->difference) >= 0.00001 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($row->difference, 2, ',', '.') }}
                            </td>

                            <td>
                                @if ($row->status === 'Sesuai Histori')
                                    <span class="badge bg-success">Sesuai Histori</span>
                                @elseif ($row->status === 'Record Stok Tidak Ada')
                                    <span class="badge bg-warning text-dark">
                                        Record Stok Tidak Ada
                                    </span>
                                @else
                                    <span class="badge bg-danger">Saldo Berbeda</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                Belum ada barang yang menggunakan pelacakan stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Perbandingan Saldo Histori</strong>
        </div>

        <div class="card-body">
            <p class="small text-muted">
                Simulasi ini menggunakan seluruh transaksi IN dan OUT yang
                tersimpan untuk barang dengan pelacakan stok aktif.
                Tidak ada transaksi dummy yang otomatis dikecualikan.
                Saldo simulasi bukan hasil penghitungan fisik di gudang.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-end">Saldo Histori</th>
                            <th class="text-end">Selisih</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($simulation as $row)
                            <tr>
                                <td>{{ $row->item_code }}</td>
                                <td>{{ $row->item_name }}</td>
                                <td>{{ $row->unit }}</td>

                                <td class="text-end">
                                    {{ number_format($row->current_stock, 2, ',', '.') }}
                                </td>

                                <td class="text-end
                                    {{ $row->simulated_stock < 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($row->simulated_stock, 2, ',', '.') }}
                                </td>

                                <td class="text-end
                                    {{ abs($row->difference) >= 0.00001 ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($row->difference, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    Tidak ada data simulasi stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <strong>Detail Transaksi untuk Pemeriksaan</strong>
            <div class="small text-muted">
                Detail mengikuti filter di atas. Barang yang tidak dilacak
                stoknya tetap ditampilkan untuk membantu penelusuran transaksi.
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor Transaksi</th>
                        <th>Jenis</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th class="text-end">Jumlah</th>
                        <th>Pelacakan Stok</th>
                        <th>Vendor</th>
                        <th>Nomor Nota</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($details as $row)
                        <tr>
                            <td>{{ $row->transaction_date }}</td>

                            <td>
                                {{ $row->transaction_number ?? ('ID ' . $row->transaction_id) }}
                            </td>

                            <td>
                                <span class="badge
                                    {{ $row->type === 'IN' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $row->type === 'IN' ? 'IN' : 'OUT' }}
                                </span>
                            </td>

                            <td>{{ $row->item_code }}</td>
                            <td>{{ $row->item_name }}</td>
                            <td>{{ $row->item_unit }}</td>

                            <td class="text-end">
                                {{ number_format((float) $row->quantity, 2, ',', '.') }}
                            </td>

                            <td>
                                @if ($row->track_stock)
                                    <span class="badge bg-success">Dilacak</span>
                                @else
                                    <span class="badge bg-secondary">Tidak dilacak</span>
                                @endif
                            </td>

                            <td>{{ $row->vendor_name ?? '—' }}</td>
                            <td>{{ $row->purchase_note_number ?? '—' }}</td>
                            <td>{{ $row->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                Tidak ada detail transaksi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    @media print {
        .sidebar,
        nav,
        .btn,
        form,
        .pagination {
            display: none !important;
        }

        .card {
            break-inside: avoid;
            box-shadow: none !important;
        }

        body {
            font-size: 10px;
        }

        .table {
            font-size: 9px;
        }
    }
</style>
@endsection