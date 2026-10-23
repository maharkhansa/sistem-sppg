@extends('layouts.app')

@section('title', 'Tambah Vendor')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Tambah Vendor</h4>
            <p class="text-muted mb-0">
                Tambahkan pasar, toko, atau tempat pembelian barang.
            </p>
        </div>

        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

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
        <div class="card-header">
            Informasi Vendor
        </div>

        <div class="card-body">
            <form action="{{ route('vendors.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Nama Vendor <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        maxlength="150"
                        placeholder="Contoh: Pasar, Toko Gas"
                        required
                    >

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        Gunakan nama umum tempat pembelian, bukan nama barang.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="contact_person" class="form-label">
                        Nama Kontak
                    </label>

                    <input
                        type="text"
                        name="contact_person"
                        id="contact_person"
                        class="form-control @error('contact_person') is-invalid @enderror"
                        value="{{ old('contact_person') }}"
                        maxlength="100"
                        placeholder="Nama penjual atau penanggung jawab"
                    >

                    @error('contact_person')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">
                        Nomor Telepon
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        maxlength="30"
                        placeholder="Nomor telepon Vendor"
                    >

                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label">
                        Alamat Vendor
                    </label>

                    <textarea
                        name="address"
                        id="address"
                        class="form-control @error('address') is-invalid @enderror"
                        rows="3"
                        placeholder="Alamat pasar, toko, atau tempat pembelian"
                    >{{ old('address') }}</textarea>

            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('vendors.index') }}" class="btn btn-light">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection