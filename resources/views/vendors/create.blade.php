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
            <form
                action="{{ route('vendors.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
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

                {{-- REKENING VENDOR --}}
                <div class="card mb-4">
                    <div class="card-header">
                        Rekening Vendor
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Nama Bank</label>

                            <select name="bank_name" id="bank_name" class="form-select">
                                <option value="">-- Pilih Bank --</option>
                                @foreach ([
                                    'BCA',
                                    'Bank Mandiri',
                                    'BNI',
                                    'BRI',
                                    'BSI',
                                    'Bank Danamon',
                                    'Bank CIMB Niaga',
                                    'Bank Permata',
                                    'Bank Panin',
                                    'Bank OCBC',
                                    'Bank UOB',
                                    'Bank BTN',
                                    'Bank Jago',
                                    'SeaBank',
                                    'Bank Neo Commerce',
                                    'Lainnya'
                                ] as $bank)
                                    <option value="{{ $bank }}"
                                        {{ old('bank_name', $vendor->bank_name ?? '') === $bank ? 'selected' : '' }}>
                                        {{ $bank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="bank_account_number" class="form-label">
                                Nomor Rekening
                            </label>

                            <input
                                type="text"
                                name="bank_account_number"
                                id="bank_account_number"
                                class="form-control @error('bank_account_number') is-invalid @enderror"
                                value="{{ old('bank_account_number') }}"
                                maxlength="50"
                                placeholder="Nomor rekening vendor"
                            >

                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="bank_account_holder" class="form-label">
                                Nama Pemilik Rekening
                            </label>

                            <input
                                type="text"
                                name="bank_account_holder"
                                id="bank_account_holder"
                                class="form-control @error('bank_account_holder') is-invalid @enderror"
                                value="{{ old('bank_account_holder') }}"
                                maxlength="150"
                                placeholder="Nama sesuai rekening bank"
                            >

                            @error('bank_account_holder')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-text mt-2">
                            Isi jika vendor menerima pembayaran melalui transfer bank.
                        </div>
                    </div>
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

                {{-- NOTA VENDOR --}}
                <div class="mb-4">
                    <label for="receipt" class="form-label">
                        Upload Nota Vendor
                    </label>

                    <input
                        type="file"
                        name="receipt"
                        id="receipt"
                        class="form-control @error('receipt') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png"
                    >

                    @error('receipt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        Format PDF, JPG, JPEG, atau PNG. Maksimal 5 MB.
                    </div>
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