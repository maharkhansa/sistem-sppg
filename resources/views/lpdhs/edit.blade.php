@extends('layouts.app')

@section('title', 'Edit LPDH')

@section('content')
<div class="container-fluid py-4 lpdh-edit">

    <style>
        .lpdh-edit .lpdh-card {
            border: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .lpdh-edit .lpdh-card-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            font-weight: 700;
        }

        .lpdh-edit .lpdh-card-body {
            padding: 20px;
        }

        .lpdh-edit .lpdh-hpe-info {
            display: block;
            margin-top: 6px;
            color: #2563eb;
            font-size: 12px;
        }

        .lpdh-edit .lpdh-result {
            margin-top: 8px;
            padding: 10px;
            border-radius: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            font-weight: 600;
            font-size: 13px;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit LPDH</h3>
            <p class="text-muted mb-0">
                Laporan Penggunaan Dana Harian SPPG
            </p>
        </div>

        <a href="{{ route('lpdhs.show', $lpdh) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
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

    <form action="{{ route('lpdhs.update', $lpdh) }}"
          method="POST" id="lpdhEditForm">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-4 lpdh-card">
            <div class="lpdh-card-header">A. Identitas Pelayanan</div>

            <div class="lpdh-card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="kitchen_id" class="form-label">
                            SPPG / Dapur <span class="text-danger">*</span>
                        </label>

                        <select name="kitchen_id" id="kitchen_id"
                                class="form-select" required>
                            <option value="">-- Pilih SPPG --</option>

                            @foreach ($kitchens as $kitchen)
                                <option value="{{ $kitchen->id }}"
                                    {{ old('kitchen_id', $lpdh->kitchen_id) == $kitchen->id ? 'selected' : '' }}>
                                    {{ $kitchen->name }} - {{ $kitchen->id_sppg }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="service_date" class="form-label">
                            Tanggal Pelayanan <span class="text-danger">*</span>
                        </label>

                        <input type="date" name="service_date" id="service_date"
                               class="form-control"
                               value="{{ old('service_date', \Illuminate\Support\Carbon::parse($lpdh->service_date)->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="day_status_display" class="form-label">HPE</label>

                        <input type="text" id="day_status_display"
                               class="form-control bg-light"
                               readonly placeholder="Otomatis berdasarkan tanggal">

                        <input type="hidden" name="day_status" id="day_status">

                        <small class="lpdh-hpe-info">
                            HPE Ke-1 dimulai pada tanggal 5 Oktober 2026.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="beneficiaries_count" class="form-label">
                            Jumlah Penerima Manfaat
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="beneficiaries_count"
                               id="beneficiaries_count" min="0" step="1"
                               class="form-control"
                               value="{{ old('beneficiaries_count', $lpdh->beneficiaries_count) }}"
                               required>

                        <small class="text-muted">
                            Insentif dihitung Rp2.000 per penerima manfaat.
                        </small>
                    </div>

                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 lpdh-card">
            <div class="lpdh-card-header">B. Penggunaan Dana dan Insentif</div>

            <div class="lpdh-card-body">

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Total bahan baku dan operasional diambil dari Invoice
                    berdasarkan SPPG dan tanggal pelayanan.
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="raw_material_expenses" class="form-label">
                            Belanja Bahan Baku Pangan (Rp)
                        </label>

                        <input type="number" name="raw_material_expenses"
                               id="raw_material_expenses" min="0" step="0.01"
                               class="form-control bg-light"
                               value="{{ old('raw_material_expenses', $lpdh->raw_material_expenses) }}"
                               readonly required>

                        <div id="raw-material-result" class="lpdh-result">
                            Mengambil total bahan baku...
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="operational_expenses" class="form-label">
                            Biaya Operasional (Rp)
                        </label>

                        <input type="number" name="operational_expenses"
                               id="operational_expenses" min="0" step="0.01"
                               class="form-control bg-light"
                               value="{{ old('operational_expenses', $lpdh->operational_expenses) }}"
                               readonly required>

                        <div id="operational-result" class="lpdh-result">
                            Mengambil total operasional...
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="incentive_calculated" class="form-label">
                            Insentif Hasil Perhitungan (Rp)
                        </label>

                        <input type="number" name="incentive_calculated"
                               id="incentive_calculated" min="0" step="0.01"
                               class="form-control bg-light"
                               value="{{ old('incentive_calculated', $lpdh->incentive_calculated) }}"
                               readonly required>
                    </div>

                    <div class="col-md-6">
                        <label for="incentive_paid" class="form-label">
                            Insentif Dibayarkan ke Mitra/Yayasan (Rp)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="incentive_paid"
                               id="incentive_paid" min="0" step="0.01"
                               class="form-control"
                               value="{{ old('incentive_paid', $lpdh->incentive_paid) }}"
                               required>
                    </div>

                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 lpdh-card">
            <div class="lpdh-card-header">C. Saldo dan Pemeriksaan</div>

            <div class="lpdh-card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="va_final_balance" class="form-label">
                            Saldo Akhir VA (Rp)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="va_final_balance"
                               id="va_final_balance" min="0" step="0.01"
                               class="form-control"
                               value="{{ old('va_final_balance', $lpdh->va_final_balance) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="topup_proposal" class="form-label">
                            Usulan Top Up (Rp)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="topup_proposal"
                               id="topup_proposal" min="0" step="0.01"
                               class="form-control"
                               value="{{ old('topup_proposal', $lpdh->topup_proposal) }}"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="inspection_result" class="form-label">
                            Hasil Pemeriksaan
                        </label>

                        <select name="inspection_result" id="inspection_result"
                                class="form-select">
                            <option value="">-- Pilih Hasil --</option>

                            <option value="Sesuai"
                                {{ old('inspection_result', $lpdh->inspection_result) === 'Sesuai' ? 'selected' : '' }}>
                                Sesuai
                            </option>

                            <option value="Perlu Perbaikan"
                                {{ old('inspection_result', $lpdh->inspection_result) === 'Perlu Perbaikan' ? 'selected' : '' }}>
                                Perlu Perbaikan
                            </option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 p-3 border-top">
                <a href="{{ route('lpdhs.show', $lpdh) }}"
                   class="btn btn-light border">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kitchenSelect = document.getElementById('kitchen_id');
    const serviceDate = document.getElementById('service_date');
    const dayStatusDisplay = document.getElementById('day_status_display');
    const dayStatus = document.getElementById('day_status');

    const beneficiariesInput = document.getElementById('beneficiaries_count');
    const incentiveCalculated = document.getElementById('incentive_calculated');

    const rawMaterial = document.getElementById('raw_material_expenses');
    const operational = document.getElementById('operational_expenses');

    const rawMaterialResult = document.getElementById('raw-material-result');
    const operationalResult = document.getElementById('operational-result');

    function calculateHPE(dateValue) {
        if (!dateValue) {
            dayStatusDisplay.value = '';
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

        dayStatusDisplay.value = hpe;
        dayStatus.value = hpe;
    }

    function calculateIncentive() {
        const beneficiaries = Number(beneficiariesInput.value || 0);
        incentiveCalculated.value = beneficiaries * 2000;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 2
        }).format(Number(number || 0));
    }

    async function loadInvoiceTotal() {
        const kitchenId = kitchenSelect.value;
        const date = serviceDate.value;

        if (!kitchenId || !date) {
            rawMaterial.value = '0';
            operational.value = '0';
            rawMaterialResult.textContent = 'Pilih SPPG dan tanggal pelayanan.';
            operationalResult.textContent = 'Pilih SPPG dan tanggal pelayanan.';
            return;
        }

        rawMaterialResult.textContent = 'Mengambil total bahan baku...';
        operationalResult.textContent = 'Mengambil total operasional...';

        const url = @json(route('lpdhs.invoice-total'));

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

            const rawTotal = Number(data.raw_material_total || 0);
            const operationalTotal = Number(data.operational_total || 0);

            rawMaterial.value = rawTotal.toFixed(2);
            operational.value = operationalTotal.toFixed(2);

            rawMaterialResult.textContent =
                'Total bahan baku: ' + formatRupiah(rawTotal);

            operationalResult.textContent =
                'Total operasional: ' + formatRupiah(operationalTotal);
        } catch (error) {
            rawMaterialResult.textContent =
                'Gagal mengambil total bahan baku.';

            operationalResult.textContent =
                'Gagal mengambil total operasional.';

            console.error(error);
        }
    }

    kitchenSelect.addEventListener('change', loadInvoiceTotal);

    serviceDate.addEventListener('change', function () {
        calculateHPE(this.value);
        loadInvoiceTotal();
    });

    beneficiariesInput.addEventListener('input', calculateIncentive);

    calculateHPE(serviceDate.value);
    calculateIncentive();
    loadInvoiceTotal();
});
</script>
@endpush