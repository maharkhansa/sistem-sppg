@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Daftar Akun</h1>
            <p class="text-muted mb-0">Bagan akun untuk pencatatan keuangan.</p>
        </div>

        <a href="{{ route('finance.journals.index') }}" class="btn btn-primary">
            <i class="bi bi-journal-text me-1"></i> Jurnal Umum
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.accounts.index') }}"
                  class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari akun</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Kode atau nama akun">
                </div>

                <div class="col-md-4">
                    <label for="type" class="form-label">Tipe akun</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Semua tipe</option>
                        @foreach([
                            'asset' => 'Aset',
                            'liability' => 'Liabilitas',
                            'equity' => 'Ekuitas',
                            'revenue' => 'Pendapatan',
                            'expense' => 'Beban',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                @selected(request('type') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('finance.accounts.index') }}"
                       class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Akun Induk</th>
                        <th>Kas/Bank</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ $account->code }}
                            </td>
                            <td>
                                {{ $account->name }}
                                @if($account->journal_entry_lines_count > 0)
                                    <div class="small text-muted">
                                        {{ $account->journal_entry_lines_count }}
                                        baris jurnal
                                    </div>
                                @endif
                            </td>
                            <td>
                                @switch($account->type)
                                    @case('asset') Aset @break
                                    @case('liability') Liabilitas @break
                                    @case('equity') Ekuitas @break
                                    @case('revenue') Pendapatan @break
                                    @case('expense') Beban @break
                                    @default {{ $account->type }}
                                @endswitch
                            </td>
                            <td>
                                {{ $account->parent?->name ?? '—' }}
                            </td>
                            <td>
                                @if($account->is_cash_bank)
                                    <span class="badge bg-info text-dark">Ya</span>
                                @else
                                    <span class="text-muted">Tidak</span>
                                @endif
                            </td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada akun yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
@endsection