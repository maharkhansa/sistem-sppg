@extends('layouts.app')

@section('title', 'Rekonsiliasi Stok')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Rekonsiliasi Stok</h1>
            <div class="text-muted small">
                Pemeriksaan saldo tanpa mengubah transaksi atau stok.
            </div>
        </div>

        <button type="button" class="btn btn-outline-secondary"
            onclick="window.print()">
            Cetak Laporan
        </button>
    </div>

    <div class="alert alert-warning">
        <strong>Mode pemeriksaan.</strong>
        Saldo simulasi bukan jumlah fisik barang. Data tidak diubah,
        tidak ada transaksi yang dihapus, dan status dummy tidak
        ditentukan otomatis.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Barang dengan record stok</div>
                    <div class="fs-3 fw-bold">
                        {{ number_format($summary['stock_items']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Saldo berbeda dari histori</div>
                    <div class="fs-3 fw-bold text-warning">
                        {{ number_format($summary['mismatches']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Simulasi saldo negatif</div>
                    <div class="fs-3 fw-bold text-danger">
                        {{ number_format($summary['negative_simulation']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Transaksi dummy terkonfirmasi</div>
                    <div class="fs-3 fw-bold">
                        {{ number_format($summary['confirmed_dummy_count']) }}
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
            <form method="GET" action="{{ route('stock-reconciliation.index') }}"
                class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari transaksi atau barang</label>
                    <input type="text" name="search" class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Nomor transaksi, kode, nama barang">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis</label>
                    <select name="type" class="form-select">
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
                    <label class="form-label">Dari tanggal</label>
                    <input type="date" name="date_from" class="form-control"
                        value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sampai tanggal</label>
                    <input type="date" name="date_to" class="form-control"
                        value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('stock-reconciliation.index') }}"
                        class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Perbandingan Saldo dan Histori</strong>
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
                                {{ abs($row->difference) > 0.0001 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($row->difference, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                Belum ada data stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Simulasi Mengecualikan 12 Transaksi Dummy Terkonfirmasi</strong>
        </div>

        <div class="card-body">
            <p class="small text-muted">
                Simulasi ini mengecualikan ID 119, 120, 121, 122, 125,
                127, 128, 129, 131, 132, 134, dan 136 dari perhitungan
                histori. Simulasi tidak mengubah saldo saat ini.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-end">Saldo Simulasi</th>
                            <th class="text-end">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($simulation as $row)
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
                                <td class="text-end">
                                    {{ number_format($row->difference, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <strong>Detail Transaksi untuk Pemeriksaan</strong>
            <div class="small text-muted">
                Gunakan tabel ini untuk menelusuri barang dan jumlah transaksi.
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
                                <span class="badge {{ $row->type === 'IN' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $row->type }}
                                </span>
                            </td>
                            <td>{{ $row->item_code }}</td>
                            <td>{{ $row->item_name }}</td>
                            <td>{{ $row->item_unit }}</td>
                            <td class="text-end">
                                {{ number_format((float) $row->quantity, 2, ',', '.') }}
                            </td>
                            <td>{{ $row->vendor_name ?? '—' }}</td>
                            <td>{{ $row->purchase_note_number ?? '—' }}</td>
                            <td>{{ $row->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                Tidak ada detail transaksi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
           {{-- Data detail belum menggunakan pagination --}}
        </div>
    </div>
</div>

<style>
    @media print {
        .sidebar, nav, .btn, form, .pagination {
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