@extends('layouts.app')

@section('title', 'Penanggung Jawab Pembelian')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-1">Penanggung Jawab Pembelian</h1>
            <p class="text-muted mb-0">
                Kelola petugas yang bertanggung jawab atas pembelian barang.
            </p>
        </div>

        <a href="{{ route('purchase-responsibles.create') }}"
           class="btn btn-primary">
            + Tambah Penanggung Jawab
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('purchase-responsibles.index') }}"
                  class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari petugas</label>
                    <input type="text" id="search" name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Nama atau nomor telepon">
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="active"
                            @selected(request('status') === 'active')>
                            Aktif
                        </option>
                        <option value="inactive"
                            @selected(request('status') === 'inactive')>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>
                    <a href="{{ route('purchase-responsibles.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No.</th>
                        <th>Nama Petugas</th>
                        <th>Nomor Telepon</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th style="min-width: 190px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($responsibles as $responsible)
                        <tr>
                            <td>
                                {{ $responsibles->firstItem() + $loop->index }}
                            </td>
                            <td class="fw-semibold">
                                {{ $responsible->name }}
                            </td>
                            <td>{{ $responsible->phone ?: '-' }}</td>
                            <td>{{ $responsible->notes ?: '-' }}</td>
                            <td>
                                @if ($responsible->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('purchase-responsibles.edit', $responsible) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('purchase-responsibles.toggle-status', $responsible) }}"
                                          onsubmit="return confirm('Yakin ingin {{ $responsible->is_active ? 'menonaktifkan' : 'mengaktifkan' }} petugas ini?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm {{ $responsible->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $responsible->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada data penanggung jawab.
                                Klik tombol Tambah Penanggung Jawab untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($responsibles->hasPages())
            <div class="card-body">
                {{ $responsibles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection