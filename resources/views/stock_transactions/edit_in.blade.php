@extends('layouts.app')

@section('title', 'Edit Barang Masuk')

@push('styles')
<style>
    .page-title {
        font-size: 1.45rem;
        font-weight: 700;
    }

    .purchase-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        background: #fff;
    }

    .purchase-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        font-weight: 700;
    }

    .purchase-card-body {
        padding: 20px;
    }

    .form-label {
        font-weight: 600;
    }

    .table th {
        white-space: nowrap;
        font-size: .85rem;
        background: #f8fafc;
    }

    .table td {
        vertical-align: middle;
    }

    .item-select {
        min-width: 230px;
    }

    .item-number {
        min-width: 100px;
    }

    .total-panel {
        max-width: 460px;
        margin-left: auto;
        padding: 18px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 10px;
    }

    .total-grand {
        padding-top: 12px;
        margin-top: 12px;
        border-top: 1px solid #d1d5db;
        font-size: 1.1rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .purchase-card-body {
            padding: 14px;
        }

        .total-panel {
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="page-title mb-1">Edit Barang Masuk</h1>
            <div class="text-muted">
                Nomor transaksi:
                <strong>{{ $stockTransaction->transaction_number }}</strong>
            </div>
        </div>

        <a href="{{ route('stock-transactions.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">Terjadi kesalahan:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stock-transactions.update', $stockTransaction->id) }}"
          method="POST"
          id="editBarangMasukForm">
        @csrf
        @method('PUT')

        {{-- INFORMASI NOTA --}}
        <div class="purchase-card">
            <div class="purchase-card-header">
                <i class="bi bi-receipt me-2"></i>Informasi Nota Pembelian
            </div>

            <div class="purchase-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="purchase_responsible_id" class="form-label">
                            Penanggung Jawab Pembelian (PJ) <span class="text-danger">*</span>
                        </label>

                        <select name="purchase_responsible_id"
                                id="purchase_responsible_id"
                                class="form-select @error('purchase_responsible_id') is-invalid @enderror"
                                required>
                            <option value="">-- Pilih PJ Pembelian --</option>

                            @foreach ($responsibles as $responsible)
                                <option value="{{ $responsible->id }}"
                                    {{ (string) old('purchase_responsible_id', $stockTransaction->purchase_responsible_id) === (string) $responsible->id ? 'selected' : '' }}>
                                    {{ $responsible->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('purchase_responsible_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="purchase_note_number" class="form-label">
                            Nomor Nota Pembelian
                        </label>

                        <input type="text"
                               name="purchase_note_number"
                               id="purchase_note_number"
                               class="form-control @error('purchase_note_number') is-invalid @enderror"
                               value="{{ old('purchase_note_number', $stockTransaction->purchase_note_number) }}"
                               maxlength="100">

                        @error('purchase_note_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="vendor_name" class="form-label">
                            Nama Vendor / Supplier
                        </label>

                        <input type="text"
                               name="vendor_name"
                               id="vendor_name"
                               class="form-control @error('vendor_name') is-invalid @enderror"
                               value="{{ old('vendor_name', $stockTransaction->vendor_name) }}"
                               maxlength="150">

                        @error('vendor_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="transaction_date" class="form-label">
                            Tanggal Pembelian <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="transaction_date"
                               id="transaction_date"
                               class="form-control @error('transaction_date') is-invalid @enderror"
                               value="{{ old('transaction_date', $stockTransaction->transaction_date ? \Illuminate\Support\Carbon::parse($stockTransaction->transaction_date)->format('Y-m-d') : date('Y-m-d')) }}"
                               required>

                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="porter_cost" class="form-label">
                            Biaya Kuli / Angkut (Rp)
                        </label>

                        <input type="number"
                               name="porter_cost"
                               id="porter_cost"
                               class="form-control @error('porter_cost') is-invalid @enderror"
                               value="{{ old('porter_cost', $stockTransaction->porter_cost ?? 0) }}"
                               min="0"
                               step="0.01">

                        @error('porter_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Rincian Biaya BBM Kendaraan</label>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Kendaraan</th>
                                        <th>Nomor Polisi</th>
                                        <th>Nomor Struk BBM</th>
                                        <th style="width: 250px;">Nominal BBM (Rp)</th>
                                        <th class="text-center" style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody id="fuelCostDetailsBody"></tbody>
                            </table>
                        </div>

                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                id="addFuelCostRowButton">
                            <i class="bi bi-plus-circle"></i> Tambah Kendaraan
                        </button>

                        <input type="hidden"
                            name="fuel_cost"
                            id="fuel_cost"
                            value="{{ old('fuel_cost', $stockTransaction->fuel_cost ?? 0) }}">

                        @error('fuel_cost')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror

                        @error('fuel_cost_details')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="parking_cost" class="form-label">
                            Biaya Parkir (Rp)
                        </label>

                        <input type="number"
                            name="parking_cost"
                            id="parking_cost"
                            class="form-control @error('parking_cost') is-invalid @enderror"
                            value="{{ old('parking_cost', $stockTransaction->parking_cost ?? 0) }}"
                            min="0"
                            step="0.01">

                        @error('parking_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- BIAYA MASAK --}}
                    <div class="col-md-6">
                        <label for="cooking_cost" class="form-label">
                            Biaya Masak / Memasak (Rp)
                        </label>

                        <input type="number"
                            name="cooking_cost"
                            id="cooking_cost"
                            class="form-control @error('cooking_cost') is-invalid @enderror"
                            value="{{ old('cooking_cost', $stockTransaction->cooking_cost ?? 0) }}"
                            min="0"
                            step="0.01">

                        @error('cooking_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- BIAYA LAIN-LAIN KANTOR --}}
                    <div class="col-md-6">
                        <label for="other_cost" class="form-label">
                            Biaya Lain-lain Kantor (Rp)
                        </label>

                        <input type="number"
                            name="other_cost"
                            id="other_cost"
                            class="form-control @error('other_cost') is-invalid @enderror"
                            value="{{ old('other_cost', $stockTransaction->other_cost ?? 0) }}"
                            min="0"
                            step="0.01">

                        @error('other_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="purchase_discount_type" class="form-label">
                            Jenis Diskon Tambahan Nota
                            <span class="text-danger">*</span>
                        </label>

                        <select name="purchase_discount_type"
                                id="purchase_discount_type"
                                class="form-select @error('purchase_discount_type') is-invalid @enderror"
                                required>
                            <option value="amount"
                                {{ old('purchase_discount_type', $stockTransaction->purchase_discount_type ?? 'amount') === 'amount' ? 'selected' : '' }}>
                                Nominal (Rp)
                            </option>

                            <option value="percent"
                                {{ old('purchase_discount_type', $stockTransaction->purchase_discount_type ?? 'amount') === 'percent' ? 'selected' : '' }}>
                                Persentase (%)
                            </option>
                        </select>

                        @error('purchase_discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="purchase_discount_value" class="form-label">
                            Nilai Diskon Tambahan Nota
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="purchase_discount_value"
                               id="purchase_discount_value"
                               class="form-control @error('purchase_discount_value') is-invalid @enderror"
                               value="{{ old('purchase_discount_value', $stockTransaction->purchase_discount_value ?? 0) }}"
                               min="0"
                               max="9999999999999.99"
                               step="0.01"
                               required>

                        @error('purchase_discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-text">
                            Nominal berarti potongan rupiah. Persentase maksimal 100%.
                            Diskon tambahan nota dihitung setelah subtotal semua barang.
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>

                        <textarea name="notes"
                                  id="notes"
                                  rows="2"
                                  maxlength="2000"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $stockTransaction->notes) }}</textarea>

                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR BARANG --}}
        <div class="purchase-card">
            <div class="purchase-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>
                    <i class="bi bi-box-seam me-2"></i>Daftar Barang Masuk
                </span>

                <button type="button"
                        class="btn btn-sm btn-primary"
                        id="addItemButton">
                    <i class="bi bi-plus-lg"></i> Tambah Barang
                </button>
            </div>

            <div class="purchase-card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text"
                               id="itemSearch"
                               class="form-control"
                               placeholder="Cari nama atau kode barang...">
                    </div>

                    <div class="col-md-4">
                        <button type="button"
                                id="clearSearchButton"
                                class="btn btn-outline-secondary w-100">
                            Bersihkan Pencarian
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:50px">No.</th>
                                <th>Barang</th>
                                <th style="min-width:110px">Jumlah</th>
                                <th style="min-width:150px">Harga Satuan (Rp)</th>
                                <th style="min-width:170px">Diskon per Jenis Barang (Rp)</th>
                                <th style="min-width:160px">Subtotal (Rp)</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>

                <div class="text-muted small">
                    Subtotal dihitung dari (jumlah × harga satuan) − diskon satu kali
                    untuk jenis barang tersebut. Diskon tidak dikalikan dengan jumlah.
                </div>

                <div class="total-panel mt-4">
                    <div class="total-row">
                        <span>Subtotal Barang</span>
                        <strong id="goodsTotalLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Diskon Tambahan Nota</span>
                        <strong class="text-danger" id="purchaseDiscountLabel">− Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya Kuli / Angkut</span>
                        <strong id="porterCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya BBM</span>
                        <strong id="fuelCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya Parkir</span>
                        <strong id="parkingCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya Masak / Memasak</span>
                        <strong id="cookingCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row">
                        <span>Biaya Lain-lain Kantor</span>
                        <strong id="otherCostLabel">Rp0</strong>
                    </div>

                    <div class="total-row total-grand">
                        <span>Total Pembelian</span>
                        <strong id="grandTotalLabel">Rp0</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
            <a href="{{ route('stock-transactions.index') }}"
               class="btn btn-outline-secondary">
                Batal
            </a>

            <button type="submit" class="btn btn-success" id="submitButton">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = {{ Illuminate\Support\Js::from(
        $items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code ?? '',
            'unit' => $item->unit ?? '',
            'price' => (float) ($item->price ?? 0),
        ])->values()
    ) }};

    const existingItems = {{ Illuminate\Support\Js::from(
        old('items', $stockTransaction->details->map(fn ($detail) => [
            'item_id' => $detail->item_id,
            'quantity' => $detail->quantity,
            'unit_price' => $detail->unit_price,
            'discount' => $detail->discount ?? 0,
            'unit' => $detail->unit,
        ])->values()->all())
    ) }};

    const existingFuelCostDetails = {{ Illuminate\Support\Js::from(
        old('fuel_cost_details', $stockTransaction->fuel_cost_details ?? [])
    ) }};

    const tbody = document.getElementById('itemsTableBody');
    const form = document.getElementById('editBarangMasukForm');
    const porterInput = document.getElementById('porter_cost');
    const fuelInput = document.getElementById('fuel_cost');
    const parkingInput = document.getElementById('parking_cost');
    const cookingInput = document.getElementById('cooking_cost');
    const otherCostInput = document.getElementById('other_cost');
    const discountTypeInput = document.getElementById('purchase_discount_type');
    const discountValueInput = document.getElementById('purchase_discount_value');
    const itemSearch = document.getElementById('itemSearch');

    let rowIndex = 0;

    const formatNumber = value => new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(Number(value) || 0);

    const formatRupiah = value => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2
    }).format(Number(value) || 0);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function getItem(itemId) {
        return items.find(item => String(item.id) === String(itemId));
    }

    function updateRowNumbers() {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            const number = row.querySelector('.row-number');

            if (number) {
                number.textContent = index + 1;
            }
        });
    }

    function updateItemOptions() {
        const selectedIds = Array.from(tbody.querySelectorAll('.item-select'))
            .map(select => select.value)
            .filter(Boolean);

        tbody.querySelectorAll('.item-select').forEach(select => {
            const currentValue = select.value;

            Array.from(select.options).forEach(option => {
                if (!option.value) {
                    return;
                }

                option.disabled =
                    selectedIds.includes(option.value) &&
                    option.value !== currentValue;
            });
        });
    }

    /*
     * Subtotal satu baris:
     * (jumlah × harga satuan) - diskon satu kali.
     *
     * Contoh:
     * 10 × Rp7.000 = Rp70.000
     * Rp70.000 - Rp1.000 = Rp69.000
     */
    function calculateRow(row) {
        const quantity = Math.max(
            0,
            Number(row.querySelector('.quantity-input').value) || 0
        );

        const price = Math.max(
            0,
            Number(row.querySelector('.price-input').value) || 0
        );

        const discount = Math.max(
            0,
            Number(row.querySelector('.discount-input').value) || 0
        );

        const subtotalBeforeDiscount = quantity * price;
        const safeDiscount = Math.min(discount, subtotalBeforeDiscount);
        const subtotal = Math.max(0, subtotalBeforeDiscount - safeDiscount);

        row.querySelector('.subtotal-input').value = formatNumber(subtotal);

        return subtotal;
    }

    function calculateTotals() {
        let goodsTotal = 0;

        tbody.querySelectorAll('tr').forEach(row => {
            goodsTotal += calculateRow(row);
        });

        const type = discountTypeInput.value;
        const value = Math.max(
            0,
            Number(discountValueInput.value) || 0
        );

        let discountAmount = type === 'percent'
            ? goodsTotal * value / 100
            : value;

        discountAmount = Math.min(
            goodsTotal,
            Math.max(0, discountAmount)
        );

        const porterCost = Math.max(
            0,
            Number(porterInput.value) || 0
        );

        const fuelCost = Math.max(
            0,
            Number(fuelInput.value) || 0
        );

        const parkingCost = Math.max(
            0,
            Number(parkingInput.value) || 0
        );

        const cookingCost = Math.max(
            0,
            Number(cookingInput.value) || 0
        );

        const otherCost = Math.max(
            0,
            Number(otherCostInput.value) || 0
        );

        const grandTotal = Math.max(
            0,
            goodsTotal - discountAmount
                + porterCost
                + fuelCost
                + parkingCost
                + cookingCost
                + otherCost
        );

        document.getElementById('goodsTotalLabel').textContent =
            formatRupiah(goodsTotal);

        document.getElementById('purchaseDiscountLabel').textContent =
            '− ' + formatRupiah(discountAmount);

        document.getElementById('porterCostLabel').textContent =
            formatRupiah(porterCost);
            
        document.getElementById('fuelCostLabel').textContent =
            formatRupiah(fuelCost);

        document.getElementById('parkingCostLabel').textContent =
            formatRupiah(parkingCost);

        document.getElementById('cookingCostLabel').textContent =
            formatRupiah(cookingCost);

        document.getElementById('otherCostLabel').textContent =
            formatRupiah(otherCost);

        document.getElementById('grandTotalLabel').textContent =
            formatRupiah(grandTotal);

        updateItemOptions();
    }

    // Mengelola rincian BBM kendaraan.
    let fuelCostRowIndex = 0;

    function addFuelCostRow(data = {}) {
        const fuelBody = document.getElementById('fuelCostDetailsBody');
        const index = fuelCostRowIndex++;

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <input type="text"
                    name="fuel_cost_details[${index}][vehicle_name]"
                    class="form-control fuel-vehicle-name"
                    maxlength="100"
                    placeholder="Contoh: Mobil Operasional">
            </td>

            <td>
                <input type="text"
                    name="fuel_cost_details[${index}][vehicle_plate]"
                    class="form-control fuel-vehicle-plate"
                    maxlength="30"
                    placeholder="Contoh: AB 1234 CD">
            </td>

            <td>
                <input type="text"
                    name="fuel_cost_details[${index}][receipt_number]"
                    class="form-control fuel-vehicle-receipt"
                    maxlength="100"
                    placeholder="Nomor struk BBM">
            </td>

            <td>
                <input type="number"
                    name="fuel_cost_details[${index}][amount]"
                    class="form-control fuel-vehicle-amount"
                    min="0"
                    max="9999999999999.99"
                    step="0.01"
                    value="0">
            </td>

            <td class="text-center">
                <button type="button"
                    class="btn btn-sm btn-outline-danger remove-fuel-row">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </td>
        `;

        row.querySelector('.fuel-vehicle-name').value =
            data.vehicle_name ?? '';

        row.querySelector('.fuel-vehicle-plate').value =
            data.vehicle_plate ?? '';

        row.querySelector('.fuel-vehicle-receipt').value =
            data.receipt_number ?? '';

        row.querySelector('.fuel-vehicle-amount').value =
            data.amount ?? 0;

        fuelBody.appendChild(row);
    }

    function updateFuelCostTotal() {
        let total = 0;

        document.querySelectorAll('.fuel-vehicle-amount')
            .forEach(input => {
                const amount = Number(input.value);

                if (Number.isFinite(amount) && amount > 0) {
                    total += amount;
                }
            });

        // Total rincian BBM masuk ke input fuel_cost.
        fuelInput.value = total.toFixed(2);

        // Perbarui total nota menggunakan fungsi yang sudah ada.
        calculateTotals();
    }

    // Tombol tambah kendaraan.
    document.getElementById('addFuelCostRowButton')
        .addEventListener('click', function () {
            addFuelCostRow();
            updateFuelCostTotal();
        });

    // Hitung ulang ketika nominal BBM berubah.
    document.getElementById('fuelCostDetailsBody')
        .addEventListener('input', function (event) {
            if (event.target.classList.contains('fuel-vehicle-amount')) {
                updateFuelCostTotal();
            }
        });

    // Hapus kendaraan dan hitung ulang total BBM.
    document.getElementById('fuelCostDetailsBody')
        .addEventListener('click', function (event) {
            const button = event.target.closest('.remove-fuel-row');

            if (!button) {
                return;
            }

            button.closest('tr').remove();

            updateFuelCostTotal();
        });

    function validateRowDiscount(row) {
        const quantity = Number(
            row.querySelector('.quantity-input').value
        );

        const price = Number(
            row.querySelector('.price-input').value
        );

        const discountInput = row.querySelector('.discount-input');
        const discount = Number(discountInput.value) || 0;

        const subtotalBeforeDiscount = Math.max(0, quantity * price);

        if (discount > subtotalBeforeDiscount) {
            discountInput.setCustomValidity(
                'Diskon tidak boleh melebihi subtotal jumlah barang.'
            );
        } else {
            discountInput.setCustomValidity('');
        }
    }

    function addRow(data = {}) {
        const index = rowIndex++;

        const options = items.map(item => {
            const selected = String(data.item_id ?? '') === String(item.id)
                ? 'selected'
                : '';

            const label = `${item.code ? item.code + ' - ' : ''}${item.name}`;

            return `
                <option value="${escapeHtml(item.id)}"
                        data-unit="${escapeHtml(item.unit)}"
                        ${selected}>
                    ${escapeHtml(label)}
                </option>
            `;
        }).join('');

        const row = document.createElement('tr');

        row.innerHTML = `
            <td class="text-center row-number"></td>

            <td>
                <select name="items[${index}][item_id]"
                        class="form-select item-select"
                        required>
                    <option value="">-- Pilih Barang --</option>
                    ${options}
                </select>

                <input type="hidden"
                       name="items[${index}][unit]"
                       class="unit-input"
                       value="${escapeHtml(data.unit ?? '')}">
            </td>

            <td>
                <input type="number"
                       name="items[${index}][quantity]"
                       class="form-control item-number quantity-input"
                       value="${escapeHtml(data.quantity ?? 1)}"
                       min="0"
                       step="0.001"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][unit_price]"
                       class="form-control item-number price-input"
                       value="${escapeHtml(data.unit_price ?? 0)}"
                       min="0"
                       step="0.01"
                       required>
            </td>

            <td>
                <input type="number"
                       name="items[${index}][discount]"
                       class="form-control item-number discount-input"
                       value="${escapeHtml(data.discount ?? 0)}"
                       min="0"
                       step="0.01">
            </td>

            <td>
                <input type="text"
                       class="form-control subtotal-input text-end"
                       value="0,00"
                       readonly>
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger remove-row"
                        title="Hapus barang">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        const select = row.querySelector('.item-select');
        const priceInput = row.querySelector('.price-input');
        const unitInput = row.querySelector('.unit-input');

        select.addEventListener('change', function () {
            const selectedItem = getItem(select.value);

            if (selectedItem) {
                priceInput.value = selectedItem.price;
                unitInput.value = selectedItem.unit || '';
            } else {
                unitInput.value = '';
            }

            validateRowDiscount(row);
            calculateTotals();
        });

        row.querySelectorAll('.quantity-input, .price-input, .discount-input')
            .forEach(input => {
                input.addEventListener('input', function () {
                    validateRowDiscount(row);
                    calculateTotals();
                });
            });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            updateRowNumbers();
            calculateTotals();
        });

        validateRowDiscount(row);
        updateRowNumbers();
        calculateTotals();
    }

    document.getElementById('addItemButton').addEventListener('click', function () {
        addRow();
    });

    porterInput.addEventListener('input', calculateTotals);
    fuelInput.addEventListener('input', calculateTotals);
    parkingInput.addEventListener('input', calculateTotals);
    cookingInput.addEventListener('input', calculateTotals);
    otherCostInput.addEventListener('input', calculateTotals);

    discountTypeInput.addEventListener('change', calculateTotals);
    discountValueInput.addEventListener('input', calculateTotals);

    // Pencarian memfilter baris barang yang sudah ada.
    itemSearch.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        tbody.querySelectorAll('tr').forEach(row => {
            const item = getItem(row.querySelector('.item-select').value);

            const label = item
                ? `${item.name} ${item.code}`.toLowerCase()
                : '';

            row.style.display = !keyword || label.includes(keyword)
                ? ''
                : 'none';
        });
    });

    document.getElementById('clearSearchButton').addEventListener('click', function () {
        itemSearch.value = '';

        tbody.querySelectorAll('tr').forEach(row => {
            row.style.display = '';
        });
    });

    // Validasi sebelum form dikirim ke server.
    form.addEventListener('submit', function (event) {
        const rows = Array.from(tbody.querySelectorAll('tr'));

        if (rows.length === 0) {
            event.preventDefault();
            alert('Tambahkan minimal satu barang.');
            return;
        }

        const selectedIds = rows.map(row => {
            return row.querySelector('.item-select').value;
        });

        if (selectedIds.some(id => !id)) {
            event.preventDefault();
            alert('Pilih barang untuk setiap baris.');
            return;
        }

        if (new Set(selectedIds).size !== selectedIds.length) {
            event.preventDefault();
            alert('Barang yang sama tidak boleh ditambahkan dua kali.');
            return;
        }

        for (const row of rows) {
            const select = row.querySelector('.item-select');
            const quantity = Number(row.querySelector('.quantity-input').value);
            const price = Number(row.querySelector('.price-input').value);
            const discount = Number(row.querySelector('.discount-input').value) || 0;

            const subtotalBeforeDiscount = quantity * price;

            if (
                !select.value ||
                !Number.isFinite(quantity) ||
                !Number.isFinite(price) ||
                !Number.isFinite(discount) ||
                quantity <= 0 ||
                price < 0 ||
                discount < 0 ||
                discount > subtotalBeforeDiscount
            ) {
                event.preventDefault();

                alert(
                    'Periksa barang, jumlah, harga satuan, dan diskon. ' +
                    'Jumlah harus lebih dari nol dan diskon tidak boleh melebihi subtotal jumlah barang.'
                );

                return;
            }
        }

        const type = discountTypeInput.value;
        const value = Number(discountValueInput.value);

        if (!type || !Number.isFinite(value) || value < 0) {
            event.preventDefault();
            alert('Jenis dan nilai diskon tambahan nota wajib diisi dengan benar.');
            return;
        }

        if (type === 'percent' && value > 100) {
            event.preventDefault();
            alert('Diskon persentase tidak boleh lebih dari 100%.');
            return;
        }

        const goodsTotal = rows.reduce((sum, row) => {
            return sum + calculateRow(row);
        }, 0);

        const discountAmount = type === 'percent'
            ? goodsTotal * value / 100
            : value;

        if (discountAmount > goodsTotal) {
            event.preventDefault();
            alert('Diskon tambahan nota tidak boleh melebihi subtotal barang.');
        }
    });

        // Pulihkan detail barang saat halaman dibuka atau validasi gagal.
        if (Array.isArray(existingItems) && existingItems.length > 0) {
            existingItems.forEach(item => addRow(item));
        } else {
            addRow();
        }

        // Pulihkan rincian BBM kendaraan.
        if (
            Array.isArray(existingFuelCostDetails) &&
            existingFuelCostDetails.length > 0
        ) {
            existingFuelCostDetails.forEach(detail => {
                addFuelCostRow(detail);
            });
        } else if (Number(fuelInput.value) > 0) {
            // Data transaksi lama yang hanya memiliki total BBM.
            addFuelCostRow({
                vehicle_name: '',
                vehicle_plate: '',
                receipt_number: '',
                amount: fuelInput.value
            });
        } else {
            addFuelCostRow();
        }

        // Hitung total BBM dan total nota saat halaman dibuka.
        updateFuelCostTotal();
    });
</script>
@endpush