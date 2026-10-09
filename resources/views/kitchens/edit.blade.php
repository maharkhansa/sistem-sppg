
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data SPPG</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <!-- HEADER -->
                    <div class="d-flex align-items-center mb-4">
                        <a href="{{ route('kitchens.index') }}"
                           class="btn btn-outline-secondary btn-sm me-3"
                           title="Kembali">
                            <i class="bi bi-arrow-left"></i>
                        </a>

                        <h2 class="h4 fw-bold text-dark m-0">
                            <i class="bi bi-pencil-square me-2 text-warning"></i>
                            Edit Data SPPG
                        </h2>
                    </div>

                    <!-- ALERT ERROR -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Terjadi kesalahan input:</strong>
                            </div>

                            <ul class="mb-0 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- FORM EDIT -->
                    <form action="{{ route('kitchens.update', $kitchen->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- DATA SPPG -->
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-building text-primary fs-5 me-2"></i>
                                <h5 class="fw-bold mb-0">Data SPPG</h5>
                            </div>

                            <div class="row g-3">

                                <!-- ID SPPG -->
                                <div class="col-md-4">
                                    <label for="id_sppg" class="form-label fw-semibold">
                                        ID SPPG <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control font-monospace @error('id_sppg') is-invalid @enderror"
                                        id="id_sppg"
                                        name="id_sppg"
                                        value="{{ old('id_sppg', $kitchen->id_sppg) }}"
                                        maxlength="30"
                                        placeholder="Contoh: SPPG-001"
                                        required
                                        autofocus
                                    >

                                    @error('id_sppg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- NAMA SPPG -->
                                <div class="col-md-8">
                                    <label for="name" class="form-label fw-semibold">
                                        Nama SPPG <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $kitchen->name) }}"
                                        maxlength="150"
                                        placeholder="Masukkan nama SPPG"
                                        required
                                    >

                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- KABUPATEN/KOTA -->
                                <div class="col-md-6">
                                    <label for="kabupaten_kota" class="form-label fw-semibold">
                                        Kabupaten/Kota <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>

                                        <input
                                            type="text"
                                            class="form-control @error('kabupaten_kota') is-invalid @enderror"
                                            id="kabupaten_kota"
                                            name="kabupaten_kota"
                                            value="{{ old('kabupaten_kota', $kitchen->kabupaten_kota) }}"
                                            maxlength="100"
                                            placeholder="Contoh: Magelang"
                                            required
                                        >
                                    </div>

                                    @error('kabupaten_kota')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- PROVINSI -->
                                <div class="col-md-6">
                                    <label for="provinsi" class="form-label fw-semibold">
                                        Provinsi <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">
                                            <i class="bi bi-map"></i>
                                        </span>

                                        <input
                                            type="text"
                                            class="form-control @error('provinsi') is-invalid @enderror"
                                            id="provinsi"
                                            name="provinsi"
                                            value="{{ old('provinsi', $kitchen->provinsi) }}"
                                            maxlength="100"
                                            placeholder="Contoh: Jawa Tengah"
                                            required
                                        >
                                    </div>

                                    @error('provinsi')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- ALAMAT LENGKAP DAPUR SPPG -->
                                <div class="col-12">
                                    <label for="alamat" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        Alamat Lengkap Dapur SPPG
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        class="form-control @error('alamat') is-invalid @enderror"
                                        id="alamat"
                                        name="alamat"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Masukkan nama jalan, nomor bangunan, RT/RW, dusun, desa/kelurahan, kecamatan, kabupaten/kota, dan provinsi"
                                        required
                                    >{{ old('alamat', $kitchen->alamat) }}</textarea>

                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <small class="text-muted">
                                        Alamat ini akan ditampilkan pada Nota Keluar sesuai dapur SPPG yang dipilih.
                                    </small>
                                </div>

                            </div>
                        </div>

                        <!-- DATA MITRA / YAYASAN -->
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-bank text-primary fs-5 me-2"></i>

                                <div>
                                    <h5 class="fw-bold mb-0">Data Mitra / Yayasan</h5>
                                    <small class="text-muted">
                                        Digunakan pada bagian pengesahan LPDH.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="foundation_name" class="form-label fw-semibold">
                                        Nama Mitra / Yayasan
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('foundation_name') is-invalid @enderror"
                                        id="foundation_name"
                                        name="foundation_name"
                                        value="{{ old('foundation_name', $kitchen->foundation_name) }}"
                                        maxlength="150"
                                        placeholder="Nama Yayasan / Mitra"
                                    >

                                    @error('foundation_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- PENGAWAS KEUANGAN -->
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-check text-primary fs-5 me-2"></i>

                                <div>
                                    <h5 class="fw-bold mb-0">Pengawas Keuangan SPPG</h5>
                                    <small class="text-muted">
                                        Digunakan pada kolom "Disusun oleh" LPDH.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-7">
                                    <label for="finance_officer_name" class="form-label fw-semibold">
                                        Nama Pengawas Keuangan
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('finance_officer_name') is-invalid @enderror"
                                        id="finance_officer_name"
                                        name="finance_officer_name"
                                        value="{{ old('finance_officer_name', $kitchen->finance_officer_name) }}"
                                        maxlength="150"
                                        placeholder="Nama lengkap"
                                    >

                                    @error('finance_officer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label for="finance_officer_nik" class="form-label fw-semibold">
                                        NIK
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('finance_officer_nik') is-invalid @enderror"
                                        id="finance_officer_nik"
                                        name="finance_officer_nik"
                                        value="{{ old('finance_officer_nik', $kitchen->finance_officer_nik) }}"
                                        maxlength="50"
                                        placeholder="Nomor NIK"
                                    >

                                    @error('finance_officer_nik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <!-- KEPALA SPPG -->
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-badge text-primary fs-5 me-2"></i>

                                <div>
                                    <h5 class="fw-bold mb-0">Kepala SPPG</h5>
                                    <small class="text-muted">
                                        Digunakan pada kolom "Mengetahui" LPDH.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-7">
                                    <label for="head_sppg_name" class="form-label fw-semibold">
                                        Nama Kepala SPPG
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('head_sppg_name') is-invalid @enderror"
                                        id="head_sppg_name"
                                        name="head_sppg_name"
                                        value="{{ old('head_sppg_name', $kitchen->head_sppg_name) }}"
                                        maxlength="150"
                                        placeholder="Nama lengkap"
                                    >

                                    @error('head_sppg_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label for="head_sppg_nip" class="form-label fw-semibold">
                                        NIP
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('head_sppg_nip') is-invalid @enderror"
                                        id="head_sppg_nip"
                                        name="head_sppg_nip"
                                        value="{{ old('head_sppg_nip', $kitchen->head_sppg_nip) }}"
                                        maxlength="50"
                                        placeholder="Nomor NIP"
                                    >

                                    @error('head_sppg_nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <!-- PERWAKILAN YAYASAN -->
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-people text-primary fs-5 me-2"></i>

                                <div>
                                    <h5 class="fw-bold mb-0">Perwakilan Mitra / Yayasan</h5>
                                    <small class="text-muted">
                                        Digunakan pada kolom "Menyetujui" LPDH.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-7">
                                    <label for="foundation_rep_name" class="form-label fw-semibold">
                                        Nama Perwakilan
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('foundation_rep_name') is-invalid @enderror"
                                        id="foundation_rep_name"
                                        name="foundation_rep_name"
                                        value="{{ old('foundation_rep_name', $kitchen->foundation_rep_name) }}"
                                        maxlength="150"
                                        placeholder="Nama lengkap"
                                    >

                                    @error('foundation_rep_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label for="foundation_rep_nik" class="form-label fw-semibold">
                                        NIK
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control @error('foundation_rep_nik') is-invalid @enderror"
                                        id="foundation_rep_nik"
                                        name="foundation_rep_nik"
                                        value="{{ old('foundation_rep_nik', $kitchen->foundation_rep_nik) }}"
                                        maxlength="50"
                                        placeholder="Nomor NIK"
                                    >

                                    @error('foundation_rep_nik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <!-- TOMBOL -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                            <a href="{{ route('kitchens.index') }}"
                               class="btn btn-light border">
                                <i class="bi bi-x-circle me-1"></i>
                                Batal
                            </a>

                            <button type="submit" class="btn btn-warning px-4 text-white">
                                <i class="bi bi-check-lg me-1"></i>
                                Update
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>