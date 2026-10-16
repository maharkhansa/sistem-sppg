@extends('layouts.app')

@section('title', 'Detail Jurnal')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Detail Jurnal</h1>
            <p class="text-muted mb-0">{{ $journal->entry_number }}</p>
        </div>
        <a href="{{ route('finance.journals.index') }}"
           class="btn btn-outline-secondary">Kembali ke Jurnal</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Nomor Jurnal</div>
                    <div class="fw-semibold">{{ $journal->entry_number }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Tanggal</div>
                    <div class="fw-semibold">
                        {{ $journal->entry_date->format('d/m/Y') }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Keterangan</div>
                    <div class="fw-semibold">{{ $journal->description }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Status</div>
                    @if($journal->status === 'posted')
                        <span class="badge bg-success">Diposting</span>
                    @elseif($journal->status === 'draft')
                        <span class="badge bg-warning text-dark">Draft</span>
                    @else
                        <span class="badge bg-secondary">Void</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Rincian Debit dan Kredit</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journal->lines as $line)
                        <tr>
                            <td class="ps-3">{{ $line->account->code }}</td>
                            <td>{{ $line->account->name }}</td>
                            <td>{{ $line->description ?: '—' }}</td>
                            <td class="text-end">
                                Rp {{ number_format((float) $line->debit, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format((float) $line->credit, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL</td>
                        <td class="text-end">
                            Rp {{ number_format($journal->total_debit, 2, ',', '.') }}
                        </td>
                        <td class="text-end">
                            Rp {{ number_format($journal->total_credit, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                @if($journal->status === 'posted')
                    <span class="text-success">
                        <i class="bi bi-check-circle"></i>
                        Jurnal telah diposting pada
                        {{ $journal->posted_at?->format('d/m/Y H:i') ?? '—' }}.
                    </span>
                @elseif($journal->status === 'draft')
                    <span class="text-muted">
                        Jurnal masih draft dan belum masuk ke laporan keuangan.
                    </span>
                @else
                    <span class="text-muted">
                        Jurnal berstatus void.
                    </span>
                @endif
            </div>

            @if($journal->status === 'draft')
                <form method="POST"
                      action="{{ route('finance.journals.post', $journal) }}"
                      onsubmit="return confirm('Posting jurnal ini? Pastikan bukti transaksi sudah diperiksa.');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Posting Jurnal
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection