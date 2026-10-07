<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Edit LPDH - SPPG</title>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 20px;
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f4f6;
        color: #111827;
        font-size: 13px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    .top-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    h1 {
        margin: 0;
        font-size: 20px;
    }

    .subtitle {
        color: #6b7280;
        margin-top: 5px;
    }

    .btn {
        display: inline-block;
        padding: 9px 14px;
        border-radius: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 13px;
    }

    .btn-back {
        background: #6b7280;
        color: white;
    }

    .btn-primary {
        background: #2563eb;
        color: white;
    }

    .card {
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .card-header {
        padding: 13px 16px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: bold;
        font-size: 14px;
    }

    .card-body {
        padding: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-group {
        margin-bottom: 5px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        font-size: 12px;
    }

    .required {
        color: #dc2626;
    }

    input,
    select {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 13px;
        background: white;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: #2563eb;
    }

    input[readonly] {
        background: #f3f4f6;
    }

    .help-text {
        margin-top: 5px;
        color: #6b7280;
        font-size: 11px;
    }

    .hpe-info {
        margin-top: 6px;
        color: #2563eb;
        font-size: 11px;
    }

    .invoice-result {
        margin-top: 8px;
        padding: 9px 10px;
        border-radius: 5px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        font-weight: bold;
    }

    .alert {
        padding: 10px 14px;
        margin-bottom: 15px;
        border-radius: 5px;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 18px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 700px) {

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .top-actions {
            align-items: flex-start;
            gap: 10px;
            flex-direction: column;
        }
    }
</style>
```

</head>

<body>

<div class="container">

```
<!-- HEADER -->

<div class="top-actions">

    <div>
        <h1>Edit LPDH</h1>

        <div class="subtitle">
            Laporan Penggunaan Dana Harian SPPG
        </div>
    </div>

    <a href="{{ route('lpdhs.show', $lpdh) }}"
       class="btn btn-back">
        ← Kembali
    </a>

</div>


<!-- ERROR -->

@if($errors->any())

    <div class="alert alert-error">

        <strong>
            Periksa kembali data berikut:
        </strong>

        <ul style="margin-bottom:0;">

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


<form action="{{ route('lpdhs.update', $lpdh) }}"
      method="POST">

    @csrf
    @method('PUT')


    <!-- IDENTITAS -->

    <div class="card">

        <div class="card-header">
            Identitas Pelayanan
        </div>

        <div class="card-body">

            <div class="form-grid">


                <!-- SPPG -->

                <div class="form-group">

                    <label>
                        SPPG / Dapur
                        <span class="required">*</span>
                    </label>

                    <select name="kitchen_id"
                            id="kitchen_id"
                            required>

                        <option value="">
                            -- Pilih SPPG --
                        </option>

                        @foreach($kitchens as $kitchen)

                            <option value="{{ $kitchen->id }}"
                                {{ old('kitchen_id', $lpdh->kitchen_id) == $kitchen->id ? 'selected' : '' }}>

                                {{ $kitchen->name }} - {{ $kitchen->id_sppg }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <!-- TANGGAL -->

                <div class="form-group">

                    <label>
                        Tanggal Pelayanan
                        <span class="required">*</span>
                    </label>

                    <input type="date"
                           name="service_date"
                           id="service_date"
                           value="{{ old('service_date', $lpdh->service_date->format('Y-m-d')) }}"
                           required>

                </div>


                <!-- STATUS HARI -->

                <div class="form-group">

                    <label>
                        Status Hari
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           id="day_status_display"
                           readonly
                           placeholder="Otomatis berdasarkan tanggal">

                    <input type="hidden"
                           name="day_status"
                           id="day_status">

                    <div class="hpe-info">
                        HPE ke-1 dimulai pada tanggal 5 Oktober 2026.
                    </div>

                </div>


                <!-- PENERIMA MANFAAT -->

                <div class="form-group">

                    <label>
                        Jumlah Penerima Manfaat
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="beneficiaries_count"
                           min="0"
                           value="{{ old('beneficiaries_count', $lpdh->beneficiaries_count) }}"
                           required>

                </div>

            </div>

        </div>

    </div>


    <!-- PENGGUNAAN DANA -->

    <div class="card">

        <div class="card-header">
            Penggunaan Dana
        </div>

        <div class="card-body">

            <div class="form-grid">


                <!-- BAHAN BAKU -->

                <div class="form-group">

                    <label>
                        Belanja Bahan Baku Pangan
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="raw_material_expenses"
                           id="raw_material_expenses"
                           min="0"
                           step="0.01"
                           value="{{ old('raw_material_expenses', $lpdh->raw_material_expenses) }}"
                           readonly
                           required>

                    <div id="raw-material-result"
                         class="invoice-result">

                        Mengambil total bahan baku...

                    </div>

                    <div class="help-text">
                        Nilai diambil otomatis dari Invoice berdasarkan SPPG dan tanggal pelayanan.
                    </div>

                </div>


                <!-- OPERASIONAL -->

                <div class="form-group">

                    <label>
                        Biaya Operasional
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="operational_expenses"
                           id="operational_expenses"
                           min="0"
                           step="0.01"
                           value="{{ old('operational_expenses', $lpdh->operational_expenses) }}"
                           readonly
                           required>

                    <div id="operational-result"
                         class="invoice-result">

                        Mengambil total operasional...

                    </div>

                </div>


                <!-- INSENTIF DIHITUNG -->

                <div class="form-group">

                    <label>
                        Insentif Dihitung
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="incentive_calculated"
                           min="0"
                           step="0.01"
                           value="{{ old('incentive_calculated', $lpdh->incentive_calculated) }}"
                           required>

                </div>


                <!-- INSENTIF DIBAYARKAN -->

                <div class="form-group">

                    <label>
                        Insentif Dibayarkan ke Mitra/Yayasan
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="incentive_paid"
                           min="0"
                           step="0.01"
                           value="{{ old('incentive_paid', $lpdh->incentive_paid) }}"
                           required>

                </div>


                <!-- SALDO VA -->

                <div class="form-group">

                    <label>
                        Saldo Akhir VA
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="va_final_balance"
                           min="0"
                           step="0.01"
                           value="{{ old('va_final_balance', $lpdh->va_final_balance) }}"
                           required>

                </div>


                <!-- TOP UP -->

                <div class="form-group">

                    <label>
                        Usulan Top Up
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="topup_proposal"
                           min="0"
                           step="0.01"
                           value="{{ old('topup_proposal', $lpdh->topup_proposal) }}"
                           required>

                </div>


                <!-- HASIL PERIKSA -->

                <div class="form-group full">

                    <label>
                        Hasil Daftar Periksa
                    </label>

                    <select name="inspection_result">

                        <option value="">
                            -- Pilih Hasil --
                        </option>

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


        <!-- ACTION -->

        <div class="form-actions">

            <a href="{{ route('lpdhs.show', $lpdh) }}"
               class="btn btn-back">
                Batal
            </a>

            <button type="submit"
                    class="btn btn-primary">
                Simpan Perubahan
            </button>

        </div>

    </div>

</form>
```

</div>

<script>

    const kitchenSelect =
        document.getElementById('kitchen_id');

    const serviceDate =
        document.getElementById('service_date');

    const dayStatusDisplay =
        document.getElementById('day_status_display');

    const dayStatus =
        document.getElementById('day_status');

    const rawMaterial =
        document.getElementById('raw_material_expenses');

    const operational =
        document.getElementById('operational_expenses');

    const rawMaterialResult =
        document.getElementById('raw-material-result');

    const operationalResult =
        document.getElementById('operational-result');


    function formatRupiah(number) {

        return new Intl.NumberFormat('id-ID', {

            style: 'currency',

            currency: 'IDR',

            minimumFractionDigits: 0

        }).format(number);

    }


    function calculateHPE(dateValue) {

        if (!dateValue) {

            dayStatusDisplay.value = '';

            dayStatus.value = '';

            return;

        }


        const startDate =
            new Date('2026-10-05T00:00:00');

        const selectedDate =
            new Date(dateValue + 'T00:00:00');


        const difference =
            Math.floor(
                (selectedDate - startDate) /
                (1000 * 60 * 60 * 24)
            ) + 1;


        if (difference < 1) {

            dayStatusDisplay.value =
                'Belum HPE';

            dayStatus.value =
                'Belum HPE';

        } else {

            const hpe =
                'HPE ke-' + difference;

            dayStatusDisplay.value =
                hpe;

            dayStatus.value =
                hpe;

        }

    }


    function loadInvoiceTotal() {

        const kitchenId =
            kitchenSelect.value;

        const date =
            serviceDate.value;


        if (!kitchenId || !date) {

            rawMaterialResult.style.display =
                'none';

            operationalResult.style.display =
                'none';

            rawMaterial.value = 0;

            operational.value = 0;

            return;

        }


        rawMaterialResult.style.display =
            'block';

        operationalResult.style.display =
            'block';


        rawMaterialResult.innerHTML =
            'Mengambil total bahan baku...';

        operationalResult.innerHTML =
            'Mengambil total operasional...';


        const url =
            "{{ route('lpdhs.invoice-total') }}" +
            "?kitchen_id=" +
            encodeURIComponent(kitchenId) +
            "&service_date=" +
            encodeURIComponent(date);


        fetch(url)

            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        'Gagal mengambil data Invoice.'
                    );

                }

                return response.json();

            })

            .then(data => {

                const rawMaterialTotal =
                    Number(
                        data.raw_material_total || 0
                    );

                const operationalTotal =
                    Number(
                        data.operational_total || 0
                    );


                rawMaterial.value =
                    rawMaterialTotal.toFixed(2);

                operational.value =
                    operationalTotal.toFixed(2);


                rawMaterialResult.innerHTML =
                    'Zenzie + Gemilang + Koperasi: ' +
                    formatRupiah(rawMaterialTotal);


                operationalResult.innerHTML =
                    'Topfast: ' +
                    formatRupiah(operationalTotal);

            })

            .catch(error => {

                rawMaterialResult.innerHTML =
                    'Gagal mengambil total bahan baku.';

                operationalResult.innerHTML =
                    'Gagal mengambil total operasional.';

                console.error(error);

            });

    }


    kitchenSelect.addEventListener(
        'change',
        loadInvoiceTotal
    );


    serviceDate.addEventListener(
        'change',
        function () {

            calculateHPE(this.value);

            loadInvoiceTotal();

        }
    );


    calculateHPE(serviceDate.value);


    if (
        kitchenSelect.value &&
        serviceDate.value
    ) {

        loadInvoiceTotal();

    }

</script>

</body>
</html>
