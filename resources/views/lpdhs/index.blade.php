@extends('layouts.app')

@section('title', 'LPDH - SPPG')

@section('content')
<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                LPDH
            </h4>
            <p class="text-muted mb-0">
                Laporan Penggunaan Dana Harian SPPG
            </p>
        </div>

        <a href="{{ route('lpdhs.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>
            Buat LPDH
        </a>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>
                <i class="bi bi-exclamation-circle me-1"></i>
                Terjadi kesalahan:
            </strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- DATA LPDH --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Laporan Penggunaan Dana Harian
                </h6>

                <span class="badge bg-secondary">
                    {{ $lpdhs->count() }} data
                </span>
            </div>
        </div>

        @if($lpdhs->count())
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 55px;">No</th>
                            <th>SPPG</th>
                            <th class="text-center">Tanggal Pelayanan</th>
                            <th class="text-center">Status Hari</th>
                            <th class="text-end">Penerima Manfaat</th>
                            <th class="text-end">Belanja Bahan Baku</th>
                            <th class="text-end">Operasional</th>
                            <th class="text-end">Saldo Akhir VA</th>
                            <th class="text-center" style="min-width: 190px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($lpdhs as $lpdh)
                            <tr>
                                {{-- NOMOR --}}
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- SPPG --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $lpdh->kitchen?->name ?? '-' }}
                                    </div>

                                    <small class="text-muted">
                                        ID SPPG:
                                        {{ $lpdh->kitchen?->id_sppg ?? '-' }}
                                    </small>
                                </td>

                                {{-- TANGGAL --}}
                                <td class="text-center text-nowrap">
                                    {{ $lpdh->service_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                {{-- STATUS --}}
                                <td class="text-center">
                                    @if($lpdh->day_status === 'Hari Pelayanan')
                                        <span class="badge bg-success">
                                            Hari Pelayanan
                                        </span>
                                    @elseif($lpdh->day_status === 'Hari Libur')
                                        <span class="badge bg-secondary">
                                            Hari Libur
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ $lpdh->day_status ?? '-' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- PENERIMA MANFAAT --}}
                                <td class="text-end text-nowrap">
                                    {{ number_format((float) ($lpdh->beneficiaries_count ?? 0), 0, ',', '.') }}
                                </td>

                                {{-- BAHAN BAKU --}}
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) ($lpdh->raw_material_expenses ?? 0), 0, ',', '.') }}
                                </td>

                                {{-- OPERASIONAL --}}
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) ($lpdh->operational_expenses ?? 0), 0, ',', '.') }}
                                </td>

                                {{-- SALDO VA --}}
                                <td class="text-end text-nowrap fw-semibold">
                                    Rp {{ number_format((float) ($lpdh->va_final_balance ?? 0), 0, ',', '.') }}
                                </td>

                                {{-- AKSI --}}
                                <td>
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                        <a
                                            href="{{ route('lpdhs.show', $lpdh->id) }}"
                                            class="btn btn-sm btn-info text-white"
                                            title="Lihat LPDH"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Lihat
                                        </a>

                                        <a
                                            href="{{ route('lpdhs.edit', $lpdh->id) }}"
                                            class="btn btn-sm btn-warning"
                                            title="Edit LPDH"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('lpdhs.destroy', $lpdh->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus LPDH ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus LPDH"
                                            >
                                                <i class="bi bi-trash"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 px-3">
                <i class="bi bi-file-earmark-x display-4 text-muted"></i>

                <h6 class="fw-bold mt-3 mb-2">
                    Belum Ada LPDH
                </h6>

                <p class="text-muted mb-3">
                    Belum terdapat laporan penggunaan dana harian.
                </p>

                <a href="{{ route('lpdhs.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>
                    Buat LPDH Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection