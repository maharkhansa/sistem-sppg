@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Laporan Laba Rugi</h1>
            <p class="text-muted mb-0">Pendapatan dikurangi beban dari jurnal yang diposting.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('finance.cash-bank') }}" class="btn btn-outline-primary">Kas & Bank</a>
            <a href="{{ route('finance.balance-sheet') }}" class="btn btn-outline-primary">Neraca</a>
            <a href="{{ route('finance.journals.index') }}" class="btn btn-outline-secondary">Jurnal</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.profit-loss') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Dari tanggal</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                           value="{{ $startDate }}" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Sampai tanggal</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                           value="{{ $endDate }}" required>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                    <a href="{{ route('finance.profit-loss') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="h4 fw-bold mb-1">LAPORAN LABA RUGI</h2>
                <p class="text-muted mb-0">
                    Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }}
                    sampai {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}
                </p>
            </div>

            <h3 class="h6 fw-bold border-bottom pb-2">PENDAPATAN</h3>
            @forelse($revenues as $account)
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <span>{{ $account->code }} — {{ $account->name }}</span>
                    <span class="text-nowrap">Rp {{ number_format($account->report_balance, 2, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-muted">Belum ada akun pendapatan.</p>
            @endforelse

            <div class="d-flex justify-content-between fw-bold py-3 mb-4">
                <span>Total Pendapatan</span>
                <span>Rp {{ number_format($totalRevenue, 2, ',', '.') }}</span>
            </div>

            <h3 class="h6 fw-bold border-bottom pb-2">BEBAN</h3>
            @forelse($expenses as $account)
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <span>{{ $account->code }} — {{ $account->name }}</span>
                    <span class="text-nowrap">Rp {{ number_format($account->report_balance, 2, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-muted">Belum ada akun beban.</p>
            @endforelse

            <div class="d-flex justify-content-between fw-bold py-3 mb-3">
                <span>Total Beban</span>
                <span>Rp {{ number_format($totalExpense, 2, ',', '.') }}</span>
            </div>

            <div class="rounded border p-3 d-flex justify-content-between align-items-center gap-3">
                <span class="fw-bold">
                    {{ $netProfit >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}
                </span>
                <span class="fs-5 fw-bold {{ $netProfit < 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format(abs($netProfit), 2, ',', '.') }}
                </span>
            </div>

            <p class="small text-muted mt-3 mb-0">
                Laporan ini hanya mencakup jurnal yang sudah diposting. Pastikan pencatatan pendapatan,
                beban, dan penyesuaian persediaan sudah lengkap sebelum menggunakan laporan untuk keputusan resmi.
            </p>
        </div>
    </div>
</div>
@endsection