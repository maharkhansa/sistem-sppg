<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk (IN)</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                
                <!-- Header & Action Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 font-weight-bold text-dark m-0">
                        <i class="bi bi-arrow-down-left-square me-2 text-success"></i>Barang Masuk (IN)
                    </h2>
                    <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Barang Masuk
                    </a>
                </div>

                <!-- Alert Success -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Table Data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 50px;">No</th>
                                <th scope="col" style="width: 180px;">No. Transaksi</th>
                                <th scope="col" style="width: 140px;">Tanggal</th>
                                <th scope="col">Supplier</th>
                                <th scope="col">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>

                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-6">
                                            {{ $transaction->transaction_number }}
                                        </span>
                                    </td>

                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $transaction->transaction_date->format('d-m-Y') }}
                                    </td>

                                    <td>
                                        @if ($transaction->supplier)
                                            <span class="fw-medium text-dark">
                                                <i class="bi bi-truck me-1 text-secondary"></i>
                                                {{ $transaction->supplier->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($transaction->notes)
                                            <span class="text-secondary">{{ $transaction->notes }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada transaksi barang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>