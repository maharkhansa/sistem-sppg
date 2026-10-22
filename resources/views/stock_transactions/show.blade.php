@extends('layouts.app')

@section('title', 'Detail Barang Masuk | Sistem SPPG')

@push('styles')
<style>
    .page-container {
        padding: 8px 0;
    }

    .transaction-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }

    .transaction-number {
        font-size: 18px;
        font-weight: 700;
        color: #0d6efd;
        overflow-wrap: anywhere;
    }

    .badge-in {
        background-color: #d1e7dd;
        color: #0f5132;
        font-weight: 600;
    }

    .info-label {
        display: block;
        margin-bottom: 5px;
        color: #6c757d;
        font-size: 12px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .transaction-table {
        margin-bottom: 0;
        min-width: 1180px;
    }

    .transaction-table th {
        background-color: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .transaction-table td {
        font-size: 13px;
        vertical-align: middle;
    }

    .total-box {
        background-color: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
    }

    .total-box hr {
        margin: 10px 0;
        opacity: .15;
    }

    .total-box .mb-3 {
        margin-bottom: 8px !important;
    }

    .total-final {
        font-size: 20px;
        font-weight: 700;
        color: #0d6efd;
    }

    .cost-highlight {
        color: #b45309;
        font-weight: 600;
    }

    .discount-highlight {
        color: #198754;
        font-weight: 600;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 36px;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .total-box {
            background-color: #fff;
        }

        .transaction-card {
            box-shadow: none !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        body {
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid container-xl py-4 py-lg-5">
    <div class="page-container">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">
                    <i class="bi bi-box-arrow-in-down me-2 text-primary"></i>
                    Detail Transaksi Barang Masuk
                </h4>

                <small class="text-muted">
                    Informasi penerimaan barang dari vendor ke stok persediaan
                </small>
            </div>

            <div class="d-flex gap-2 no-print">
                <button type="button"
                        class="btn btn-outline-primary"
                        onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>
                    Cetak
                </button>

                <a href="{{ route('stock-transactions.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>

        {{-- PESAN --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"></button>
            </div>
        @endif

        {{-- INFORMASI TRANSAKSI --}}
        <div class="card transaction-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    Informasi Transaksi
                </h6>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="row g-4">

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Nomor Transaksi</span>
                        <div class="transaction-number">
                            {{ $stockTransaction->transaction_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Tanggal Barang Masuk</span>
                        <div class="info-value">
                            {{ $stockTransaction->transaction_date
                                ? \Illuminate\Support\Carbon::parse($stockTransaction->transaction_date)->format('d-m-Y')
                                : '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Nama Vendor</span>
                        <div class="info-value">
                            {{ $stockTransaction->vendor_name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Nomor Nota Pembelian</span>
                        <div class="info-value">
                            {{ $stockTransaction->purchase_note_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Jenis Transaksi</span>
                        <div class="mt-1">
                            @if ($stockTransaction->type === 'IN')
                                <span class="badge rounded-pill badge-in px-3 py-2">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>
                                    Barang Masuk
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    {{ $stockTransaction->type ?? '-' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <span class="info-label">Dibuat Oleh</span>
                        <div class="info-value">
                            {{ $stockTransaction->creator->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6">
                        <span class="info-label">Catatan</span>
                        <div style="white-space: pre-line;">
                            {{ $stockTransaction->notes ?: '-' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- HITUNG TOTAL --}}
        @php
            $totalQty = 0;
            $totalAmount = 0;

            $porterCost = round((float) ($stockTransaction->porter_cost ?? 0), 2);
            $fuelCost = round((float) ($stockTransaction->fuel_cost ?? 0), 2);
            $parkingCost = round((float) ($stockTransaction->parking_cost ?? 0), 2);

            foreach ($stockTransaction->details as $detail) {
                $totalQty += (float) ($detail->quantity ?? 0);
                $totalAmount += (float) ($detail->subtotal ?? 0);
            }

            $totalQty = round($totalQty, 2);
            $totalAmount = round($totalAmount, 2);

            $purchaseDiscountType = $stockTransaction->purchase_discount_type ?? 'amount';
            $purchaseDiscountValue = round((float) ($stockTransaction->purchase_discount_value ?? 0), 2);

            if ($purchaseDiscountType === 'percent') {
                $purchaseDiscountValue = min(100, max(0, $purchaseDiscountValue));
                $purchaseDiscountAmount = round($totalAmount * $purchaseDiscountValue / 100, 2);
            } else {
                $purchaseDiscountValue = max(0, $purchaseDiscountValue);
                $purchaseDiscountAmount = round(min($totalAmount, $purchaseDiscountValue), 2);
            }

            // Total nota = nilai barang setelah diskon nota
            // ditambah biaya kuli, BBM, dan parkir.
            $goodsAfterPurchaseDiscount = round(
                max(0, $totalAmount - $purchaseDiscountAmount),
                2
            );

            $grandTotal = round(
                $goodsAfterPurchaseDiscount
                + $porterCost
                + $fuelCost
                + $parkingCost,
                2
            );
        @endphp

        {{-- DETAIL BARANG --}}
        <div class="card transaction-card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-1 fw-bold">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    Daftar Barang Masuk
                </h6>

                <small class="text-muted">
                    Subtotal setiap baris mengikuti jumlah, harga satuan, dan diskon per barang.
                </small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle transaction-table">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Vendor</th>
                                <th>Kategori</th>
                                <th>Supplier Yayasan</th>
                                <th class="text-end">Jumlah</th>
                                <th>Satuan</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Diskon Barang</th>
                                <th class="text-end">Subtotal Barang</th>
                            </tr>
                        </thead>

                        <tbody>
                    @forelse ($stockTransaction->details as $detail)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td class="text-nowrap">
                                {{ $detail->item->code ?? '-' }}
                            </td>

                            <td class="fw-semibold">
                                {{ $detail->item->name ?? '-' }}
                            </td>

                            <td>
                                {{ $stockTransaction->vendor_name ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->item->category->name ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->supplier->name
                                    ?? $detail->item->supplier->name
                                    ?? '-' }}
                            </td>

                            <td class="text-end text-nowrap">
                                {{ number_format((float) $detail->quantity, 2, ',', '.') }}
                            </td>

                            <td>
                                {{ $detail->unit ?? $detail->item->unit ?? '-' }}
                            </td>

                            <td class="text-end text-nowrap">
                Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap discount-highlight">
                Rp {{ number_format((float) ($detail->discount ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap fw-semibold">
                Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>

                                    <div class="mt-2 fw-semibold">
                                        Tidak ada detail barang.
                                    </div>

                                    <small>
                                        Belum ada barang yang tercatat pada transaksi ini.
                                    </small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($stockTransaction->details->count() > 0)
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">
                                TOTAL
                            </th>

                            <th class="text-end text-nowrap">
                                {{ number_format($totalQty, 2, ',', '.') }}
                            </th>

                            <th></th>
                            <th></th>

                            <th class="text-end text-nowrap discount-highlight">
                Rp {{ number_format((float) $stockTransaction->details->sum('discount'), 0, ',', '.') }}
                            </th>

                            <th class="text-end text-nowrap">
                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
                </table>
                </div>
            </div>
        </div>

        @php
            $paidAmount = $stockTransaction->payments->sum('amount');
            $remainingAmount = max(0, $grandTotal - $paidAmount);
        @endphp

        {{-- RINGKASAN TRANSAKSI --}}
        <div class="row justify-content-end mt-4">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="total-box shadow-sm">

                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-calculator me-2 text-primary"></i>
                        Ringkasan Transaksi
                    </h6>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Jenis Barang</span>
                        <strong>
                            {{ $stockTransaction->details->count() }} item
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Quantity</span>
                        <strong>
                            {{ number_format($totalQty, 2, ',', '.') }}
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Nilai Barang (setelah diskon per barang)</span>
                        <strong class="text-end">
                            Rp {{ number_format($totalAmount, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">
                            Diskon Tambahan Nota
                            @if ($purchaseDiscountType === 'percent' && $purchaseDiscountValue > 0)
                                ({{ number_format($purchaseDiscountValue, 2, ',', '.') }}%)
                            @endif
                        </span>
                        <strong class="discount-highlight text-end">
                            − Rp {{ number_format($purchaseDiscountAmount, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Nilai Barang Setelah Diskon Nota</span>
                        <strong class="text-end">
                            Rp {{ number_format($goodsAfterPurchaseDiscount, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Biaya Kuli</span>
                        <strong class="cost-highlight text-end">
                            Rp {{ number_format($porterCost, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Biaya BBM</span>
                        <strong class="cost-highlight text-end">
                            Rp {{ number_format($fuelCost, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Biaya Parkir</span>
                        <strong class="cost-highlight text-end">
                            Rp {{ number_format($parkingCost, 0, ',', '.') }}
                        </strong>
</div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <strong>Total Pembelian</strong>

                        <strong class="total-final text-end">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </strong>
                    </div>

                                        <hr>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Total Sudah Dibayar</span>
                        <strong class="text-success text-end">
                            Rp {{ number_format($paidAmount, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <span class="text-muted">Sisa Pembayaran</span>
                        <strong class="text-danger text-end">
                            Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                        </strong>
                    </div>

                    {{-- RIWAYAT PEMBAYARAN --}}
                    @if ($stockTransaction->payments->isNotEmpty())
                        <div class="mt-3">

                            <button
                                class="btn btn-outline-secondary w-100 d-flex
                                    justify-content-between align-items-center"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#paymentHistoryCollapse"
                                aria-expanded="false"
                                aria-controls="paymentHistoryCollapse"
                            >
                                <span class="fw-semibold">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Riwayat Pembayaran
                                    <span class="badge bg-secondary ms-2">
                                        {{ $stockTransaction->payments->count() }}
                                    </span>
                                </span>

                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="collapse mt-2" id="paymentHistoryCollapse">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered
                                                table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Metode</th>
                                                <th>Rekening Sumber</th>
                                                <th class="text-end">Jumlah</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($stockTransaction->payments as $payment)
                                                <tr>
                                                    <td class="text-nowrap">
                                                        {{ $payment->payment_date
                                                            ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y')
                                                            : '-' }}
                                                    </td>

                                                    <td>
                                                        @switch($payment->payment_method)
                                                            @case('cash')
                                                                Tunai
                                                                @break
                                                            @case('transfer')
                                                                Transfer
                                                                @break
                                                            @case('other')
                                                                Lainnya
                                                                @break
                                                            @default
                                                                -
                                                        @endswitch
                                                    </td>

                                                    <td>
                                                        {{ $payment->account?->name
                                                            ?? 'Belum ditentukan' }}
                                                    </td>

                                                    <td class="text-end text-nowrap">
                                                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                                    </td>

                                                    <td>
                                                        {{ $payment->notes
                                                            ?: ($payment->reference_number ?: '-') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- FORM PEMBAYARAN TAMBAHAN --}}
                    @if ($stockTransaction->type === 'IN' && $remainingAmount > 0)
                        <hr>
                        <button
                            class="btn btn-primary w-100 d-flex
                                justify-content-between align-items-center"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#additionalPaymentCollapse"
                            aria-expanded="false"
                            aria-controls="additionalPaymentCollapse"
                        >
                            <span>
                                <i class="bi bi-cash-stack me-2"></i>
                                Catat Pembayaran Tambahan
                            </span>

                            <span class="badge bg-light text-primary">
                                Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                            </span>
                        </button>

                        <p class="text-muted small">
                            Sisa tagihan saat ini:
                            <strong class="text-danger">
                                Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                            </strong>
                        </p>

                        <div class="collapse mt-3" id="additionalPaymentCollapse">

                        <form
                            action="{{ route('stock-transactions.payments.store', $stockTransaction->id) }}"
                            method="POST"
                        >
                            @csrf

                            <input type="hidden" name="source_type" value="cash_bank">

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="payment_date" class="form-label">
                                        Tanggal Pembayaran <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="payment_date"
                                        id="payment_date"
                                        class="form-control @error('payment_date') is-invalid @enderror"
                                        value="{{ old('payment_date', now()->toDateString()) }}"
                                        required
                                    >
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="amount" class="form-label">
                                        Jumlah Pembayaran (Rp) <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="amount"
                                        id="amount"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        value="{{ old('amount') }}"
                                        min="0.01"
                                        max="{{ number_format($remainingAmount, 2, '.', '') }}"
                                        step="0.01"
                                        placeholder="Masukkan nominal pembayaran"
                                        required
                                    >
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="payment_method" class="form-label">
                                        Metode Pembayaran <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        name="payment_method"
                                        id="payment_method"
                                        class="form-select @error('payment_method') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">Pilih metode pembayaran</option>
                                        <option value="cash" @selected(old('payment_method') === 'cash')>
                                            Tunai
                                        </option>
                                        <option value="transfer" @selected(old('payment_method') === 'transfer')>
                                            Transfer
                                        </option>
                                        <option value="other" @selected(old('payment_method') === 'other')>
                                            Lainnya
                                        </option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="account_id" class="form-label">
                                        Rekening Sumber <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        name="account_id"
                                        id="account_id"
                                        class="form-select @error('account_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">Pilih rekening sumber</option>
                                        @foreach ($accounts as $account)
                                            <option
                                                value="{{ $account->id }}"
                                                @selected((string) old('account_id') === (string) $account->id)
                                            >
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if ($accounts->isEmpty())
                                        <small class="text-danger">
                                            Belum ada rekening kas atau bank aktif. Pembayaran belum dapat dicatat.
                                        </small>
                                    @endif
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="reference_number" class="form-label">
                                        Nomor Referensi
                                    </label>
                                    <input
                                        type="text"
                                        name="reference_number"
                                        id="reference_number"
                                        class="form-control @error('reference_number') is-invalid @enderror"
                                        value="{{ old('reference_number') }}"
                                        maxlength="100"
                                        placeholder="Nomor bukti transfer atau referensi"
                                    >
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="payment_notes" class="form-label">
                                        Catatan Pembayaran
                                    </label>
                                    <textarea
                                        name="notes"
                                        id="payment_notes"
                                        rows="2"
                                        maxlength="5000"
                                        class="form-control @error('notes') is-invalid @enderror"
                                        placeholder="Catatan tambahan jika diperlukan"
                                    >{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        @disabled($accounts->isEmpty())
                                    >
                                        <i class="bi bi-check-circle me-1"></i>
                                        Simpan Pembayaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <small class="text-muted d-block mt-2">
                        Total pembelian dihitung dari total nilai barang setelah diskon per barang,
                        dikurangi diskon tambahan nota, kemudian ditambah biaya kuli.
                    </small>

                </div>
            </div>
        </div>

        {{-- TOMBOL KEMBALI --}}
        <div class="d-flex justify-content-end mt-4 no-print">
            <a href="{{ route('stock-transactions.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke Daftar Barang Masuk
            </a>
        </div>

    </div>
</div>
@endsection