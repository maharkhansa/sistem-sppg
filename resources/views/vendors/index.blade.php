@extends('layouts.app')

@section('title', 'Data Vendor')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Data Vendor</h4>
            <p class="text-muted mb-0">
                Kelola pasar, toko, dan tempat pembelian barang.
            </p>
        </div>

        <a href="{{ route('vendors.create') }}" class="btn btn-primary">
            + Tambah Vendor
        </a>
    </div>

    {{-- PENCARIAN DAN FILTER --}}
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('vendors.index') }}" method="GET">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-6">
                        <label for="search" class="form-label">
                            Cari Vendor
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Nama vendor, kontak, telepon..."
                        >
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="status" class="form-label">
                            Status Vendor
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-select"
                        >
                            <option value="">Semua Status</option>

                            <option
                                value="aktif"
                                {{ request('status') === 'aktif' ? 'selected' : '' }}
                            >
                                Aktif
                            </option>

                            <option
                                value="nonaktif"
                                {{ request('status') === 'nonaktif' ? 'selected' : '' }}
                            >
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Cari / Filter
                            </button>

                            <a
                                href="{{ route('vendors.index') }}"
                                class="btn btn-secondary"
                            >
                                Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No.</th>
                            <th>Nama Vendor</th>
                            <th>PJ</th>
                            <th>Kontak</th>
                            <th class="text-center">Jumlah Barang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $vendor->name }}</strong>

                                    @if($vendor->address)
                                        <div class="small text-muted">
                                            {{ $vendor->address }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @forelse($vendor->purchaseResponsibles as $pj)
                                        <div>{{ $pj->name }}</div>
                                    @empty
                                        <span class="text-muted">Belum ada PJ</span>
                                    @endforelse
                                </td>
                                <td>
                                    {{ $vendor->contact_person ?? '-' }}

                                    @if($vendor->phone)
                                        <div class="small text-muted">
                                            {{ $vendor->phone }}
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{ $vendor->items_count }} barang
                                </td>

                                <td class="text-center">
                                    @if($vendor->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a
                                        href="{{ route('vendors.edit', $vendor) }}"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('vendors.toggle-status', $vendor) }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $vendor->is_active ? 'btn-secondary' : 'btn-success' }}"
                                            onclick="return confirm('Yakin ingin mengubah status Vendor ini?')"
                                        >
                                            {{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada data Vendor.
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