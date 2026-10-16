@extends('layouts.app')

@section('title', 'Buat Jurnal')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Buat Jurnal Umum</h1>
            <p class="text-muted mb-0">Isi transaksi sesuai bukti dan akun yang tepat.</p>
        </div>
        <a href="{{ route('finance.journals.index') }}"
           class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Jurnal belum dapat disimpan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.journals.store') }}"
          id="journalForm">
        @csrf

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="entry_date" class="form-label">
                            Tanggal jurnal <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="entry_date" name="entry_date"
                               class="form-control @error('entry_date') is-invalid @enderror"
                               value="{{ old('entry_date', date('Y-m-d')) }}" required>
                        @error('entry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label for="description" class="form-label">
                            Keterangan <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="description" name="description"
                               class="form-control @error('description') is-invalid @enderror"
                               maxlength="255" value="{{ old('description') }}"
                               placeholder="Contoh: Setoran modal awal" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <h2 class="h5 mb-0">Rincian Jurnal</h2>
                <button type="button" class="btn btn-sm btn-outline-primary"
                        id="addLine">+ Tambah Baris</button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="journalLines">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:240px">Akun</th>
                            <th style="min-width:180px">Keterangan Baris</th>
                            <th style="min-width:150px">Debit (Rp)</th>
                            <th style="min-width:150px">Kredit (Rp)</th>
                            <th style="width:70px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="lineContainer">
                        @php
                            $oldLines = old('lines', [
                                ['account_id' => '', 'description' => '', 'debit' => '0', 'credit' => '0'],
                                ['account_id' => '', 'description' => '', 'debit' => '0', 'credit' => '0'],
                            ]);
                        @endphp

                        @foreach($oldLines as $index => $line)
                            <tr class="journal-line">
                                <td>
                                    <select name="lines[{{ $index }}][account_id]"
                                            class="form-select" required>
                                        <option value="">Pilih akun</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                @selected((string) ($line['account_id'] ?? '') === (string) $account->id)>
                                                {{ $account->code }} — {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text"
                                           name="lines[{{ $index }}][description]"
                                           class="form-control"
                                           maxlength="255"
                                           value="{{ $line['description'] ?? '' }}"
                                           placeholder="Opsional">
                                </td>
                                <td>
                                    <input type="number"
                                           name="lines[{{ $index }}][debit]"
                                           class="form-control debit-input"
                                           min="0" step="0.01"
                                           value="{{ $line['debit'] ?? '0' }}" required>
                                </td>
                                <td>
                                    <input type="number"
                                           name="lines[{{ $index }}][credit]"
                                           class="form-control credit-input"
                                           min="0" step="0.01"
                                           value="{{ $line['credit'] ?? '0' }}" required>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger remove-line"
                                            title="Hapus baris">×</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total</th>
                            <th id="totalDebit">Rp 0,00</th>
                            <th id="totalCredit">Rp 0,00</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end">
                                <span id="balanceStatus" class="badge bg-secondary">
                                    Isi rincian jurnal
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="card-body d-flex flex-wrap gap-2 justify-content-end">
                <a href="{{ route('finance.journals.index') }}"
                   class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    Simpan sebagai Draft
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('lineContainer');
    const addButton = document.getElementById('addLine');
    const accounts = @json($accounts->map(fn ($account) => [
        'id' => $account->id,
        'label' => $account->code . ' — ' . $account->name,
    ])->values());

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function updateTotals() {
        let debit = 0;
        let credit = 0;

        container.querySelectorAll('.debit-input').forEach(input => {
            debit += Number(input.value || 0);
        });

        container.querySelectorAll('.credit-input').forEach(input => {
            credit += Number(input.value || 0);
        });

        document.getElementById('totalDebit').textContent = formatRupiah(debit);
        document.getElementById('totalCredit').textContent = formatRupiah(credit);

        const status = document.getElementById('balanceStatus');

        if (debit > 0 && credit > 0 && Math.abs(debit - credit) < 0.005) {
            status.className = 'badge bg-success';
            status.textContent = 'Seimbang';
        } else {
            status.className = 'badge bg-warning text-dark';
            status.textContent = 'Belum seimbang';
        }
    }

    function reindexLines() {
        container.querySelectorAll('.journal-line').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach(input => {
                input.name = input.name.replace(
                    /lines\[\d+\]/,
                    'lines[' + index + ']'
                );
            });
        });
    }

    function addLine() {
        const index = container.querySelectorAll('.journal-line').length;
        const row = document.createElement('tr');
        row.className = 'journal-line';

        const options = accounts.map(account =>
            `<option value="${account.id}">${escapeHtml(account.label)}</option>`
        ).join('');

        row.innerHTML = `
            <td>
                <select name="lines[${index}][account_id]" class="form-select" required>
                    <option value="">Pilih akun</option>
                    ${options}
                </select>
            </td>
            <td>
                <input type="text" name="lines[${index}][description]"
                    class="form-control" maxlength="255" placeholder="Opsional">
            </td>
            <td>
                <input type="number" name="lines[${index}][debit]"
                    class="form-control debit-input" min="0" step="0.01"
                    value="0" required>
            </td>
            <td>
                <input type="number" name="lines[${index}][credit]"
                    class="form-control credit-input" min="0" step="0.01"
                    value="0" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-line">×</button>
            </td>
        `;

        container.appendChild(row);
        updateTotals();
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    addButton.addEventListener('click', addLine);

    container.addEventListener('input', function (event) {
        if (
            event.target.classList.contains('debit-input') ||
            event.target.classList.contains('credit-input')
        ) {
            updateTotals();
        }
    });

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-line');

        if (!button) return;

        if (container.querySelectorAll('.journal-line').length <= 2) {
            alert('Jurnal minimal harus memiliki dua baris.');
            return;
        }

        button.closest('tr').remove();
        reindexLines();
        updateTotals();
    });

    document.getElementById('journalForm').addEventListener('submit', function (event) {
        const debit = Array.from(container.querySelectorAll('.debit-input'))
            .reduce((sum, input) => sum + Number(input.value || 0), 0);

        const credit = Array.from(container.querySelectorAll('.credit-input'))
            .reduce((sum, input) => sum + Number(input.value || 0), 0);

        if (
            container.querySelectorAll('.journal-line').length < 2 ||
            debit <= 0 ||
            credit <= 0 ||
            Math.abs(debit - credit) >= 0.005
        ) {
            event.preventDefault();
            alert('Periksa kembali jurnal. Total debit dan kredit harus sama serta lebih dari nol.');
        }
    });

    updateTotals();
});
</script>
@endsection