@extends('layouts.app')

@section('title', 'Dashboard | Sistem SPPG')

@php
    $menuGroups = [
        'TRANSAKSI PERSEDIAAN' => [
            ['icon' => '📥', 'label' => 'Barang Masuk', 'route' => 'stock-transactions.create'],
            ['icon' => '📤', 'label' => 'Barang Keluar', 'route' => 'stock-transactions.out'],
            ['icon' => '📦', 'label' => 'Stok Persediaan', 'route' => 'stocks.index'],
        ],

        'KAS OPERASIONAL' => [
            ['icon' => '💵', 'label' => 'Petty Cash', 'route' => 'petty-cash.index'],
        ],
        
        'PEMBELIAN & DOKUMEN' => [
            ['icon' => '🛒', 'label' => 'Purchase Order', 'route' => 'purchase-orders.index'],
            ['icon' => '🧾', 'label' => 'Invoice', 'route' => 'invoices.index'],
            ['icon' => '📄', 'label' => 'Nota Keluar', 'route' => 'nota-keluars.index'],
        ],

        'DATA MASTER' => [
            ['icon' => '🥬', 'label' => 'Data Barang', 'route' => 'items.index'],
            ['icon' => '🏷️', 'label' => 'Kategori Barang', 'route' => 'categories.index'],
            ['icon' => '🚚', 'label' => 'Data Supplier', 'route' => 'suppliers.index'],
            ['icon' => '🏪', 'label' => 'Data Vendor', 'route' => 'vendors.index'],
            ['icon' => '🏢', 'label' => 'Data Dapur SPPG', 'route' => 'kitchens.index'],
            ['icon' => '💰', 'label' => 'Jenis Biaya', 'route' => 'expense-types.index'],
        ],

        'PELAPORAN' => [
            ['icon' => '📊', 'label' => 'LPDH', 'route' => 'lpdhs.index'],
        ],
    ];

    // Menu pengelolaan akun hanya ditampilkan untuk admin.
    if (strtoupper((string) auth()->user()->role) === 'ADMIN') {
        $menuGroups['PENGATURAN AKUN'] = [
            ['icon' => '👤', 'label' => 'Kelola Akun Admin', 'route' => 'users.index'],
        ];
    }
@endphp

