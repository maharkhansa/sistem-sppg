@extends('layouts.app')

@section('title', 'Laporan Neraca')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Laporan Neraca</h1>
            <p class="text-muted mb-0">Posisi aset, kewajiban, dan ekuitas.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('finance.cash-bank') }}" class="btn btn-outline-primary">Kas & Bank</a>
            <a href="{{ route('finance.profit-loss') }}" class="btn btn-outline-primary">Laba Rugi</a>
            <a href="{{ route('finance.journals.index') }}" class="btn btn-outline-secondary">Jurnal</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.balance-sheet') }}"
                  class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="as_of" class="form-label">Posisi per tanggal</label>
                    <input type="date" id="as_of" name="as_of" class="form-control"
                           value="{{ $asOf }}" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Tampilkan Neraca</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="h4 fw-bold mb-1">NERACA</h2>
                <p class="text-muted mb-0">
                    Posisi per {{ \Illuminate\Support\Carbon::parse($asOf)->format('d/m/Y') }}
                </p>
            </div>

            <h3 class="h6 fw-bold border-bottom pb-2">ASET</h3>
            @forelse($assets as $account)
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <span>{{ $account->code }} — {{ $account->name }}</span>
                    <span class="text-nowrap">
                        Rp {{ number_format($account->report_balance, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-muted">Belum ada akun aset.</p>
            @endforelse
            <div class="d-flex justify-content-between fw-bold py-3 mb-4">
                <span>TOTAL ASET</span>
                <span>Rp {{ number_format($totalAssets, 2, ',', '.') }}</span>
            </div>

            <h3 class="h6 fw-bold border-bottom pb-2">LIABILITAS / KEWAJIBAN</h3>
            @forelse($liabilities as $account)
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <span>{{ $account->code }} — {{ $account->name }}</span>
                    <span class="text-nowrap">
                        Rp {{ number_format($account->report_balance, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-muted">Belum ada akun kewajiban.</p>
            @endforelse
            <div class="d-flex justify-content-between fw-bold py-3 mb-4">
                <span>TOTAL LIABILITAS</span>
                <span>Rp {{ number_format($totalLiabilities, 2, ',', '.') }}</span>
            </div>

            <h3 class="h6 fw-bold border-bottom pb-2">EKUITAS / MODAL</h3>
            @forelse($equity as $account)
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <span>{{ $account->code }} — {{ $account->name }}</span>
                    <span class="text-nowrap">
                        Rp {{ number_format($account->report_balance, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-muted">Belum ada akun ekuitas.</p>
            @endforelse

            <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                <span>Hasil Usaha Berjalan (sebelum tutup buku)</span>
                <span class="text-nowrap">
                    Rp {{ number_format($currentEarnings, 2, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between fw-bold py-3 mb-4">
                <span>TOTAL EKUITAS</span>
                <span>Rp {{ number_format($totalEquity, 2, ',', '.') }}</span>
            </div>

            <div class="rounded border p-3 mb-3 d-flex justify-content-between gap-3 fw-bold">
                <span>TOTAL LIABILITAS + EKUITAS</span>
                <span class="text-nowrap">
                    Rp {{ number_format($totalLiabilitiesEquity, 2, ',', '.') }}
                </span>
            </div>

            @php
                $difference = round($totalAssets - $totalLiabilitiesEquity, 2);
            @endphp

            @if(abs($difference) < 0.01)
                <div class="alert alert-success mb-0">
                    Neraca seimbang. Selisih: Rp {{ number_format(abs($difference), 2, ',', '.') }}.
                </div>
            @else
                <div class="alert alert-warning mb-0">
                    Neraca belum seimbang. Selisih aset terhadap liabilitas dan ekuitas:
                    <strong>Rp {{ number_format($difference, 2, ',', '.') }}</strong>.
                    Periksa saldo awal, jurnal, dan pencatatan akun.
                </div>
            @endif

            <p class="small text-muted mt-3 mb-0">
                Hasil usaha berjalan masih dihitung dari pendapatan dan beban kumulatif jurnal yang diposting.
                Laporan ini belum melakukan tutup buku otomatis.
            </p>
        </div>
    </div>
</div>
@endsection