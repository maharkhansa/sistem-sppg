@extends('layouts.app')

@section('title', 'Tambah LPDH')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tambah LPDH</h3>
            <p class="text-muted mb-0">Laporan Penggunaan Dana Harian</p>
        </div>

        <a href="{{ route('lpdhs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Data belum dapat disimpan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lpdhs.store') }}" method="POST" id="lpdhForm">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">A. Data Pelayanan</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="kitchen_id" class="form-label">
                            Dapur SPPG <span class="text-danger">*</span>
                        </label>

                        <select name="kitchen_id" id="kitchen_id"
                                class="form-select @error('kitchen_id') is-invalid @enderror"
                                required>
                            <option value="">-- Pilih Dapur SPPG --</option>

                            @foreach ($kitchens as $kitchen)
                                <option value="{{ $kitchen->id }}"
                                    {{ old('kitchen_id') == $kitchen->id ? 'selected' : '' }}>
                                    {{ $kitchen->id_sppg }} - {{ $kitchen->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('kitchen_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="service_date" class="form-label">
                            Tanggal Pelayanan <span class="text-danger">*</span>
                        </label>

                        <input type="date" name="service_date" id="service_date"
                               class="form-control @error('service_date') is-invalid @enderror"
                               value="{{ old('service_date', date('Y-m-d')) }}"
                               required>

                        @error('service_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="hpe_display" class="form-label">HPE</label>

                        <input type="text" id="hpe_display"
                               class="form-control bg-light"
                               readonly placeholder="Otomatis berdasarkan tanggal">

                        <input type="hidden" name="day_status" id="day_status"
                               value="{{ old('day_status') }}">

                        <small class="text-muted">
                            HPE Ke-1 dimulai tanggal 5 Oktober 2026.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="beneficiaries_count" class="form-label">
                            Jumlah Penerima Manfaat <span class="text-danger">*</span>
                        </label>

                        <input type="number" min="0" step="1"
                               name="beneficiaries_count" id="beneficiaries_count"
                               class="form-control"
                               value="{{ old('beneficiaries_count', 0) }}"
                               required>

                        <small class="text-muted">
                            Perhitungan insentif Rp2.000 per penerima manfaat.
                        </small>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">B. Biaya dan Insentif</h5>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Total belanja diambil dari Invoice berdasarkan dapur dan
                    tanggal pelayanan. Bahan baku berasal dari Koperasi Sumber
                    Rejeki, Zenzie, dan Gemilang. Biaya operasional mengikuti
                    klasifikasi pada controller.
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="raw_material_expenses" class="form-label">
                            Belanja Bahan Baku (Rp)
                        </label>

                        <input type="number" min="0" step="0.01"
                               name="raw_material_expenses"
                               id="raw_material_expenses"
                               class="form-control bg-light"
                               value="{{ old('raw_material_expenses', 0) }}"
                               readonly required>

                        <small class="text-muted">
                            Dihitung otomatis dari Invoice.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="operational_expenses" class="form-label">
                            Total Biaya Operasional (Rp)
                        </label>

                        <input type="text" id="operational_expenses"
                               class="form-control bg-light"
                               value="Rp 0" readonly>

                        <small class="text-muted">
                            Termasuk Top Fast dan biaya operasional yang
                            terdaftar sesuai perhitungan controller.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="incentive_calculated" class="form-label">
                            Insentif Hasil Perhitungan (Rp)
                        </label>

                        <input type="number" min="0" step="0.01"
                               name="incentive_calculated"
                               id="incentive_calculated"
                               class="form-control bg-light"
                               value="{{ old('incentive_calculated', 0) }}"
                               readonly required>
                    </div>

                    <div class="col-md-6">
                        <label for="incentive_paid" class="form-label">
                            Insentif Dibayarkan (Rp)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" min="0" step="0.01"
                               name="incentive_paid" id="incentive_paid"
                               class="form-control"
                               value="{{ old('incentive_paid', 0) }}"
                               required>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">C. Saldo dan Pemeriksaan</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="va_final_balance" class="form-label">
                            Saldo Akhir VA (Rp) <span class="text-danger">*</span>
                        </label>

                        <input type="number" min="0" step="0.01"
                               name="va_final_balance" id="va_final_balance"
                               class="form-control"
                               value="{{ old('va_final_balance', 0) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="topup_proposal" class="form-label">
                            Usulan Top Up (Rp) <span class="text-danger">*</span>
                        </label>

                        <input type="number" min="0" step="0.01"
                               name="topup_proposal" id="topup_proposal"
                               class="form-control"
                               value="{{ old('topup_proposal', 0) }}"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="inspection_result" class="form-label">
                            Hasil Pemeriksaan
                        </label>

                        <textarea name="inspection_result" id="inspection_result"
                                  rows="3" maxlength="100" class="form-control"
                                  placeholder="Masukkan hasil pemeriksaan jika ada">{{ old('inspection_result') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('lpdhs.index') }}" class="btn btn-light border">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan LPDH
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kitchenSelect = document.getElementById('kitchen_id');
    const serviceDate = document.getElementById('service_date');
    const hpeDisplay = document.getElementById('hpe_display');
    const dayStatus = document.getElementById('day_status');
    const beneficiariesInput = document.getElementById('beneficiaries_count');
    const incentiveCalculated = document.getElementById('incentive_calculated');
    const rawMaterialInput = document.getElementById('raw_material_expenses');
    const operationalInput = document.getElementById('operational_expenses');

    function calculateHPE(dateValue) {
        if (!dateValue) {
            hpeDisplay.value = '';
            dayStatus.value = '';
            return;
        }

        const startDate = new Date('2026-10-05T00:00:00');
        const selectedDate = new Date(dateValue + 'T00:00:00');

        const difference = Math.floor(
            (selectedDate.getTime() - startDate.getTime()) / 86400000
        ) + 1;

        const hpe = difference < 1
            ? 'Belum HPE'
            : 'HPE ke-' + difference;

        hpeDisplay.value = hpe;
        dayStatus.value = hpe;
    }

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function calculateIncentive() {
        const beneficiaries = Number(beneficiariesInput.value || 0);
        incentiveCalculated.value = beneficiaries * 2000;
    }

    async function loadInvoiceTotals() {
        if (!kitchenSelect.value || !serviceDate.value) {
            rawMaterialInput.value = 0;
            operationalInput.value = formatRupiah(0);
            return;
        }

        operationalInput.value = 'Memuat...';

        try {
            const response = await fetch(
                @json(route('lpdhs.invoice-total')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({
                        kitchen_id: kitchenSelect.value,
                        service_date: serviceDate.value
                    })
                }
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil data Invoice.');
            }

            const data = await response.json();

            rawMaterialInput.value = Number(
                data.raw_material_total || 0
            ).toFixed(2);

            operationalInput.value = formatRupiah(
                data.operational_total || 0
            );
        } catch (error) {
            operationalInput.value = 'Gagal memuat data';
            console.error(error);
        }
    }

    beneficiariesInput.addEventListener('input', calculateIncentive);

    kitchenSelect.addEventListener('change', loadInvoiceTotals);

    serviceDate.addEventListener('change', function () {
        calculateHPE(this.value);
        loadInvoiceTotals();
    });

    calculateHPE(serviceDate.value);
    calculateIncentive();
    loadInvoiceTotals();
});
</script>
@endpush