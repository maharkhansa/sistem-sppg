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

    <!-- Bootstrap Icons: untuk semua ikon aplikasi -->
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
            width: 250px;
            padding: 25px 17px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
            z-index: 1000;
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

        /* KONTEN UTAMA */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        /* IKON */
        .bi {
            vertical-align: middle;
        }

        /* RESPONSIVE TABLET */
        @media (max-width: 991px) {
            .sidebar {
                width: 215px;
            }

            .main-content {
                margin-left: 215px;
                padding: 22px;
            }
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 767px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 14px;
                border-right: 0;
                border-bottom: 1px solid #e5e7eb;
            }

            .brand {
                padding-bottom: 13px;
                margin-bottom: 10px;
            }

            .sidebar-nav {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .menu-label {
                width: 100%;
                margin: 12px 0 5px;
            }

            .menu-link {
                width: auto;
                padding: 8px 10px;
                margin: 0;
                font-size: 11px;
            }

            .main-content {
                margin-left: 0;
                padding: 19px 14px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- SIDEBAR NAVIGASI -->
    @include('layouts.sidebar')

    <!-- KONTEN HALAMAN -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Bootstrap JavaScript untuk modal, dropdown, dan alert -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript tambahan dari masing-masing halaman -->
    @stack('scripts')
</body>
</html>