@push('styles')
<style>
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 30px;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .page-title {
        margin: 0;
        font-size: 27px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .page-subtitle {
        margin: 7px 0 0;
        color: var(--text-muted);
        font-size: 13px;
    }

    .date-badge {
        padding: 10px 15px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .logout-form {
        margin: 0;
    }

    .logout-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 15px;
        background: #fff;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 11px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        cursor: pointer;
        transition: background .2s, color .2s, border-color .2s;
    }

    .logout-button:hover {
        background: #b91c1c;
        color: #fff;
        border-color: #b91c1c;
    }

    .welcome-banner {
        position: relative;
        overflow: hidden;
        padding: 29px 30px;
        margin-bottom: 29px;
        color: #fff;
        border-radius: 18px;
        background: linear-gradient(115deg, #14532d, #15803d);
        box-shadow: 0 8px 24px rgba(20, 83, 45, .12);
    }

    .welcome-banner::after {
        content: "";
        position: absolute;
        width: 200px;
        height: 200px;
        right: -50px;
        top: -85px;
        border: 30px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        pointer-events: none;
    }

    .welcome-banner h2 {
        position: relative;
        z-index: 1;
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 9px;
    }

    .welcome-banner p {
        position: relative;
        z-index: 1;
        max-width: 620px;
        margin: 0;
        color: #dcfce7;
        font-size: 13px;
        line-height: 1.8;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin: 30px 0 15px;
    }

    .section-heading h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
    }

    .section-heading small {
        color: var(--text-muted);
        font-size: 11px;
    }

    .feature-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        height: 100%;
        padding: 19px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 15px;
        color: inherit;
        text-decoration: none;
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    .feature-card:hover {
        transform: translateY(-3px);
        border-color: #bbf7d0;
        box-shadow: 0 9px 24px rgba(0, 0, 0, .055);
        color: inherit;
    }

    .feature-icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: #f0fdf4;
        font-size: 22px;
    }

    .feature-card h6 {
        margin: 2px 0 7px;
        font-size: 13px;
        font-weight: 800;
    }

    .feature-card p {
        margin: 0;
        color: var(--text-muted);
        font-size: 11px;
        line-height: 1.7;
    }

    .feature-arrow {
        margin-left: auto;
        color: #9ca3af;
        font-size: 18px;
    }

    .dashboard-footer {
        margin-top: 35px;
        padding-top: 19px;
        border-top: 1px solid #e5e7eb;
        color: #9ca3af;
        font-size: 11px;
        text-align: center;
    }

    @media (max-width: 767px) {
        .page-title {
            font-size: 22px;
        }

        .topbar {
            align-items: flex-start;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .topbar-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .date-badge {
            white-space: normal;
        }

        .welcome-banner {
            padding: 23px 20px;
        }

        .welcome-banner h2 {
            font-size: 21px;
        }

        .section-heading {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')

    <header class="topbar">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">
                Selamat datang di pusat pengelolaan Sistem SPPG LA TAHZAN INDONESIA DAN AGHITS STAR INTERNATIONAL
            </p>
        </div>

        <div class="topbar-actions">
            <div class="date-badge">
                📅 {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
            </div>

            @auth
                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="logout-form"
                    onsubmit="return confirm('Apakah Anda yakin ingin keluar dari Sistem SPPG?');"
                >
                    @csrf

                    <button type="submit" class="logout-button">
                        <span aria-hidden="true">↪</span>
                        Logout
                    </button>
                </form>
            @endauth
        </div>
    </header>

    <section class="welcome-banner">
        <h2>
            Selamat Datang di Sistem SPPG LA TAHZAN INDONESIA DAN AGHITS STAR INTERNATIONAL 👋
        </h2>

        <p>
            Kelola persediaan bahan baku, transaksi barang masuk dan keluar,
            pembelian, dokumen tagihan, serta pelaporan dalam satu aplikasi.
            Pilih menu di bawah untuk mulai bekerja.
        </p>
    </section>

    @foreach ($menuGroups as $group => $items)
        <section>
            <div class="section-heading">
                <h5>{{ ucwords(strtolower($group)) }}</h5>
                <small>Pilih menu untuk membuka fitur</small>
            </div>

            <div class="row g-3">
                @foreach ($items as $item)
                    @if (\Illuminate\Support\Facades\Route::has($item['route']))
                        <div class="col-12 col-md-6 col-xl-4">
                            <a href="{{ route($item['route']) }}" class="feature-card">
                                <div class="feature-icon">
                                    {{ $item['icon'] }}
                                </div>

                                <div>
                                    <h6>{{ $item['label'] }}</h6>

                                    <p>
                                        @switch($item['route'])
                                            @case('stock-transactions.create')
                                                Catat penerimaan barang dari supplier.
                                                @break

                                            @case('stock-transactions.out')
                                                Kelola pengeluaran barang dari persediaan.
                                                @break

                                            @case('petty-cash.index')
                                                Kelola saldo kas operasional PJ, top up, dan pembayaran pembelian.
                                                @break

                                            @case('stocks.index')
                                                Periksa jumlah stok barang tersedia.
                                                @break

                                            @case('purchase-orders.index')
                                                Kelola pesanan dan proses pembelian barang.
                                                @break

                                            @case('invoices.index')
                                                Lihat dan kelola invoice transaksi.
                                                @break

                                            @case('nota-keluars.index')
                                                Buka nota keluar berdasarkan supplier.
                                                @break

                                            @case('items.index')
                                                Kelola kode, nama, satuan, dan harga barang.
                                                @break

                                            @case('categories.index')
                                                Atur kategori persediaan barang.
                                                @break

                                            @case('suppliers.index')
                                                Kelola informasi supplier dan pemasok.
                                                @break

                                            @case('kitchens.index')
                                                Kelola identitas dan data dapur SPPG.
                                                @break

                                            @case('expense-types.index')
                                                Atur jenis biaya operasional.
                                                @break

                                            @case('lpdhs.index')
                                                Buka dan kelola laporan LPDH.
                                                @break

                                            @case('users.index')
                                                Tambah akun admin serta ubah username dan password.
                                                @break

                                            @default
                                                Buka dan kelola data {{ strtolower($item['label']) }}.
                                        @endswitch
                                    </p>
                                </div>

                                <span class="feature-arrow">›</span>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach

    <footer class="dashboard-footer">
        Sistem Yayasan La Tahzan Indonesia & Aghits Star International ·
        Manajemen Persediaan dan Distribusi MBG
        <br>
        &copy; {{ date('Y') }} Sistem Yayasan
    </footer>

@endsection