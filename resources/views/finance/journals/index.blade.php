@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Jurnal Umum</h1>
            <p class="text-muted mb-0">Daftar pencatatan debit dan kredit.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('finance.accounts.index') }}"
               class="btn btn-outline-secondary">Daftar Akun</a>
            <a href="{{ route('finance.journals.create') }}"
               class="btn btn-primary">+ Buat Jurnal</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.journals.index') }}"
                  class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="search">Cari jurnal</label>
                    <input id="search" name="search" class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Nomor atau keterangan">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="start_date">Dari tanggal</label>
                    <input type="date" id="start_date" name="start_date"
                           class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="end_date">Sampai tanggal</label>
                    <input type="date" id="end_date" name="end_date"
                           class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="draft" @selected(request('status') === 'draft')>
                            Draft
                        </option>
                        <option value="posted" @selected(request('status') === 'posted')>
                            Diposting
                        </option>
                        <option value="void" @selected(request('status') === 'void')>
                            Void
                        </option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('finance.journals.index') }}"
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
                        <th class="ps-3">Nomor</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ $journal->entry_number }}
                            </td>
                            <td>{{ $journal->entry_date->format('d/m/Y') }}</td>
                            <td>{{ $journal->description }}</td>
                            <td class="text-end">
                                Rp {{ number_format((float) $journal->total_debit, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format((float) $journal->total_credit, 2, ',', '.') }}
                            </td>
                            <td>
                                @if($journal->status === 'posted')
                                    <span class="badge bg-success">Diposting</span>
                                @elseif($journal->status === 'draft')
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @else
                                    <span class="badge bg-secondary">Void</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.journals.show', $journal) }}"
                                   class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada jurnal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $journals->links() }}
        </div>
    </div>
</div>
@endsection