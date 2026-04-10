<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Community') - OxNet Community</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
    <!-- DNS prefetch & preconnect -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: var(--bs-body-bg); }
        .community-navbar { background: #1e2a3a !important; }
        .community-navbar .navbar-brand { color: #fff !important; font-weight: 700; }
        .community-navbar .nav-link { color: rgba(255,255,255,.8) !important; }
        .community-navbar .nav-link:hover { color: #fff !important; }
        .sidebar-widget { border-radius: 10px; }
        .post-card { border-radius: 10px; transition: box-shadow .2s; }
        .post-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.1); }
        .tag-badge { font-size: .75rem; padding: .3rem .6rem; border-radius: 20px; cursor: pointer; text-decoration: none; }
        .reputation-badge { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border-radius: 12px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
        .post-type-badge { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; }
        [data-bs-theme="dark"] .community-navbar { background: #111827 !important; }
        .accepted-answer { border-left: 4px solid #22c55e; }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg community-navbar sticky-top">
    <div class="container-xl">
        <a class="navbar-brand" href="{{ route('community.index') }}">
            <i class="bi bi-people-fill me-2 text-primary"></i>OxNet Community
        </a>
        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#communityNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="communityNav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('community.index') ? 'fw-semibold text-white' : '' }}" href="{{ route('community.index') }}">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('community.posts.*') ? 'fw-semibold text-white' : '' }}" href="{{ route('community.posts.index') }}">
                        <i class="bi bi-chat-square-text me-1"></i>Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('community.categories.*') ? 'fw-semibold text-white' : '' }}" href="{{ route('community.categories.index') }}">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('community.tags.*') ? 'fw-semibold text-white' : '' }}" href="{{ route('community.tags.index') }}">
                        <i class="bi bi-tags me-1"></i>Tags
                    </a>
                </li>
            </ul>
            <form class="d-flex me-3" action="{{ route('community.search') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input class="form-control" type="search" name="q" placeholder="Search..." value="{{ request('q') }}" style="min-width:200px;">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <ul class="navbar-nav gap-1 align-items-center">
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-secondary" id="themeToggle"><i class="bi bi-sun-fill" id="themeIcon"></i></button>
                </li>
                @auth('community')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('community.notifications') }}"><i class="bi bi-bell"></i></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            @if(auth('community')->user()->avatar)
                                <img src="{{ Storage::url(auth('community')->user()->avatar) }}" class="rounded-circle" width="28" height="28" alt="">
                            @else
                                <i class="bi bi-person-circle fs-5"></i>
                            @endif
                            <span class="d-none d-lg-inline">{{ auth('community')->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('community.profile.show', auth('community')->id()) }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('community.profile.edit') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('community.logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm" href="{{ route('community.posts.create') }}"><i class="bi bi-plus-lg me-1"></i>New Post</a>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('community.login') }}">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm" href="{{ route('community.register') }}">Join Community</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div class="container-xl py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            @foreach(['success'=>'success','error'=>'danger','info'=>'info','warning'=>'warning'] as $key=>$class)
                @if(session($key))
                    <div class="alert alert-{{ $class }} alert-dismissible fade show mb-3">
                        {{ session($key) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach
            @yield('content')
        </div>
        <div class="col-lg-4">
            @yield('sidebar')
            @hasSection('sidebar')
            @else
            {{-- Default sidebar --}}
            @includeWhen(isset($popularCategories) || isset($trendingTags), 'community.partials.sidebar')
            @endif
        </div>
    </div>
</div>

<footer class="border-top py-4 mt-5">
    <div class="container-xl text-center text-muted small">
        <p class="mb-1">OxNet Community &copy; {{ date('Y') }} — A place for ISP professionals</p>
        <p class="mb-0">
            <a href="{{ route('community.index') }}" class="text-muted me-3">Home</a>
            <a href="{{ route('community.posts.index') }}" class="text-muted me-3">Posts</a>
            <a href="{{ route('community.categories.index') }}" class="text-muted me-3">Categories</a>
            <a href="{{ route('community.tags.index') }}" class="text-muted">Tags</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');
    const savedTheme = localStorage.getItem('oxnet_theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    if (themeIcon) themeIcon.className = savedTheme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    document.getElementById('themeToggle')?.addEventListener('click', function () {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('oxnet_theme', next);
        themeIcon.className = next === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
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
<x-ai-chat-widget portal="community" />
</body>
</html>
