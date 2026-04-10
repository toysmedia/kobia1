<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('settings.system_general.title', 'iNetto') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')

    <style>
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .ox-pub-content { flex: 1 0 auto; }

        /* ── Public Navbar ── */
        .ox-pub-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1050;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            transition: box-shadow .25s ease;
            padding: 0;
        }
        .ox-pub-nav.ox-nav-scrolled {
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
        }
        .ox-pub-nav .navbar-brand { display: flex; align-items: center; gap: 10px; }
        .ox-pub-nav .navbar-brand img { max-height: 38px; width: auto; }
        .ox-pub-nav .ox-brand-icon { font-size: 2rem; color: #4f46e5; line-height: 1; }
        .ox-pub-nav .ox-brand-text {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -.3px;
        }
        .ox-pub-nav .nav-link {
            color: #374151 !important;
            font-size: .88rem;
            font-weight: 500;
            padding: 6px 12px !important;
            transition: color .2s;
        }
        .ox-pub-nav .nav-link:hover { color: #4f46e5 !important; }
        .ox-pub-nav .ox-nav-btn {
            background: #4f46e5;
            color: #fff !important;
            border-radius: 6px;
            padding: 8px 18px !important;
            font-weight: 600;
        }
        .ox-pub-nav .ox-nav-btn:hover {
            background: #3730a3;
            color: #fff !important;
        }
        .ox-pub-nav .navbar-toggler {
            border: 1.5px solid #e5e7eb;
            padding: 5px 8px;
            border-radius: 8px;
        }
        .ox-pub-nav .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba(0,0,0,0.65)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        /* ── Public Footer ── */
        .ox-pub-footer {
            background: #0f0e2a;
            color: rgba(255,255,255,.55);
            padding: 40px 0 28px;
            flex-shrink: 0;
        }
        .ox-pub-footer .ox-footer-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .ox-pub-footer .ox-footer-brand img { max-height: 32px; width: auto; filter: brightness(1.2); }
        .ox-pub-footer .ox-footer-brand-icon { font-size: 1.6rem; color: #a78bfa; }
        .ox-pub-footer .ox-footer-brand-text { font-size: 1rem; font-weight: 800; color: #fff; }
        .ox-pub-footer .ox-footer-tagline { font-size: .82rem; line-height: 1.55; margin-bottom: 0; }
        .ox-pub-footer .ox-footer-divider { border-color: rgba(255,255,255,.08); margin: 28px 0 20px; }
        .ox-pub-footer .ox-footer-copy { font-size: .8rem; }
        .ox-pub-footer .ox-footer-copy a { color: #a78bfa; text-decoration: none; }
        .ox-pub-footer .ox-footer-copy a:hover { text-decoration: underline; }
        .ox-pub-footer .ox-footer-links { display: flex; flex-wrap: wrap; gap: 4px 16px; justify-content: flex-end; }
        .ox-pub-footer .ox-footer-links a { font-size: .8rem; color: rgba(255,255,255,.45); text-decoration: none; transition: color .2s; }
        .ox-pub-footer .ox-footer-links a:hover { color: #a78bfa; }

        @media (max-width: 767.98px) {
            .ox-pub-footer .ox-footer-links { justify-content: flex-start; margin-top: 12px; }
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="ox-pub-nav navbar navbar-expand-lg" id="oxPubNav">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            @if(config('settings.system_general.logo_path'))
                <img src="{{ asset('storage/' . config('settings.system_general.logo_path')) }}"
                     alt="{{ config('settings.system_general.title', 'OxNet') }}">
            @else
                <i class="bx bx-station ox-brand-icon"></i>
                <span class="ox-brand-text">{{ config('settings.system_general.logo_text', 'OxNet') }}</span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('community.login') }}">
                <i class="bx bx-group me-1"></i>Join Our Community
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">
                <i class="bx bx-user me-1"></i>Customer Portal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link ox-nav-btn" href="{{ route('admin.login') }}">
                <i class="bx bx-lock-alt me-1"></i>Admin Login
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link ox-nav-btn" href="https://oxnet.co.ke/super-admin/login">
                <i class="bx bx-crown me-1"></i>Super Admin
            </a>
        </li>
    </ul>
</div>
    </div>
</nav>

<!-- Content -->
<div class="ox-pub-content">
    @yield('content')
</div>

<!-- Footer -->
<footer class="ox-pub-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="ox-footer-brand">
                    @if(config('settings.system_general.logo_path'))
                        <img src="{{ asset('storage/' . config('settings.system_general.logo_path')) }}"
                             alt="{{ config('settings.system_general.title', 'OxNet') }}">
                    @else
                        <i class="bx bx-station ox-footer-brand-icon"></i>
                        <span class="ox-footer-brand-text">{{ config('settings.system_general.logo_text', 'OxNet') }}</span>
                    @endif
                </div>
                <p class="ox-footer-tagline">
                    Premium ISP management SaaS for Kenyan internet service providers.
                </p>
            </div>
        </div>
        <hr class="ox-footer-divider">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="ox-footer-copy mb-0">
                    {!! config('settings.system_general.copyright', 'Developed by <a href="#" target="_blank">Kelvin Muriuki</a>') !!}
                </p>
            </div>
            <div class="col-md-6">
                <div class="ox-footer-links">
                    <a href="{{ route('admin.login') }}">Admin</a>
                    <a href="{{ route('login') }}">Customer</a>
                    <a href="{{ route('seller.login') }}">Seller</a>
                    @if(Route::has('community.login'))
                        <a href="{{ route('community.login') }}">Community</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

<script>
(function () {
    var nav = document.getElementById('oxPubNav');
    if (!nav) return;
    function updateNav() {
        if (window.scrollY > 40) {
            nav.classList.add('ox-nav-scrolled');
        } else {
            nav.classList.remove('ox-nav-scrolled');
        }
    }
    updateNav();
    window.addEventListener('scroll', updateNav, { passive: true });
})();
</script>

@stack('scripts')
<x-ai-chat-widget portal="guest" />
</body>
</html>