<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Sistem SPPG')</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>
        :root {
            --primary: #166534;
            --primary-dark: #14532d;
            --primary-light: #dcfce7;
            --page-bg: #f5f7f6;
            --text-main: #1f2937;
            --text-muted: #6b7280;

            --sidebar-width: 250px;
            --sidebar-tablet-width: 215px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text-main);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            padding: 25px 17px;
            padding-top: 65px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
            z-index: 1000;

            transition: transform .25s ease;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 27px;
            border-bottom: 1px solid #edf0ed;
            margin-bottom: 22px;
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            flex-shrink: 0;
            border-radius: 13px;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .brand h5 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
        }

        .brand small {
            color: var(--text-muted);
            font-size: 11px;
        }

        .menu-label {
            padding: 0 12px;
            margin: 23px 0 9px;
            color: #9ca3af;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.2px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 13px;
            margin-bottom: 5px;
            color: #4b5563;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: .2s;
        }

        .menu-link:hover,
        .menu-link.active {
            color: var(--primary);
            background: var(--primary-light);
        }

        .menu-icon {
            width: 23px;
            flex-shrink: 0;
            text-align: center;
            font-size: 17px;
        }

        /* TOMBOL SIDEBAR */
        .sidebar-toggle {
            position: fixed;
            top: 14px;
            left: calc(var(--sidebar-width) + 14px);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: var(--primary);
            font-size: 20px;
            cursor: pointer;
            z-index: 1100;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
            transition: left .25s ease, background .2s;
        }

        .sidebar-toggle:hover {
            background: var(--primary-light);
        }

        /* LAPISAN GELAP DI BELAKANG SIDEBAR */
        .sidebar-backdrop {
            display: none;
        }

        /* KONTEN UTAMA */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            padding-top: 70px;
            min-height: 100vh;
            transition: margin-left .25s ease;
        }

        /* SIDEBAR TERSEMBUNYI */
        body.sidebar-hidden .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-hidden .main-content {
            margin-left: 0;
        }

        body.sidebar-hidden .sidebar-toggle {
            left: 14px;
        }

        /* IKON */
        .bi {
            vertical-align: middle;
        }

        /* TABLET */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: var(--sidebar-tablet-width);
            }

            .sidebar {
                width: var(--sidebar-tablet-width);
            }

            .main-content {
                margin-left: var(--sidebar-tablet-width);
                padding: 22px;
                padding-top: 70px;
            }

            body.sidebar-hidden .main-content {
                margin-left: 0;
            }
        }

        /* MOBILE */
        @media (max-width: 767px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: min(280px, 85vw);
                height: 100%;
                padding: 65px 14px 20px;
                border-right: 1px solid #e5e7eb;
                border-bottom: 0;
                transform: translateX(-100%);
                box-shadow: 4px 0 20px rgba(15, 23, 42, .08);
            }

            /* Sidebar hanya muncul ketika dibuka */
            body:not(.sidebar-hidden) .sidebar {
                transform: translateX(0);
            }

            /* Konten mobile selalu menggunakan lebar layar */
            .main-content,
            body.sidebar-hidden .main-content {
                margin-left: 0;
                padding: 70px 14px 19px;
            }

            /* Tombol selalu di kiri atas */
            .sidebar-toggle,
            body.sidebar-hidden .sidebar-toggle {
                left: 14px;
            }

            /* Latar belakang ketika sidebar terbuka */
            body:not(.sidebar-hidden) .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .4);
                z-index: 900;
            }

            /* Brand */
            .brand {
                padding-bottom: 13px;
                margin-bottom: 10px;
            }

            /* Menu tetap vertikal */
            .sidebar-nav {
                display: block;
            }

            .menu-label {
                width: 100%;
                margin: 20px 0 7px;
            }

            .menu-link {
                width: 100%;
                padding: 10px 12px;
                margin-bottom: 4px;
                font-size: 12px;
            }
        }

        /* Kurangi animasi jika pengguna memilihnya */
        @media (prefers-reduced-motion: reduce) {
            .sidebar,
            .main-content,
            .sidebar-toggle {
                transition: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Tombol buka/tutup sidebar -->
    <button
        type="button"
        class="sidebar-toggle"
        id="sidebarToggle"
        aria-label="Tutup sidebar"
        aria-controls="appSidebar"
        aria-expanded="true"
        title="Tutup sidebar"
    >
        <i class="bi bi-list"></i>
    </button>

    <!-- Latar belakang sidebar pada mobile -->
    <div
        class="sidebar-backdrop"
        id="sidebarBackdrop"
        aria-hidden="true"
    ></div>

    <!-- SIDEBAR NAVIGASI -->
    <div id="appSidebar">
        @include('layouts.sidebar')
    </div>

    <!-- KONTEN HALAMAN -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const toggleButton = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            const mobileQuery = window.matchMedia('(max-width: 767px)');

            const storageKey = 'sppg_sidebar_hidden';

            function isMobile() {
                return mobileQuery.matches;
            }

            function setSidebarHidden(hidden, savePreference = true) {
                body.classList.toggle('sidebar-hidden', hidden);

                toggleButton.setAttribute(
                    'aria-expanded',
                    String(!hidden)
                );

                toggleButton.setAttribute(
                    'aria-label',
                    hidden ? 'Buka sidebar' : 'Tutup sidebar'
                );

                toggleButton.setAttribute(
                    'title',
                    hidden ? 'Buka sidebar' : 'Tutup sidebar'
                );

                toggleButton.innerHTML = hidden
                    ? '<i class="bi bi-list"></i>'
                    : '<i class="bi bi-x-lg"></i>';

                if (savePreference) {
                    try {
                        localStorage.setItem(storageKey, String(hidden));
                    } catch (error) {
                        // Sidebar tetap berfungsi jika penyimpanan tidak tersedia.
                    }
                }
            }

            // Ambil status sidebar yang tersimpan.
            let savedHidden = false;

            try {
                savedHidden = localStorage.getItem(storageKey) === 'true';
            } catch (error) {
                savedHidden = false;
            }

            // Pada mobile, sidebar awalnya tertutup.
            if (isMobile()) {
                setSidebarHidden(true, false);
            } else {
                setSidebarHidden(savedHidden, false);
            }

            // Tombol hamburger.
            toggleButton.addEventListener('click', function () {
                const currentlyHidden = body.classList.contains('sidebar-hidden');

                setSidebarHidden(!currentlyHidden);
            });

            // Klik area gelap menutup sidebar pada mobile.
            backdrop.addEventListener('click', function () {
                if (isMobile()) {
                    setSidebarHidden(true);
                }
            });

            // Sidebar otomatis tertutup setelah memilih menu di mobile.
            document.querySelectorAll('.sidebar .menu-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobile()) {
                        setSidebarHidden(true);
                    }
                });
            });

            // Ketika ukuran layar berubah.
            mobileQuery.addEventListener('change', function (event) {
                if (event.matches) {
                    setSidebarHidden(true, false);
                } else {
                    let hidden = false;

                    try {
                        hidden = localStorage.getItem(storageKey) === 'true';
                    } catch (error) {
                        hidden = false;
                    }

                    setSidebarHidden(hidden, false);
                }
            });
        });
    </script>

    <!-- JavaScript tambahan dari masing-masing halaman -->
    @stack('scripts')
</body>
</html>