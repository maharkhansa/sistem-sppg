@extends('layouts.app')

@section('title', 'Kelola Akun Admin | Sistem SPPG')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kelola Akun Admin</h1>
            <p class="text-muted mb-0">
                Tambah akun admin dan perbarui username atau password.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                Kembali ke Dashboard
            </a>

            <a href="{{ route('users.create') }}" class="btn btn-success">
                + Tambah Akun
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">No.</th>
                            <th>Nama Admin</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    {{ $users->firstItem() + $loop->index }}
                                </td>
                                <td class="fw-semibold">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="badge bg-success ms-1">Akun Anda</span>
                                    @endif
                                </td>
                                <td>{{ $user->username ?: '-' }}</td>
                                <td>{{ $user->email ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $user->role ?: 'ADMIN' }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        href="{{ route('users.edit', $user) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada akun admin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection