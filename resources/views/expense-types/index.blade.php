<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Biaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <div class="container my-5">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h2 class="h4 font-weight-bold text-dark m-0">
                        <i class="bi bi-cash-stack me-2 text-primary"></i>
                        Jenis Biaya
                    </h2>

                    <a href="{{ route('expense-types.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Jenis Biaya

                    </a>

                </div>


                <!-- Alert Success -->
                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show"
                         role="alert">

                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>

                    </div>

                @endif


                <!-- Alert Errors -->
                @if($errors->any())

                    <div class="alert alert-danger alert-dismissible fade show"
                         role="alert">

                        <div class="d-flex align-items-center mb-1">

                            <i class="bi bi-exclamation-triangle-fill me-2"></i>

                            <strong>Terjadi kesalahan:</strong>

                        </div>

                        <ul class="mb-0 ps-4">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>

                    </div>

                @endif


                <!-- Table -->
                <div class="table-responsive">

                    <table class="table table-hover align-middle border">

                        <thead class="table-light">

                            <tr>

                                <th class="text-center"
                                    style="width: 60px;">
                                    No
                                </th>

                                <th>
                                    Nama Jenis Biaya
                                </th>

                                <th>
                                    Kategori
                                </th>

                                <th class="text-center"
                                    style="width: 130px;">
                                    Status
                                </th>

                                <th class="text-center"
                                    style="width: 220px;">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($expenseTypes as $expenseType)

                                <tr>

                                    <td class="text-center text-muted fw-semibold">
                                        {{ $loop->iteration }}
                                    </td>


                                    <td class="fw-medium text-dark">

                                        <i class="bi bi-receipt me-1 text-secondary"></i>

                                        {{ $expenseType->name }}

                                    </td>


                                    <td>

                                        @if($expenseType->category)

                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                {{ $expenseType->category }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center">

                                        @if($expenseType->status)

                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">

                                                <i class="bi bi-check-circle me-1"></i>
                                                Aktif

                                            </span>

                                        @else

                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">

                                                <i class="bi bi-x-circle me-1"></i>
                                                Nonaktif

                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center">

                                        <div class="d-flex justify-content-center gap-2">

                                            <!-- Edit -->
                                            <a href="{{ route('expense-types.edit', $expenseType) }}"
                                               class="btn btn-sm btn-outline-warning">

                                                <i class="bi bi-pencil me-1"></i>
                                                Edit

                                            </a>


                                            <!-- Toggle Status -->
                                            <form action="{{ route('expense-types.toggle-status', $expenseType) }}"
                                                  method="POST"
                                                  class="d-inline">

                                                @csrf

                                                @method('PATCH')


                                                @if($expenseType->status)

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Yakin ingin menonaktifkan jenis biaya ini?')">

                                                        <i class="bi bi-power me-1"></i>
                                                        Nonaktifkan

                                                    </button>

                                                @else

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success">

                                                        <i class="bi bi-power me-1"></i>
                                                        Aktifkan

                                                    </button>

                                                @endif

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="text-center py-4 text-muted">

                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                        Belum ada jenis biaya.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
    </script>

</body>
</html>