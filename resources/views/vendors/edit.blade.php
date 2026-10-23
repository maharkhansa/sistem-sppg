@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Vendor</h4>
            <p class="text-muted mb-0">
                Perbarui informasi Vendor dan barang yang dijual.
            </p>
        </div>

        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendors.update', $vendor) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                Informasi Vendor
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">
                        Nama Vendor <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $vendor->name) }}"
                        maxlength="150"
                        required
                    >

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contact_person" class="form-label">
                        Nama Kontak
                    </label>

                    <input
                        type="text"
                        name="contact_person"
                        id="contact_person"
                        class="form-control @error('contact_person') is-invalid @enderror"
                        value="{{ old('contact_person', $vendor->contact_person) }}"
                        maxlength="100"
                    >

                    @error('contact_person')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">
                        Nomor Telepon
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $vendor->phone) }}"
                        maxlength="30"
                    >

                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">
                        Alamat Vendor
                    </label>

                    <textarea
                        name="address"
                        id="address"
                        class="form-control @error('address') is-invalid @enderror"
                        rows="3"
                >{{ old('address', $vendor->address) }}</textarea>

                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Barang yang Dijual Vendor</strong>
                    <div class="small text-muted">
                        Centang barang yang tersedia dari Vendor ini.
                    </div>
                </div>

                <span class="badge bg-primary" id="selectedItemsCount">
                    0 barang dipilih
                </span>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <input
                        type="text"
                        id="itemSearch"
                        class="form-control"
                        placeholder="Cari nama atau kode barang..."
                    >
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="selectVisibleItems"
                    >
                        Pilih hasil pencarian
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="unselectVisibleItems"
                    >
                        Hapus centang hasil pencarian
                    </button>
                </div>

                @error('items')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div
                    class="border rounded p-3"
                    id="itemsList"
                    style="max-height: 450px; overflow-y: auto;"
                >
                    @php
                        $selectedItems = old(
                            'items',
                            $vendor->items->pluck('id')->toArray()
                        );

                        $selectedItems = array_map('strval', $selectedItems);
                    @endphp

                    @foreach($items as $item)
                        <div
                            class="form-check item-option py-1"
                            data-search="{{ strtolower($item->name . ' ' . ($item->code ?? '')) }}"
                        >
                            <input
                                class="form-check-input item-checkbox"
                                type="checkbox"
                                name="items[]"
                                value="{{ $item->id }}"
                                id="item_{{ $item->id }}"
                                {{ in_array((string) $item->id, $selectedItems, true) ? 'checked' : '' }}
                            >

                            <label
                                class="form-check-label"
                                for="item_{{ $item->id }}"
                            >
                                {{ $item->name }}

                                @if($item->code)
                                    <span class="text-muted small">
                                        ({{ $item->code }})
                                    </span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="text-muted small mt-2" id="searchResultCount"></div>
            </div>
        </div>

        @php
            $selectedPurchaseResponsibles = old(
                'purchase_responsibles',
                $vendor->purchaseResponsibles->pluck('id')->toArray()
            );

            $selectedPurchaseResponsibles = array_map(
                'strval',
                $selectedPurchaseResponsibles
            );
        @endphp

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>PJ yang Menggunakan Vendor Ini</strong>
                    <div class="small text-muted">
                        Pilih PJ yang boleh menggunakan Vendor ini untuk pembelian.
                    </div>
                </div>

                <span class="badge bg-primary" id="selectedPjCount">
                    0 PJ dipilih
                </span>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <input
                        type="text"
                        id="pjSearch"
                        class="form-control"
                        placeholder="Cari nama PJ..."
                    >
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="selectVisiblePj"
                    >
                        Pilih hasil pencarian
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="unselectVisiblePj"
                    >
                        Hapus centang hasil pencarian
                    </button>
                </div>

                @error('purchase_responsibles')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div
                    class="border rounded p-3"
                    id="pjList"
                    style="max-height: 300px; overflow-y: auto;"
                >
                    @foreach($purchaseResponsibles as $pj)
                        <div
                            class="form-check pj-option py-1"
                            data-search="{{ strtolower($pj->name) }}"
                        >
                            <input
                                class="form-check-input pj-checkbox"
                                type="checkbox"
                                name="purchase_responsibles[]"
                                value="{{ $pj->id }}"
                                id="pj_{{ $pj->id }}"
                                {{ in_array((string) $pj->id, $selectedPurchaseResponsibles, true) ? 'checked' : '' }}
                            >

                            <label
                                class="form-check-label"
                                for="pj_{{ $pj->id }}"
                            >
                                {{ $pj->name }}

                                @if(!$pj->is_active)
                                    <span class="text-muted small">
                                        (Tidak aktif)
                                    </span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="text-muted small mt-2" id="pjSearchResultCount"></div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('vendors.index') }}" class="btn btn-light">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('itemSearch');
    const itemOptions = Array.from(document.querySelectorAll('.item-option'));
    const itemCheckboxes = Array.from(document.querySelectorAll('.item-checkbox'));
    const selectedItemsCount = document.getElementById('selectedItemsCount');
    const searchResultCount = document.getElementById('searchResultCount');

    function updateCounts() {
        const selectedCount = itemCheckboxes.filter(item => item.checked).length;
        const visibleCount = itemOptions.filter(item => item.style.display !== 'none').length;

        selectedItemsCount.textContent = selectedCount + ' barang dipilih';
        searchResultCount.textContent = visibleCount + ' barang ditampilkan';
    }

    function filterItems() {
        const keyword = searchInput.value.trim().toLowerCase();

        itemOptions.forEach(option => {
            const searchText = option.dataset.search || '';
            option.style.display = searchText.includes(keyword) ? '' : 'none';
        });

        updateCounts();
    }

    searchInput.addEventListener('input', filterItems);

    document.getElementById('selectVisibleItems').addEventListener('click', function () {
        itemOptions.forEach(option => {
            if (option.style.display !== 'none') {
                option.querySelector('.item-checkbox').checked = true;
            }
        });

        updateCounts();
    });

    document.getElementById('unselectVisibleItems').addEventListener('click', function () {
        itemOptions.forEach(option => {
            if (option.style.display !== 'none') {
                option.querySelector('.item-checkbox').checked = false;
            }
        });

        updateCounts();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCounts);
    });

    filterItems();

        // Pencarian dan pilihan PJ
        const pjSearchInput = document.getElementById('pjSearch');
        const pjOptions = Array.from(document.querySelectorAll('.pj-option'));
        const pjCheckboxes = Array.from(document.querySelectorAll('.pj-checkbox'));
        const selectedPjCount = document.getElementById('selectedPjCount');
        const pjSearchResultCount = document.getElementById('pjSearchResultCount');

        function updatePjCounts() {
            const selectedCount = pjCheckboxes.filter(pj => pj.checked).length;
            const visibleCount = pjOptions.filter(pj => pj.style.display !== 'none').length;

            selectedPjCount.textContent = selectedCount + ' PJ dipilih';
            pjSearchResultCount.textContent = visibleCount + ' PJ ditampilkan';
        }

        function filterPj() {
            const keyword = pjSearchInput.value.trim().toLowerCase();

            pjOptions.forEach(option => {
                const searchText = option.dataset.search || '';
                option.style.display = searchText.includes(keyword) ? '' : 'none';
            });

            updatePjCounts();
        }

        pjSearchInput.addEventListener('input', filterPj);

        document.getElementById('selectVisiblePj').addEventListener('click', function () {
            pjOptions.forEach(option => {
                if (option.style.display !== 'none') {
                    option.querySelector('.pj-checkbox').checked = true;
                }
            });

            updatePjCounts();
        });

        document.getElementById('unselectVisiblePj').addEventListener('click', function () {
            pjOptions.forEach(option => {
                if (option.style.display !== 'none') {
                    option.querySelector('.pj-checkbox').checked = false;
                }
            });

            updatePjCounts();
        });

        pjCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updatePjCounts);
        });

        filterPj();
    });
</script>
@endpush
@endsection