@extends('layouts.app')

@section('title', 'Kas dan Bank')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kas & Bank</h1>
            <p class="text-muted mb-0">Mutasi berdasarkan jurnal yang sudah diposting.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('finance.journals.index') }}" class="btn btn-outline-secondary">Jurnal Umum</a>
            <a href="{{ route('finance.profit-loss') }}" class="btn btn-outline-primary">Laba Rugi</a>
            <a href="{{ route('finance.balance-sheet') }}" class="btn btn-outline-primary">Neraca</a>
        </div>
    </div>

    @if(!$account)
        <div class="alert alert-warning">
            Belum ada akun Kas/Bank aktif. Pastikan akun Kas atau Bank sudah tersedia dan ditandai sebagai akun Kas/Bank.
        </div>
    @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('finance.cash-bank') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="account_id" class="form-label">Akun</label>
                        <select name="account_id" id="account_id" class="form-select">
                            @foreach($accounts as $option)
                                <option value="{{ $option->id }}" @selected($account->id === $option->id)>
                                    {{ $option->code }} — {{ $option->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Dari tanggal</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Sampai tanggal</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <a href="{{ route('finance.cash-bank') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Saldo Awal</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($openingBalance, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Mutasi Bersih Periode</div>
                        <div class="fs-4 fw-bold">
                            Rp {{ number_format($totalDebit - $totalCredit, 2, ',', '.') }}
                        </div>
                        <div class="small text-muted">Debit dikurangi kredit</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Saldo Akhir</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($runningBalance, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0">{{ $account->code }} — {{ $account->name }}</h2>
                <div class="small text-muted">
                    Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }}
                    sampai {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Nomor Jurnal</th>
                            <th>Keterangan</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th class="text-end">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-light">
                            <td colspan="5" class="ps-3 fw-semibold">Saldo Awal</td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($openingBalance, 2, ',', '.') }}
                            </td>
                        </tr>
                        @forelse($transactions as $line)
                            <tr>
                                <td class="ps-3">{{ $line->journalEntry->entry_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('finance.journals.show', $line->journalEntry) }}">
                                        {{ $line->journalEntry->entry_number }}
                                    </a>
                                </td>
                                <td>{{ $line->description ?: $line->journalEntry->description }}</td>
                                <td class="text-end">Rp {{ number_format((float) $line->debit, 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format((float) $line->credit, 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($line->running_balance, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada transaksi kas/bank yang diposting pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="ps-3">Total Mutasi</td>
                            <td class="text-end">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($runningBalance, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection