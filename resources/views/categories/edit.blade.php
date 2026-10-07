<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex align-items-center mb-4">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm me-3" title="Kembali">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <h2 class="h4 font-weight-bold text-dark m-0">
                                <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Kategori
                            </h2>
                        </div>

                        <!-- Global Alert Errors -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Terjadi kesalahan input:</strong>
                                </div>
                                <ul class="mb-0 ps-4">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Form Edit -->
                        <form action="{{ route('categories.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold text-dark">
                                    Nama Kategori <span class="text-danger">*</span>
                                </label>
                                
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $category->name) }}" 
                                    placeholder="Masukkan nama kategori..."
                                    required 
                                    autofocus
                                >

                                <!-- Feedback Validasi Inline -->
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('categories.index') }}" class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-warning px-4 text-white">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>