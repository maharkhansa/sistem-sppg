@extends('layouts.app')

@section('title', 'Data Kategori')

@section('content')

<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-tags me-2 text-success"></i>
                Data Kategori
            </h4>
            <p class="text-muted mb-0">
                Kelola kategori barang pada Sistem SPPG.
            </p>
        </div>

        <a href="{{ route('categories.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Kategori
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

    {{-- Tabel kategori --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-list-ul me-2 text-success"></i>
                    Daftar Kategori
                </h6>

                <span class="badge bg-success-subtle text-success">
                    Total: {{ $categories->count() }} kategori
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center py-3" style="width: 70px;">
                                No.
                            </th>
                            <th>Nama Kategori</th>
                            <th class="text-center" style="width: 150px;">
                                Status
                            </th>
                            <th class="text-center" style="width: 260px;">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-center text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $category->name }}
                                </td>

                                <td class="text-center">
                                    @if($category->status)
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

                                        {{-- Edit --}}
                                        <a href="{{ route('categories.edit', $category) }}"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </a>

                                        {{-- Aktif / nonaktif --}}
                                        <form action="{{ route('categories.toggle-status', $category) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            @if($category->status)
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Yakin ingin menonaktifkan kategori ini?')">
                                                    <i class="bi bi-power me-1"></i>
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="return confirm('Aktifkan kembali kategori ini?')">
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
                                <td colspan="4" class="text-center py-5">
                                    <i class="bi bi-inbox display-6 text-muted d-block mb-3"></i>
                                    <div class="fw-semibold text-muted">
                                        Belum ada data kategori.
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Klik tombol Tambah Kategori untuk memasukkan data baru.
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