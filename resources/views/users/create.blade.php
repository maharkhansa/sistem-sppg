@extends('layouts.app')

@section('title', 'Tambah Akun Admin | Sistem SPPG')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-4">
        <a href="{{ route('users.index') }}"
           class="text-decoration-none text-muted small">
            ← Kembali ke Kelola Akun
        </a>

        <h1 class="h3 fw-bold mt-3 mb-1">Tambah Akun Admin</h1>
        <p class="text-muted mb-0">
            Isi data untuk membuat akun admin baru.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="max-width: 760px; border-radius: 14px;">
        <div class="card-body p-4">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        Nama Admin
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        maxlength="255"
                        required
                        autocomplete="name"
                        placeholder="Masukkan nama admin"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">
                        Username
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}"
                        maxlength="50"
                        required
                        autocomplete="username"
                        placeholder="Contoh: admin_gudang"
                    >
                    <div class="form-text">
                        Gunakan username yang unik. Spasi tidak diperbolehkan.
                    </div>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        maxlength="255"
                        required
                        autocomplete="email"
                        placeholder="Masukkan email admin"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        Konfirmasi Password
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        Simpan Akun
                    </button>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection