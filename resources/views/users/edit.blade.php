@extends('layouts.app')

@section('title', 'Edit Akun Admin | Sistem SPPG')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-4">
        <a href="{{ route('users.index') }}"
           class="text-decoration-none text-muted small">
            ← Kembali ke Kelola Akun
        </a>

        <h1 class="h3 fw-bold mt-3 mb-1">Edit Akun Admin</h1>
        <p class="text-muted mb-0">
            Perbarui identitas login akun yang dipilih.
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
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        Nama Admin
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        required
                        autocomplete="name"
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
                        value="{{ old('username', $user->username) }}"
                        maxlength="50"
                        required
                        autocomplete="username"
                    >
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
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <h5 class="fw-bold mb-2">Ganti Password</h5>
                <p class="text-muted small mb-3">
                    Kosongkan kedua kolom password jika tidak ingin menggantinya.
                </p>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        Password Baru
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        Konfirmasi Password Baru
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        Simpan Perubahan
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