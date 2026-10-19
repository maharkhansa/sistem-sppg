<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123c2d">

    <title>Login - Sistem YASI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green-950: #102f25;
            --green-900: #123c2d;
            --green-800: #18563d;
            --green-700: #1e7049;
            --green-600: #248653;
            --green-100: #e4f3e8;
            --green-50: #f0f8f1;
            --cream: #f7f8f2;
            --text: #1b2c23;
            --muted: #7b8980;
            --border: #e3eae4;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: var(--cream);
        }

        button,
        input {
            font: inherit;
        }

        .page {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(420px, 0.95fr);
            min-height: 100vh;
        }

        /* PANEL KIRI */

        .visual-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            padding: 42px clamp(30px, 5vw, 76px);
            overflow: hidden;
            color: #fff;
            background:
                radial-gradient(
                    circle at 85% 18%,
                    rgba(103, 172, 117, 0.23),
                    transparent 31%
                ),
                radial-gradient(
                    circle at 10% 88%,
                    rgba(121, 183, 125, 0.15),
                    transparent 34%
                ),
                linear-gradient(145deg, #194d37 0%, #123c2d 52%, #0b2b20 100%);
        }

        .visual-panel::before,
        .visual-panel::after {
            position: absolute;
            content: "";
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 50%;
            pointer-events: none;
        }

        .visual-panel::before {
            width: 540px;
            height: 540px;
            top: 13%;
            right: -300px;
        }

        .visual-panel::after {
            width: 390px;
            height: 390px;
            top: 23%;
            right: -220px;
        }

        .brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .brand-logo svg {
            width: 27px;
            height: 27px;
        }

        .brand-name {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.6px;
        }

        .brand-caption {
            margin: 3px 0 0;
            color: #b9d5c2;
            font-size: 11px;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .visual-content {
            position: relative;
            z-index: 1;
            max-width: 570px;
            margin: 55px auto;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 25px;
            padding: 9px 14px;
            border: 1px solid rgba(214, 243, 220, 0.2);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.07);
            color: #d5eedb;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .eyebrow-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #9ee6a8;
            box-shadow: 0 0 12px rgba(158, 230, 168, 0.6);
        }

        .visual-content h2 {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: clamp(34px, 4vw, 53px);
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -2px;
        }

        .visual-content h2 span {
            color: #a8dfad;
        }

        .visual-description {
            max-width: 430px;
            margin: 22px 0 0;
            color: #c1d9c8;
            font-size: 15px;
            line-height: 1.9;
        }

        .illustration {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 210px;
            margin-top: 36px;
        }

        .illustration-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 19px;
            width: min(100%, 380px);
            padding: 22px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.09);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(10px);
        }

        .illustration-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 82px;
            height: 82px;
            flex-shrink: 0;
            border-radius: 19px;
            background: rgba(211, 241, 211, 0.15);
        }

        .illustration-icon svg {
            width: 57px;
            height: 57px;
        }

        .illustration-info p {
            margin: 0;
            color: #c6dfcc;
            font-size: 12px;
        }

        .illustration-info h3 {
            margin: 6px 0 8px;
            font-size: 17px;
            font-weight: 700;
        }

        .status-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #b9edc2;
            font-size: 11px;
        }

        .status-label svg {
            width: 13px;
            height: 13px;
        }

        .visual-footer {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding-top: 23px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            color: #b7d0be;
            font-size: 11px;
            line-height: 1.7;
        }

        /* PANEL KANAN */

        .form-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 45px clamp(28px, 5vw, 78px);
            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(222, 239, 222, 0.36),
                    transparent 34%
                ),
                var(--cream);
        }

        .mobile-brand {
            display: none;
        }

        .login-container {
            width: 100%;
            max-width: 410px;
        }

        .form-heading {
            margin-bottom: 30px;
        }

        .welcome-label {
            margin-bottom: 12px;
            color: var(--green-700);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .form-heading h1 {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: clamp(29px, 3vw, 35px);
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -1.3px;
        }

        .form-heading p {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 22px;
            padding: 13px 15px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.65;
        }

        .alert svg {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-error {
            border-color: #fecaca;
            background: #fff1f2;
            color: #991b1b;
        }

        .alert-success {
            border-color: #bbf7d0;
            background: #effdf4;
            color: #166534;
        }

        .alert div + div {
            margin-top: 3px;
        }

        .form-group {
            margin-bottom: 21px;
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 9px;
        }

        .label-row label {
            color: #304438;
            font-size: 13px;
            font-weight: 700;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            color: #82958a;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .input-icon svg {
            width: 19px;
            height: 19px;
        }

        .form-control {
            display: block;
            width: 100%;
            height: 51px;
            padding: 0 46px;
            border: 1px solid #dce5dd;
            border-radius: 11px;
            outline: none;
            background: #fff;
            color: var(--text);
            font-size: 14px;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .form-control::placeholder {
            color: #a6b1a9;
        }

        .form-control:hover {
            border-color: #b8cbbd;
        }

        .form-control:focus {
            border-color: var(--green-600);
            box-shadow: 0 0 0 4px rgba(36, 134, 83, 0.1);
        }

        .input-wrap:focus-within .input-icon {
            color: var(--green-700);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 31px;
            height: 31px;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #82958a;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .toggle-password:hover {
            background: var(--green-50);
            color: var(--green-800);
        }

        .toggle-password svg {
            width: 19px;
            height: 19px;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 1px 0 25px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #64756a;
            font-size: 13px;
            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--green-700);
            cursor: pointer;
        }

        .login-button {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            width: 100%;
            min-height: 52px;
            padding: 14px 20px;
            overflow: hidden;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(110deg, #1c7048, #248653);
            box-shadow: 0 9px 20px rgba(30, 112, 73, 0.19);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            background: linear-gradient(110deg, #185b3b, #1c7048);
            box-shadow: 0 12px 24px rgba(30, 112, 73, 0.25);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button svg {
            width: 18px;
            height: 18px;
            transition: transform 0.2s ease;
        }

        .login-button:hover svg {
            transform: translateX(3px);
        }

        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 22px;
            color: #87948a;
            font-size: 11px;
            line-height: 1.7;
            text-align: center;
        }

        .security-note svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            color: var(--green-700);
        }

        .form-footer {
            width: 100%;
            max-width: 410px;
            margin-top: 42px;
            color: #9aa59c;
            font-size: 11px;
            line-height: 1.8;
            text-align: center;
        }

        /* RESPONSIVE */

        @media (max-width: 1000px) {
            .page {
                grid-template-columns: minmax(0, 0.9fr) minmax(400px, 1.1fr);
            }

            .visual-panel {
                padding-right: 30px;
                padding-left: 30px;
            }

            .visual-content h2 {
                font-size: 39px;
            }

            .form-panel {
                padding-right: 35px;
                padding-left: 35px;
            }
        }

        @media (max-width: 760px) {
            .page {
                display: block;
                min-height: 100vh;
            }

            .visual-panel {
                display: none;
            }

            .form-panel {
                justify-content: flex-start;
                min-height: 100vh;
                padding: 30px 23px 22px;
            }

            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                width: 100%;
                max-width: 410px;
                margin: 0 auto 55px;
            }

            .mobile-brand .brand-logo {
                border-color: #d4e8d8;
                background: var(--green-100);
                color: var(--green-800);
            }

            .mobile-brand .brand-name {
                color: var(--green-950);
            }

            .mobile-brand .brand-caption {
                color: #7b9180;
            }

            .login-container {
                margin: auto 0;
            }

            .form-heading h1 {
                font-size: 31px;
            }

            .form-footer {
                margin-top: 38px;
            }
        }

        @media (max-width: 380px) {
            .form-panel {
                padding-right: 18px;
                padding-left: 18px;
            }

            .mobile-brand {
                margin-bottom: 38px;
            }

            .form-options {
                align-items: flex-start;
            }

            .remember {
                font-size: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- PANEL KIRI: IDENTITAS SISTEM -->
    <aside class="visual-panel">

        <div class="brand">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M20 4c-8 0-13 3.5-13 9a5 5 0 0 0 5 5c5.5 0 8-6 8-14Z"/>
                    <path d="M4 21c2.5-5.5 6-8.5 11-11"/>
                    <path d="M5 5c2 0 3.5 1 4.5 2.5"/>
                </svg>
            </div>

            <div>
                <p class="brand-name">Sistem Yayasan Aghits Star</p>
                <p class="brand-caption">Management System</p>
            </div>
        </div>

        <div class="visual-content">

            <div class="eyebrow">
                <span class="eyebrow-dot"></span>
                SISTEM MANAJEMEN TERPADU
            </div>

            <h2>
                Kelola lebih mudah,<br>
                distribusi lebih <span>terarah.</span>
            </h2>

            <p class="visual-description">
                Satu sistem untuk mengelola persediaan bahan baku,
                transaksi gudang, pesanan pembelian, hingga dokumen
                distribusi dapur SPPG secara terintegrasi.
            </p>

            <div class="illustration">
                <div class="illustration-card">

                    <div class="illustration-icon">
                        <svg viewBox="0 0 64 64" fill="none"
                             stroke="#d5efd7" stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M9 27 32 14l23 13"/>
                            <path d="M14 29v22h36V29"/>
                            <path d="M25 51V36h14v15"/>
                            <path d="M19 31h8v7h-8z"/>
                            <path d="M37 31h8v7h-8z"/>
                            <path d="M29 10c0-4 5-6 8-3 2 2 1 5-2 7"/>
                            <path d="M33 14c-5-1-8-4-8-8"/>
                        </svg>
                    </div>

                    <div class="illustration-info">
                        <p>Pengelolaan operasional</p>
                        <h3>Semua dalam satu sistem</h3>
                        <span class="status-label">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                            Data transaksi terorganisir
                        </span>
                    </div>

                </div>
            </div>
        </div>

        <div class="visual-footer">
            <span>Sistem Yayasan Aghits Star Indonesia</span>
            <span>Terintegrasi &amp; Terstruktur</span>
        </div>

    </aside>

    <!-- PANEL KANAN: FORM LOGIN -->
    <main class="form-panel">

        <!-- Brand khusus tampilan HP -->
        <div class="mobile-brand">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M20 4c-8 0-13 3.5-13 9a5 5 0 0 0 5 5c5.5 0 8-6 8-14Z"/>
                    <path d="M4 21c2.5-5.5 6-8.5 11-11"/>
                    <path d="M5 5c2 0 3.5 1 4.5 2.5"/>
                </svg>
            </div>

            <div>
                <p class="brand-name">Sistem Yayasan Aghits Star</p>
                <p class="brand-caption">Management System</p>
            </div>
        </div>

        <section class="login-container">

            <div class="form-heading">
                <div class="welcome-label">Selamat Datang</div>

                <h1>Masuk ke akun Anda</h1>

                <p>
                    Silakan masukkan username dan password untuk
                    melanjutkan ke dashboard Sistem Yayasan Aghits Star.
                </p>
            </div>

            <!-- Pesan berhasil -->
            @if (session('success'))
                <div class="alert alert-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <path d="m9 11 3 3L22 4"/>
                    </svg>

                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <!-- Pesan validasi dan login gagal -->
            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>

                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <div class="label-row">
                        <label for="username">Username</label>
                    </div>

                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                            </svg>
                        </span>

                        <input
                            class="form-control"
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username Anda"
                            autocomplete="username"
                            maxlength="50"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                    </div>

                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <rect x="4" y="10" width="16" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                <path d="M12 14v3"/>
                            </svg>
                        </span>

                        <input
                            class="form-control"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password Anda"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            class="toggle-password"
                            type="button"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                            title="Tampilkan atau sembunyikan password"
                        >
                            <svg id="eyeIcon" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Ingat Saya -->
                <div class="form-options">
                    <label class="remember" for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                        >
                        <span>Ingat saya</span>
                    </label>
                </div>

                <!-- Tombol Login -->
                <button class="login-button" type="submit">
                    <span>Masuk ke Dashboard</span>

                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>

            </form>

            <div class="security-note">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                </svg>

                <span>Akses sistem dilindungi. Gunakan akun Anda sendiri.</span>
            </div>

        </section>

        <footer class="form-footer">
            &copy; {{ date('Y') }} Sistem Manajemen<br>
            Yayasan Aghits Star Indonesia
        </footer>

    </main>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        if (!passwordInput || !toggleButton || !eyeIcon) {
            return;
        }

        const eyeOpen = `
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
        `;

        const eyeClosed = `
            <path d="m3 3 18 18"/>
            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
            <path d="M9.9 5.2A11 11 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-4 4.8"/>
            <path d="M6.2 6.2C3.4 8 2 12 2 12s3.5 7 10 7c1.3 0 2.5-.3 3.5-.8"/>
        `;

        toggleButton.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';

            passwordInput.type = showPassword ? 'text' : 'password';
            eyeIcon.innerHTML = showPassword ? eyeClosed : eyeOpen;

            toggleButton.setAttribute(
                'aria-label',
                showPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );

            toggleButton.setAttribute(
                'aria-pressed',
                showPassword ? 'true' : 'false'
            );
        });
    });
</script>

</body>
</html>