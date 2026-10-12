@extends('layouts.app')

@section('title', 'Data Dapur SPPG')

@section('content')

<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-house-door me-2 text-success"></i>
                Data Dapur SPPG
            </h4>
            <p class="text-muted mb-0">
                Kelola identitas, lokasi, dan status dapur SPPG.
            </p>
        </div>

        <a href="{{ route('kitchens.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Data SPPG
        </a>
    </div>

    {{-- Notifikasi berhasil --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Notifikasi error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Validasi --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Terjadi kesalahan:
            </div>

            <ul class="mb-0 ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Tabel dapur SPPG --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-list-ul me-2 text-success"></i>
                    Daftar Dapur SPPG
                </h6>

                <span class="badge bg-success-subtle text-success">
                    Total: {{ $kitchens->count() }} dapur
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="text-center py-3" style="width: 65px;">
                                No.
                            </th>
                            <th style="min-width: 130px;">ID SPPG</th>
                            <th style="min-width: 220px;">Nama SPPG</th>
                            <th style="min-width: 170px;">Kabupaten/Kota</th>
                            <th style="min-width: 150px;">Provinsi</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                            <th class="text-center" style="min-width: 240px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($kitchens as $kitchen)
                            <tr>
                                <td class="text-center text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-2">
                                        {{ $kitchen->id_sppg }}
                                    </span>
                                </td>

                                <td class="fw-semibold">
                                    {{ $kitchen->name }}
                                </td>

                                <td>
                                    @if($kitchen->kabupaten_kota)
                                        <i class="bi bi-geo-alt me-1 text-secondary"></i>
                                        {{ $kitchen->kabupaten_kota }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($kitchen->provinsi)
                                        <i class="bi bi-map me-1 text-secondary"></i>
                                        {{ $kitchen->provinsi }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($kitchen->status)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center flex-wrap gap-2">

                                        {{-- Edit dapur --}}
                                        <a href="{{ route('kitchens.edit', $kitchen->id) }}"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </a>

                                        {{-- Aktif / nonaktif --}}
                                        <form action="{{ route('kitchens.toggle-status', $kitchen->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            @if($kitchen->status)
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Yakin ingin menonaktifkan SPPG ini?')">
                                                    <i class="bi bi-power me-1"></i>
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="return confirm('Aktifkan kembali SPPG ini?')">
                                                    <i class="bi bi-power me-1"></i>
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-house-door display-6 text-muted d-block mb-3"></i>

                                    <div class="fw-semibold text-muted">
                                        Belum ada data dapur SPPG.
                                    </div>

                                    <div class="small text-muted mt-1">
                                        Klik Tambah Data SPPG untuk memasukkan data baru.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>

@endsection