<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - OxNet</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
    <!-- DNS prefetch & preconnect -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        #sidebar { width: var(--sidebar-width); min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000; background: #1e2a3a; color: #cdd3da; transition: transform .3s ease; overflow-y: auto; }
        #sidebar .brand { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.1); }
        #sidebar .nav-link { color: #8a9ab0; padding: .6rem 1.5rem; border-radius: 6px; margin: 2px 8px; display: flex; align-items: center; gap: .5rem; transition: all .2s; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        #sidebar .nav-section { font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: #6b7a8d; padding: .75rem 1.5rem .25rem; }
        #main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        #topbar { background: #fff; border-bottom: 1px solid #e9ecef; padding: .75rem 1.5rem; position: sticky; top: 0; z-index: 999; }
        [data-bs-theme="dark"] #topbar { background: #242832; border-color: #343a40; }
        .page-content { padding: 1.5rem; }
        @media (max-width: 768px) { #sidebar { transform: translateX(-100%); } #sidebar.show { transform: translateX(0); } #main-content { margin-left: 0; } }
    </style>
    @stack('styles')
</head>
<body>
<div id="sidebar">
    <div class="brand">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>OxNet Admin</h5>
        <small class="text-secondary">Super Admin Panel</small>
    </div>
    <nav class="pt-2 pb-4">
        <div class="nav-section">Main</div>
        <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="{{ route('super-admin.tenants.index') }}" class="nav-link {{ request()->routeIs('super-admin.tenants.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> Tenants</a>
        <a href="{{ route('super-admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('super-admin.subscriptions.*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i> Subscriptions</a>
        <a href="{{ route('super-admin.pricing-plans.index') }}" class="nav-link {{ request()->routeIs('super-admin.pricing-plans.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Pricing Plans</a>
        <div class="nav-section">Content & Config</div>
        <a href="{{ route('super-admin.cms.index') }}" class="nav-link {{ request()->routeIs('super-admin.cms.*') ? 'active' : '' }}"><i class="bi bi-file-text"></i> CMS</a>
        <a href="{{ route('super-admin.sms-gateway.index') }}" class="nav-link {{ request()->routeIs('super-admin.sms-gateway.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i> SMS Gateway</a>
        <a href="{{ route('super-admin.email-gateway.index') }}" class="nav-link {{ request()->routeIs('super-admin.email-gateway.*') ? 'active' : '' }}"><i class="bi bi-envelope"></i> Email Gateway</a>
        <div class="nav-section">System</div>
        <a href="{{ route('super-admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('super-admin.audit-logs.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Audit Logs</a>
        <a href="{{ route('super-admin.recycle-bin.index') }}" class="nav-link {{ request()->routeIs('super-admin.recycle-bin.*') ? 'active' : '' }}"><i class="bi bi-trash3"></i> Recycle Bin</a>
        <a href="{{ route('super-admin.tenant-map.index') }}" class="nav-link {{ request()->routeIs('super-admin.tenant-map.*') ? 'active' : '' }}"><i class="bi bi-map"></i> Tenant Map</a>
        <div class="nav-section">Community</div>
        <a href="{{ route('super-admin.community.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.community.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Community Portal</a>
        <div class="nav-section">AI Assistant</div>
        <a href="{{ route('super-admin.ai.index') }}" class="nav-link {{ request()->routeIs('super-admin.ai.*') ? 'active' : '' }}"><i class="bi bi-robot"></i> AI Assistant</a>
    </nav>
</div>
<div id="main-content">
    <div id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <span class="fw-semibold text-muted d-none d-md-inline">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary" id="themeToggle"><i class="bi bi-sun-fill" id="themeIcon"></i></button>
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ auth('super_admin')->user()->name ?? 'Admin' }}</span>
            <form method="POST" action="{{ route('super-admin.logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
            </form>
        </div>
    </div>
    <div class="page-content">
        @foreach(['success'=>'success','error'=>'danger','info'=>'info','warning'=>'warning'] as $key=>$class)
            @if(session($key))
                <div class="alert alert-{{ $class }} alert-dismissible fade show">
                    {{ session($key) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach
        @yield('content')
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');
    const savedTheme = localStorage.getItem('oxnet_theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    themeIcon.className = savedTheme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    document.getElementById('themeToggle').addEventListener('click', function () {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('oxnet_theme', next);
        themeIcon.className = next === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    });
    document.getElementById('sidebarToggle')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function (err) { if (window.console && console.warn) console.warn('SW registration failed:', err); });
    });
}
</script>
<x-ai-chat-widget portal="admin" />
</body>
</html>
