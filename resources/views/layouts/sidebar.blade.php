@php
    $menuGroups = [
        'TRANSAKSI PERSEDIAAN' => [
            ['icon' => '📥', 'label' => 'Barang Masuk', 'route' => 'stock-transactions.index'],
            ['icon' => '📤', 'label' => 'Barang Keluar', 'route' => 'stock-transactions.out'],
            ['icon' => '📦', 'label' => 'Stok Persediaan', 'route' => 'stocks.index'],
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
            ['icon' => '🏢', 'label' => 'Data Dapur SPPG', 'route' => 'kitchens.index'],
            ['icon' => '💰', 'label' => 'Jenis Biaya', 'route' => 'expense-types.index'],
        ],

        'PELAPORAN' => [
            ['icon' => '📊', 'label' => 'LPDH', 'route' => 'lpdhs.index'],
        ],
    ];

    // Tambahkan menu pengelolaan akun khusus untuk admin.
    if (auth()->check() && strtoupper((string) auth()->user()->role) === 'ADMIN') {
        $menuGroups['PENGATURAN AKUN'] = [
            ['icon' => '👤', 'label' => 'Kelola Akun Admin', 'route' => 'users.index'],
        ];
    }
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">🍱</div>

        <div>
            <h5>SISTEM YAYASAN AGHIST STAR</h5>
            <small>Manajemen Persediaan</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}"
           class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="menu-icon">🏠</span>
            <span>Dashboard</span>
        </a>

        @foreach ($menuGroups as $group => $items)
            <div class="menu-label">{{ $group }}</div>

            @foreach ($items as $item)
                @if (\Illuminate\Support\Facades\Route::has($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="menu-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <span class="menu-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        @endforeach
    </nav>
</aside