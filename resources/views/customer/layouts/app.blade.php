<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Customer Portal') | {{ config('settings.system_general.title', 'OxNet') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
    <!-- DNS prefetch & preconnect -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eqjn9gIos1qrSA8oXBFjO5A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        :root {
            --cp-sidebar-width: 240px;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand img { height: 32px; }
        .expiry-badge { font-size: .75rem; }
        .countdown-digits { font-variant-numeric: tabular-nums; }
        main { flex: 1; }
        [data-bs-theme="dark"] .table { --bs-table-bg: transparent; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg border-bottom" id="cp-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('customer.dashboard') }}">
            <i class="fa-solid fa-network-wired me-1 text-primary"></i>
            {{ config('settings.system_general.title', 'OxNet') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cpNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="cpNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active fw-semibold' : '' }}"
                       href="{{ route('customer.dashboard') }}">
                        <i class="fa-solid fa-gauge-high me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customer.package.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('customer.package.index') }}">
                        <i class="fa-solid fa-box me-1"></i>Package
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customer.payments.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('customer.payments.index') }}">
                        <i class="fa-solid fa-credit-card me-1"></i>Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customer.support.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('customer.support.index') }}">
                        <i class="fa-solid fa-headset me-1"></i>Support
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customer.profile.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('customer.profile.index') }}">
                        <i class="fa-solid fa-user me-1"></i>Profile
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                {{-- Subscription countdown badge --}}
                @auth('customer')
                @php $sub = auth('customer')->user(); @endphp
                @if($sub->expires_at)
                <span class="badge expiry-badge {{ $sub->isExpired() ? 'bg-danger' : 'bg-success' }} countdown-badge"
                      data-expiry="{{ $sub->expires_at->toISOString() }}">
                    <i class="fa-solid fa-clock me-1"></i>
                    <span class="countdown-text countdown-digits">
                        {{ $sub->isExpired() ? 'Expired' : $sub->expires_at->diffForHumans() }}
                    </span>
                </span>
                @endif
                @endauth

                {{-- Dark mode toggle --}}
                <button class="btn btn-sm btn-outline-secondary" id="theme-toggle" title="Toggle dark mode">
                    <i class="fa-solid fa-moon" id="theme-icon"></i>
                </button>

                {{-- Logout --}}
                @auth('customer')
                <form method="POST" action="{{ route('customer.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Expired banner --}}
@auth('customer')
@if(auth('customer')->user()->isExpired())
<div class="alert alert-danger alert-dismissible rounded-0 mb-0 text-center py-2" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-1"></i>
    Your subscription has expired.
    <a href="{{ route('customer.payments.renew') }}" class="alert-link ms-1">Renew now</a>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif
@endauth

{{-- Flash messages --}}
<div class="container-fluid mt-3 px-4">
    @foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info', 'status' => 'info'] as $key => $type)
    @if(session($key))
    <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
        {!! session($key) !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @endforeach
</div>

<main class="container-fluid px-4 py-3">
    @yield('content')
</main>

<footer class="border-top py-3 mt-auto text-center small text-muted">
    &copy; {{ date('Y') }} {{ config('settings.system_general.title', 'OxNet') }}. All rights reserved.
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFnpSPPQEJ6W5vMz6Mi4ZKDyJR"
        crossorigin="anonymous"></script>

<script>
// Dark/Light mode toggle
(function () {
    const html = document.getElementById('html-root');
    const btn  = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');

    function applyTheme(theme) {
        html.setAttribute('data-bs-theme', theme);
        icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }

    const saved = localStorage.getItem('oxnet_theme') || 'light';
    applyTheme(saved);

    if (btn) {
        btn.addEventListener('click', function () {
            const current = html.getAttribute('data-bs-theme');
            const next    = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('oxnet_theme', next);
            applyTheme(next);
        });
    }
})();

// Subscription countdown timer
(function () {
    const badges = document.querySelectorAll('.countdown-badge[data-expiry]');
    badges.forEach(function (badge) {
        const expiry = new Date(badge.dataset.expiry).getTime();
        const textEl = badge.querySelector('.countdown-text');
        if (!textEl) return;

        function update() {
            const diff = expiry - Date.now();
            if (diff <= 0) {
                textEl.textContent = 'Expired';
                badge.className = badge.className.replace('bg-success', 'bg-danger');
                return;
            }
            const d  = Math.floor(diff / 86400000);
            const h  = Math.floor((diff % 86400000) / 3600000);
            const m  = Math.floor((diff % 3600000) / 60000);
            const s  = Math.floor((diff % 60000) / 1000);
            const pad = n => String(n).padStart(2, '0');
            textEl.textContent = d > 0
                ? d + 'd ' + pad(h) + 'h ' + pad(m) + 'm'
                : pad(h) + ':' + pad(m) + ':' + pad(s);
        }

        update();
        setInterval(update, 1000);
    });
})();
</script>

@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function (err) { if (window.console && console.warn) console.warn('SW registration failed:', err); });
    });
}
</script>
<x-ai-chat-widget portal="customer" />
</body>
</html>
