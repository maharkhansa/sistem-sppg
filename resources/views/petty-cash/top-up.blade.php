@extends('layouts.app')

@section('title', 'Tambah Top Up Sub Kas Operasional')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Top Up Sub Kas Operasional</h1>

    <div class="alert alert-info">
        <strong>Pengisian saldo kas kecil PJ</strong>
        <p class="mb-0">
            Masukkan nominal dana yang diberikan dari rekening sumber
            kepada PJ Sub Kas. Saldo kas kecil PJ akan bertambah
            setelah top up berhasil disimpan.
        </p>
    </div>

    <form action="{{ route('petty-cash.top-up.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="purchase_responsible_id" class="form-label">
                PJ Sub Kas
            </label>

            <select
                name="purchase_responsible_id"
                id="purchase_responsible_id"
                class="form-select"
                required
            >
                <option value="">-- Pilih PJ --</option>

                @foreach ($responsibles as $responsible)
                    <option
                        value="{{ $responsible->id }}"
                        {{ old('purchase_responsible_id') == $responsible->id ? 'selected' : '' }}
                    >
                        {{ $responsible->name }}
                    </option>
                @endforeach
            </select>

            @error('purchase_responsible_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Informasi saldo PJ --}}
        <div
            id="pettyCashInfo"
            class="card mb-3 border-info"
            style="display: none;"
        >
            <div class="card-header bg-light">
                <strong>Informasi Saldo Sub Kas</strong>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Saldo saat ini</span>
                    <strong id="currentBalance">Rp0</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Top up terakhir</span>
                    <strong id="lastTopUpAmount">Rp0</strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Tanggal top up terakhir</span>
                    <strong id="lastTopUpDate">Belum ada</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <span>Perkiraan saldo setelah top up</span>
                    <strong id="estimatedBalance" class="text-success">
                        Rp0
                    </strong>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="transaction_date" class="form-label">
                Tanggal Top Up
            </label>

            <input
                type="date"
                name="transaction_date"
                id="transaction_date"
                class="form-control"
                value="{{ old('transaction_date', date('Y-m-d')) }}"
                required
            >

            @error('transaction_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">
                Nominal Top Up (Rp)
            </label>

            <input
                type="number"
                name="amount"
                id="amount"
                class="form-control"
                min="1"
                step="0.01"
                value="{{ old('amount') }}"
                required
            >

            @error('amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="source_account_id" class="form-label">
                Rekening Sumber
            </label>

            <select
                name="source_account_id"
                id="source_account_id"
                class="form-select"
                required
            >
                <option value="">-- Pilih Rekening Sumber --</option>

                @foreach ($accounts as $account)
                    <option
                        value="{{ $account->id }}"
                        {{ old('source_account_id') == $account->id ? 'selected' : '' }}
                    >
                        {{ $account->code }} - {{ $account->name }}
                    </option>
                @endforeach
            </select>

            @error('source_account_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="reference_number" class="form-label">
                Nomor Referensi (Opsional)
            </label>

            <input
                type="text"
                name="reference_number"
                id="reference_number"
                class="form-control"
                value="{{ old('reference_number') }}"
                maxlength="100"
            >

            @error('reference_number')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">
                Catatan (Opsional)
            </label>

            <textarea
                name="notes"
                id="notes"
                class="form-control"
                rows="3"
                maxlength="1000"
            >{{ old('notes') }}</textarea>

            @error('notes')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            Simpan Top Up
        </button>

        <a href="{{ route('petty-cash.index') }}" class="btn btn-secondary">
            Batal
        </a>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const balances = {{ Illuminate\Support\Js::from($pettyCashBalances) }};

    const responsibleInput = document.getElementById(
        'purchase_responsible_id'
    );

    const amountInput = document.getElementById('amount');
    const infoPanel = document.getElementById('pettyCashInfo');

    const currentBalanceLabel = document.getElementById('currentBalance');
    const lastTopUpAmountLabel = document.getElementById('lastTopUpAmount');
    const lastTopUpDateLabel = document.getElementById('lastTopUpDate');
    const estimatedBalanceLabel = document.getElementById('estimatedBalance');

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(value || 0);
    }

    function updateBalanceInfo() {
        const responsibleId = responsibleInput.value;
        const data = balances[responsibleId];

        if (!responsibleId || !data) {
            infoPanel.style.display = 'none';
            return;
        }

        const currentBalance = Number(data.balance || 0);
        const lastTopUpAmount = Number(data.last_top_up_amount || 0);
        const topUpAmount = Number(amountInput.value || 0);

        currentBalanceLabel.textContent = formatRupiah(currentBalance);
        lastTopUpAmountLabel.textContent = formatRupiah(lastTopUpAmount);

        lastTopUpDateLabel.textContent = data.last_top_up_date
            ? new Date(data.last_top_up_date).toLocaleDateString('id-ID')
            : 'Belum ada';

        estimatedBalanceLabel.textContent = formatRupiah(
            currentBalance + topUpAmount
        );

        infoPanel.style.display = 'block';
    }

    responsibleInput.addEventListener('change', updateBalanceInfo);
    amountInput.addEventListener('input', updateBalanceInfo);

    // Tampilkan informasi jika PJ sudah dipilih sebelumnya
    updateBalanceInfo();
});
</script>
@endpush