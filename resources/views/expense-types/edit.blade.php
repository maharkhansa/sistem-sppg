<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Jenis Biaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <div class="container my-5">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h2 class="h4 text-dark m-0">

                        <i class="bi bi-pencil-square me-2 text-warning"></i>

                        Edit Jenis Biaya

                    </h2>

                    <a href="{{ route('expense-types.index') }}"
                       class="btn btn-secondary">

                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali

                    </a>

                </div>


                @if($errors->any())

                    <div class="alert alert-danger">

                        <strong>Terjadi kesalahan:</strong>

                        <ul class="mb-0 mt-2">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                <form action="{{ route('expense-types.update', $expenseType) }}"
                      method="POST">

                    @csrf
                    @method('PUT')


                    <div class="mb-3">

                        <label for="name"
                               class="form-label fw-semibold">

                            Nama Jenis Biaya
                            <span class="text-danger">*</span>

                        </label>

                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control"
                               value="{{ old('name', $expenseType->name) }}"
                               required>

                    </div>


                    <div class="mb-4">

                        <label for="category"
                               class="form-label fw-semibold">

                            Kategori

                        </label>

                        <input type="text"
                               name="category"
                               id="category"
                               class="form-control"
                               value="{{ old('category', $expenseType->category) }}">

                    </div>


                    <div class="d-flex justify-content-end gap-2">

                        <a href="{{ route('expense-types.index') }}"
                           class="btn btn-secondary">

                            Batal

                        </a>

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="bi bi-save me-1"></i>
                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>
</html>