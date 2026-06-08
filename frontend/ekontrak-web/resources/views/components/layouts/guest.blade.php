<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Log Masuk' }} — eKontrak</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* blue-900 = #1e3a8a, blue-800 = #1e40af */
            --primary:        #1e3a8a;
            --primary-btn:    #1e40af;
            --primary-hover:  #1e3a8a;
            --yellow:         #facc15;
            --red-bar:        #dc2626;
            --white:          #ffffff;
            --bg:             #f8fafc;
            --slate-50:       #f8fafc;
            --slate-100:      #f1f5f9;
            --slate-200:      #e2e8f0;
            --slate-300:      #cbd5e1;
            --slate-400:      #94a3b8;
            --slate-500:      #64748b;
            --slate-600:      #475569;
            --slate-700:      #334155;
            --slate-800:      #1e293b;
            --slate-900:      #0f172a;
            --red:            #ef4444;
            --green:          #22c55e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Navbar ── */
        .site-nav {
            background: var(--white);
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            padding: 0.5rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 20;
        }

        .site-nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .kpkt-logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            flex-shrink: 0;
        }

        .kpkt-logo-wrap img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        .site-nav-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--slate-900);
            letter-spacing: -0.025em;
            line-height: 1.3;
        }

        .site-nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .site-nav-links a {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-600);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
        }
        .site-nav-links a:hover { color: #1e40af; }

        .nav-link-login {
            color: var(--primary) !important;
        }
        .nav-link-login:hover { color: #1e40af !important; }

        /* ── Accent bar: 50% yellow | 50% red ── */
        .accent-bar {
            display: flex;
            height: 6px;
            position: relative;
            z-index: 20;
        }
        .accent-bar-yellow { width: 50%; background: var(--yellow); }
        .accent-bar-red    { width: 50%; background: var(--red-bar); }

        /* ── Main content ── */
        .page-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 10;
        }

        /* Hexagonal background pattern */
        .page-body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='104' viewBox='0 0 60 104' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0L60 17.32v34.64L30 69.28L0 51.96V17.32L30 0zM30 104L60 86.68V52.04L30 34.72L0 52.04v34.64L30 104z' fill='none' stroke='%23000' stroke-width='1'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
        }

        .page-inner {
            width: 100%;
            max-width: 90rem;
            margin: 0 auto;
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: clamp(2.5rem, 5vw, 6rem);
            padding: clamp(2rem, 5vw, 5rem);
            flex: 1;
        }

        /* ── Left Hero ── */
        .hero-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-heading {
            font-size: 4.5rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.025em;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero-heading .ekontrak {
            color: var(--primary);
        }

        .hero-underline {
            width: 7rem;
            height: 0.5rem;
            background: var(--yellow);
            border-radius: 9999px;
            margin-bottom: 2rem;
        }

        .hero-desc {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--slate-700);
            line-height: 1.625;
            max-width: 540px;
            margin-bottom: 3rem;
        }

        .feature-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            width: 100%;
        }

        .feature-card {
            background: var(--white);
            border-radius: 0.5rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            border: 1px solid var(--slate-200);
        }

        .feature-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .feature-card-icon.navy  { background: var(--primary); }
        .feature-card-icon.amber { background: var(--yellow); }

        .feature-card-icon svg { width: 20px; height: 20px; }
        .feature-card-icon.navy svg  { color: var(--white); }
        .feature-card-icon.amber svg { color: #172554; }

        .feature-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 0.25rem;
        }

        .feature-card-sub {
            font-size: 0.875rem;
            color: var(--slate-500);
            line-height: 1.4;
        }

        /* ── Login Card ── */
        .login-card {
            width: 100%;
            max-width: 36rem;
            flex-shrink: 0;
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            border: 1px solid var(--slate-200);
            overflow: hidden;
            position: relative;
        }

        .login-card-top {
            height: 0.625rem;
            width: 100%;
            background: var(--yellow);
            position: absolute;
            top: 0;
            left: 0;
        }

        .login-card-body {
            padding: clamp(2rem, 4vw, 3.5rem) clamp(1.35rem, 3.4vw, 3rem) clamp(1.75rem, 3vw, 3rem);
        }

        .login-card-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 2.5rem;
        }

        /* ── Form elements ── */
        .form-fields { display: flex; flex-direction: column; gap: 1.2rem; }

        .form-group { display: flex; flex-direction: column; gap: 0.45rem; margin-bottom: 1rem; }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--slate-700);
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400);
            width: 20px;
            height: 20px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrap:focus-within .input-icon {
            color: var(--primary-btn);
        }

        .toggle-password {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--slate-400);
            padding: 0;
            line-height: 0;
        }
        .toggle-password:hover { color: var(--slate-700); }

        .invalid-feedback {
            font-size: 0.75rem;
            color: var(--red);
            margin-top: 0.25rem;
        }

        /* Forgot row */
        .forgot-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.25rem;
            gap: 0.75rem;
        }

        .form-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e40af;
            text-decoration: underline;
            text-decoration-color: rgba(30,64,175,0.3);
            text-underline-offset: 4px;
            transition: color 0.2s;
        }
        .form-link:hover { color: var(--primary); }

        /* ── Buttons ── */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-top: 1rem;
        }

        .login-card .btn { width: 100%; letter-spacing: 0.04em; }
        .btn:active { transform: scale(0.98); }

        /* ── Alerts ── */
        .alert {
            padding: 0.75rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }
        .alert-error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .page-inner { gap: 3rem; padding: 3rem; }
            .hero-heading { font-size: 3.75rem; }
        }
        @media (max-width: 900px) {
            .page-inner { flex-direction: column; align-items: center; padding: 2rem 1.5rem; gap: 2rem; }
            .hero-left { display: none; }
            .login-card { max-width: 480px; }
        }
        @media (max-width: 480px) {
            .site-nav { padding: 0.5rem 1rem; }
            .login-card-body { padding: 2rem 1.15rem 1.75rem; }
            .hero-heading { font-size: 2.75rem; }
        }

        .global-footer {
            width: 100%;
            border-top: 3px solid #dc2626;
            margin-top: auto;
            background: #4b5563;
            color: #e5e7eb;
        }

        .global-footer-contact {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            align-items: center;
            padding: 0.72rem 1.1rem;
            background: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .global-footer-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            justify-content: center;
            text-align: center;
            line-height: 1.35;
        }

        .global-footer-item svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: #f3f4f6;
        }

        .global-footer-note {
            text-align: center;
            background: #5b6470;
            color: #f3f4f6;
            padding: 0.62rem 1rem;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .global-footer-copy {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.82rem 1rem;
            background: #4b5563;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .global-footer-copy img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            border-radius: 6px;
            background: #ffffff;
            padding: 2px;
        }

        @media (max-width: 900px) {
            .global-footer-contact {
                grid-template-columns: 1fr;
                gap: 0.45rem;
                padding: 0.8rem;
            }

            .global-footer-item {
                justify-content: flex-start;
                text-align: left;
            }

            .global-footer-note,
            .global-footer-copy {
                font-size: 0.86rem;
                padding-left: 0.9rem;
                padding-right: 0.9rem;
            }
        }
    </style>
    @include('components.design-system')
</head>
<body>

    {{-- Top Navbar --}}
    <header class="site-nav">
        <a href="/" class="site-nav-brand">
            <div class="kpkt-logo-wrap">
                <img src="{{ asset('storage/images/JATA_KPKT_BM_BLACK.png') }}" alt="Jata Negara Malaysia - KPKT">
            </div>
            <div class="site-nav-title">
                KEMENTERIAN PERUMAHAN<br>DAN KERAJAAN TEMPATAN
            </div>
        </a>
        <div class="site-nav-links">
            <a href="#">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Panduan Pentadbir Kontrak
            </a>
            <a href="{{ route('login') }}" class="nav-link-login">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Log Masuk
            </a>
        </div>
    </header>

    {{-- Accent bar: 50% yellow | 50% red ── --}}
    <div class="accent-bar">
        <div class="accent-bar-yellow"></div>
        <div class="accent-bar-red"></div>
    </div>

    {{-- Page body --}}
    <div class="page-body">
        <div class="page-inner">
            @php
                // List of route names that should NOT show the left hero section
                $noHeroRoutes = [
                    'register.form', // Borang Pendaftaran Pengguna (full form)
                ];
            @endphp
            @if (!in_array(Route::currentRouteName(), $noHeroRoutes))
                {{-- Left: Hero --}}
                <div class="hero-left">
                    <h1 class="hero-heading">
                        Sistem <span class="ekontrak">eKONTRAK</span>
                    </h1>
                    <div class="hero-underline"></div>
                    <p class="hero-desc">
                        Sistem eKONTRAK merupakan sistem yang dibangunkan bagi membantu pegawai
                        Kementerian Perumahan dan Kerajaan Tempatan (KPKT) mendaftar, mengurus
                        dan memantau kontrak-kontrak di bawah seliaan KPKT.
                    </p>
                    <div class="feature-cards">
                        <div class="feature-card">
                            <div class="feature-card-icon navy">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="feature-card-title">Pendaftaran</div>
                            <div class="feature-card-sub">Daftar kontrak baharu</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-card-icon amber">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="feature-card-title">Pengurusan</div>
                            <div class="feature-card-sub">Urus kontrak sedia ada</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-card-icon navy">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="feature-card-title">Pemantauan</div>
                            <div class="feature-card-sub">Pantau status kontrak</div>
                        </div>
                    </div>
                </div>
            @endif
            {{-- Right: Login Card or Main Content --}}
            <div class="login-card" style="@if(in_array(Route::currentRouteName(), $noHeroRoutes))max-width:100%;@endif">
                <div class="login-card-top"></div>
                <div class="login-card-body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @include('components.layouts.global-footer', ['variant' => 'public'])

@include('components.sweetalert-service')

</body>
</html>
