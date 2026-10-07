<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang Masuk</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex align-items-center mb-4">
                            <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary btn-sm me-3" title="Kembali">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <h2 class="h4 font-weight-bold text-dark m-0">
                                <i class="bi bi-arrow-down-left-square me-2 text-success"></i>Tambah Barang Masuk (IN)
                            </h2>
                        </div>

                        <!-- Error Alert -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Terjadi kesalahan:</strong>
                                </div>
                                <ul class="mb-0 ps-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('stock-transactions.store') }}" method="POST">
                            @csrf

                            <!-- Section Tanggal & Supplier -->
                            <div class="row g-3 mb-4 bg-light p-3 rounded border">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label fw-semibold text-dark">
                                        Tanggal Barang Masuk
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
                                        <input
                                            type="date"
                                            id="transaction_date"
                                            name="transaction_date"
                                            class="form-control"
                                            value="{{ old('transaction_date', date('Y-m-d')) }}"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label fw-semibold text-dark">
                                        Supplier
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-truck"></i></span>
                                        <select
                                            id="supplier_id"
                                            name="supplier_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">-- Pilih Supplier --</option>
                                            @foreach ($suppliers as $supplier)
                                                <option
                                                    value="{{ $supplier->id }}"
                                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
                                                >
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Barang Header & Table -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 text-dark m-0 fw-bold">
                                    <i class="bi bi-list-check me-2 text-primary"></i>Detail Barang
                                </h3>
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle" id="items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 50px;">No</th>
                                            <th scope="col" style="min-width: 220px;">Barang</th>
                                            <th scope="col" style="width: 120px;">Jumlah</th>
                                            <th scope="col" style="width: 110px;">Satuan</th>
                                            <th scope="col" style="width: 160px;">Harga Satuan</th>
                                            <th scope="col" style="width: 180px;">Subtotal</th>
                                            <th scope="col" class="text-center" style="width: 80px;">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody id="items-body">
                                        <tr class="item-row">
                                            <td class="text-center row-number fw-semibold text-muted">1</td>

                                            <td>
                                                <select
                                                    name="items[0][item_id]"
                                                    class="form-select item-select"
                                                    required
                                                >
                                                    <option value="">-- Pilih Barang --</option>
                                                    @foreach ($items as $item)
                                                        <option
                                                            value="{{ $item->id }}"
                                                            data-unit="{{ $item->unit }}"
                                                        >
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    name="items[0][quantity]"
                                                    class="form-control quantity text-end"
                                                    step="0.01"
                                                    min="0.01"
                                                    required
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control unit bg-light text-center"
                                                    readonly
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    name="items[0][unit_price]"
                                                    class="form-control unit-price text-end"
                                                    step="0.01"
                                                    min="0"
                                                    required
                                                >
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control subtotal bg-light text-end font-monospace fw-semibold"
                                                    readonly
                                                >
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-4">
                                <button type="button" id="add-row" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-lg me-1"></i> + Tambah Barang
                                </button>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-4">
                                <label for="notes" class="form-label fw-semibold text-dark">Catatan</label>
                                <textarea
                                    name="notes"
                                    id="notes"
                                    class="form-control"
                                    rows="3"
                                >{{ old('notes') }}</textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                <a href="{{ route('stock-transactions.index') }}" class="btn btn-light border">
                                    <i class="bi bi-x-circle me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Simpan Barang Masuk
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script Asli Tanpa Perubahan Logika -->
    <script>
    let rowIndex = 1;

    const itemsBody = document.getElementById('items-body');
    const addRowButton = document.getElementById('add-row');

    function updateRowNumbers() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(function(row, index) {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function updateUnit(row) {
        const select = row.querySelector('.item-select');
        const option = select.options[select.selectedIndex];
        const unit = option.dataset.unit || '';
        row.querySelector('.unit').value = unit;
    }

    function calculateSubtotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price').value) || 0;
        const subtotal = quantity * price;

        row.querySelector('.subtotal').value = subtotal.toLocaleString('id-ID');
    }

    addRowButton.addEventListener('click', function() {
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelector('.item-select').name = 'items[' + rowIndex + '][item_id]';
        newRow.querySelector('.quantity').name = 'items[' + rowIndex + '][quantity]';
        newRow.querySelector('.unit-price').name = 'items[' + rowIndex + '][unit_price]';

        newRow.querySelector('.item-select').value = '';
        newRow.querySelector('.quantity').value = '';
        newRow.querySelector('.unit').value = '';
        newRow.querySelector('.unit-price').value = '';
        newRow.querySelector('.subtotal').value = '';

        itemsBody.appendChild(newRow);

        rowIndex++;

        updateRowNumbers();
    });

    itemsBody.addEventListener('change', function(event) {
        if (event.target.classList.contains('item-select')) {
            const row = event.target.closest('.item-row');
            updateUnit(row);
        }
    });

    itemsBody.addEventListener('input', function(event) {
        if (
            event.target.classList.contains('quantity') ||
            event.target.classList.contains('unit-price')
        ) {
            const row = event.target.closest('.item-row');
            calculateSubtotal(row);
        }
    });

    itemsBody.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('.item-row');

            if (rows.length > 1) {
                event.target.closest('.item-row').remove();
                updateRowNumbers();
            } else {
                alert('Minimal harus ada satu barang.');
            }
        }
    });
    </script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>