@extends('layouts.app')

@section('title', 'Tambah Penanggung Jawab')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-4">
        <h1 class="h4 mb-1">Tambah Penanggung Jawab</h1>
        <p class="text-muted mb-0">
            Masukkan identitas petugas yang bertanggung jawab atas pembelian.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-responsibles.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Nama Petugas <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           maxlength="150" required autofocus
                           placeholder="Contoh: Nikmat">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}"
                           maxlength="30"
                           placeholder="Opsional">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label">Keterangan</label>
                    <textarea id="notes" name="notes" rows="3"
                              maxlength="2000"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Catatan tambahan jika diperlukan">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        Simpan Petugas
                    </button>
                    <a href="{{ route('purchase-responsibles.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection