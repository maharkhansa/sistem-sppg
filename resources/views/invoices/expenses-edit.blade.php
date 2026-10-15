@extends('layouts.app')

@section('title', 'Edit Biaya Non-Stok | Sistem SPPG')

@push('styles')
<style>
    .expense-edit-card {
        border: 1px solid #dee2e6;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }

    .expense-edit-header {
        padding: 20px;
        background: #f8fafc;
        border-bottom: 1px solid #dee2e6;
    }

    .expense-edit-table th,
    .expense-edit-table td {
        padding: 10px;
        vertical-align: middle;
    }

    .expense-edit-table th {
        background: #f8f9fa;
        white-space: nowrap;
        font-size: 12px;
    }

    .expense-edit-table input,
    .expense-edit-table select {
        min-width: 110px;
    }

    .expense-edit-table .expense-supplier {
        min-width: 180px;
    }

    .expense-edit-table .expense-notes {
        min-width: 180px;
    }

    .expense-edit-table .expense-type {
        min-width: 180px;
    }

    .expense-edit-table .expense-date {
        min-width: 145px;
    }

    .expense-edit-table .expense-amount {
        min-width: 140px;
    }

    .expense-edit-table .expense-qty {
        min-width: 90px;
    }

    .expense-edit-table .expense-unit {
        min-width: 100px;
    }

    .expense-summary {
        padding: 16px 20px;
        background: #f8fafc;
        border-top: 1px solid #dee2e6;
    }

    .expense-table-wrapper {
        overflow-x: auto;
    }

    .expense-empty {
        padding: 28px;
        color: #6c757d;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid container-xl py-4 py-lg-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">
                <i class="bi bi-receipt-cutoff text-warning me-2"></i>
                Edit Biaya Non-Stok
            </h2>
            <p class="text-muted small mb-0">
                Kelola biaya berdasarkan tanggal, jenis biaya, dan supplier tanpa mengubah transaksi stok.
            </p>
        </div>

        <a href="{{ route('invoices.show', $invoice->id) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Invoice
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data biaya:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="expense-edit-card shadow-sm mb-4">
        <div class="expense-edit-header">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="text-muted small">Nomor Invoice</div>
                    <div class="fw-bold">
                        {{ $invoice->invoice_number }}
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="text-muted small">Nomor OUT</div>
                    <div class="fw-semibold">
                        {{ $invoice->stockTransaction?->transaction_number ?? '-' }}
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="text-muted small">Dapur / SPPG</div>
                    <div class="fw-semibold">
                        {{ $invoice->kitchen?->id_sppg ?? '-' }}
                        - {{ $invoice->kitchen?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('invoices.expenses.update', $invoice->id) }}"
              id="expenseEditForm">
            @csrf
            @method('PUT')

            @php
                $defaultRows = $details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'supplier_name' => $detail->supplier_name ?? '',
                        'expense_type_id' => $detail->expense_type_id,
                        'expense_date' => $detail->expense_date
                            ? \Illuminate\Support\Carbon::parse($detail->expense_date)->format('Y-m-d')
                            : '',
                        'quantity' => $detail->quantity,
                        'unit' => $detail->unit,
                        'unit_price' => $detail->unit_price,
                        'notes' => $detail->notes,
                    ];
                })->values()->all();

                $formRows = old('details', $defaultRows);
            @endphp

            <div class="p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h5 class="h6 fw-bold mb-1">Rincian Biaya</h5>
                        <p class="text-muted small mb-0">
                            Isi nama supplier untuk setiap biaya. Setiap baris disimpan sebagai rincian tersendiri.
                        </p>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="addExpenseRow">
                        <i class="bi bi-plus-circle me-1"></i>
                        Tambah Biaya
                    </button>
                </div>

                <div class="expense-table-wrapper">
                    <table class="table table-bordered expense-edit-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th>Tanggal Biaya</th>
                                <th>Nama Supplier</th>
                                <th>Jenis Biaya</th>
                                <th>Keterangan</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Harga Satuan (Rp)</th>
                                <th>Jumlah (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="expenseRows">
                            @foreach ($formRows as $index => $row)
                                <tr class="expense-row">
                                    <td class="text-center row-number">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <input type="hidden"
                                               name="details[{{ $index }}][id]"
                                               value="{{ $row['id'] ?? '' }}">

                                        <input type="date"
                                               class="form-control form-control-sm expense-date"
                                               name="details[{{ $index }}][expense_date]"
                                               value="{{ $row['expense_date'] ?? '' }}"
                                               required>
                                    </td>

                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm expense-supplier"
                                               name="details[{{ $index }}][supplier_name]"
                                               value="{{ $row['supplier_name'] ?? '' }}"
                                               maxlength="255"
                                               placeholder="Nama supplier">
                                    </td>

                                    <td>
                                        <select class="form-select form-select-sm expense-type"
                                                name="details[{{ $index }}][expense_type_id]"
                                                required>
                                            <option value="">Pilih jenis biaya</option>
                                            @foreach ($expenseTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ (string) ($row['expense_type_id'] ?? '') === (string) $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm expense-notes"
                                               name="details[{{ $index }}][notes]"
                                               value="{{ $row['notes'] ?? '' }}"
                                               maxlength="1000"
                                               placeholder="Keterangan biaya">
                                    </td>

                                    <td>
                                        <input type="number"
                                               class="form-control form-control-sm expense-qty"
                                               name="details[{{ $index }}][quantity]"
                                               value="{{ $row['quantity'] ?? 1 }}"
                                               min="0.01"
                                               step="any"
                                               required>
                                    </td>

                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm expense-unit"
                                               name="details[{{ $index }}][unit]"
                                               value="{{ $row['unit'] ?? 'pcs' }}"
                                               maxlength="50"
                                               required>
                                    </td>

                                    <td>
                                        <input type="number"
                                               class="form-control form-control-sm expense-amount expense-price"
                                               name="details[{{ $index }}][unit_price]"
                                               value="{{ $row['unit_price'] ?? 0 }}"
                                               min="0"
                                               step="any"
                                               required>
                                    </td>

                                    <td class="text-end expense-subtotal">
                                        Rp 0
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-expense"
                                                title="Hapus biaya ini">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="emptyExpenseMessage"
                     class="expense-empty {{ count($formRows) ? 'd-none' : '' }}">
                    Belum ada biaya. Klik <strong>Tambah Biaya</strong> untuk memasukkan biaya non-stok.
                </div>
            </div>

            <div class="expense-summary d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted small">Total Biaya Non-Stok</div>
                    <div class="h5 fw-bold mb-0" id="expenseGrandTotal">
                        Rp 0
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('invoices.show', $invoice->id) }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Biaya
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="alert alert-info small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Perubahan pada halaman ini hanya ditujukan untuk detail biaya non-stok.
        Data barang dan transaksi stok tidak diubah.
    </div>

</div>

<template id="expenseRowTemplate">
    <tr class="expense-row">
        <td class="text-center row-number"></td>

        <td>
            <input type="hidden" data-field="id" value="">

            <input type="date"
                   class="form-control form-control-sm expense-date"
                   data-field="expense_date"
                   required>
        </td>

        <td>
            <input type="text"
                   class="form-control form-control-sm expense-supplier"
                   data-field="supplier_name"
                   maxlength="255"
                   placeholder="Nama supplier">
        </td>

        <td>
            <select class="form-select form-select-sm expense-type"
                    data-field="expense_type_id"
                    required>
                <option value="">Pilih jenis biaya</option>
                @foreach ($expenseTypes as $type)
                    <option value="{{ $type->id }}">
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="text"
                   class="form-control form-control-sm expense-notes"
                   data-field="notes"
                   maxlength="1000"
                   placeholder="Keterangan biaya">
        </td>

        <td>
            <input type="number"
                   class="form-control form-control-sm expense-qty"
                   data-field="quantity"
                   value="1"
                   min="0.01"
                   step="any"
                   required>
        </td>

        <td>
            <input type="text"
                   class="form-control form-control-sm expense-unit"
                   data-field="unit"
                   value="pcs"
                   maxlength="50"
                   required>
        </td>

        <td>
            <input type="number"
                   class="form-control form-control-sm expense-amount expense-price"
                   data-field="unit_price"
                   value="0"
                   min="0"
                   step="any"
                   required>
        </td>

        <td class="text-end expense-subtotal">Rp 0</td>

        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-outline-danger remove-expense"
                    title="Hapus biaya ini">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rowsContainer = document.getElementById('expenseRows');
    const addButton = document.getElementById('addExpenseRow');
    const template = document.getElementById('expenseRowTemplate');
    const totalElement = document.getElementById('expenseGrandTotal');
    const emptyMessage = document.getElementById('emptyExpenseMessage');

    let nextIndex = {{ count($formRows) }};

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 2
        }).format(value || 0);
    }

    function refreshRows() {
        let grandTotal = 0;

        rowsContainer.querySelectorAll('.expense-row').forEach(function (row, index) {
            row.querySelector('.row-number').textContent = index + 1;

            const quantityInput =
                row.querySelector('[data-field="quantity"]') ||
                row.querySelector('[name$="[quantity]"]');

            const priceInput =
                row.querySelector('[data-field="unit_price"]') ||
                row.querySelector('[name$="[unit_price]"]');

            const quantity = parseFloat(quantityInput?.value ?? 0) || 0;
            const price = parseFloat(priceInput?.value ?? 0) || 0;
            const subtotal = quantity * price;

            row.querySelector('.expense-subtotal').textContent =
                formatRupiah(subtotal);

            grandTotal += subtotal;
        });

        totalElement.textContent = formatRupiah(grandTotal);

        emptyMessage.classList.toggle(
            'd-none',
            rowsContainer.querySelectorAll('.expense-row').length > 0
        );
    }

    function addExpenseRow() {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.expense-row');

        row.querySelectorAll('[data-field]').forEach(function (field) {
            const fieldName = field.dataset.field;
            field.name = `details[${nextIndex}][${fieldName}]`;
        });

        rowsContainer.appendChild(fragment);
        nextIndex++;

        refreshRows();
    }

    addButton.addEventListener('click', addExpenseRow);

    rowsContainer.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-expense');

        if (!button) {
            return;
        }

        const row = button.closest('.expense-row');

        if (row && confirm('Hapus biaya ini setelah formulir disimpan?')) {
            row.remove();
            refreshRows();
        }
    });

    rowsContainer.addEventListener('input', function (event) {
        if (
            event.target.matches('[name$="[quantity]"]') ||
            event.target.matches('[name$="[unit_price]"]') ||
            event.target.matches('[data-field="quantity"]') ||
            event.target.matches('[data-field="unit_price"]')
        ) {
            refreshRows();
        }
    });

    refreshRows();
});
</script>
@endpush