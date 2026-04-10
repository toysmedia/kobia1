@extends("layouts.public")
@section('title', 'Home')

@push('styles')
<style>
/* =============================================
   OXNET – Clean Minimal Guest Page
   ============================================= */
:root {
    --ox-primary:   #4f46e5;
    --ox-primary-d: #3730a3;
    --ox-primary-l: #ede9fe;
    --ox-text:      #111827;
    --ox-muted:     #6b7280;
    --ox-border:    #e5e7eb;
    --ox-bg-alt:    #f9fafb;
    --ox-radius:    10px;
    --ox-radius-lg: 14px;
    --ox-shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
    --ox-shadow:    0 4px 12px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.05);
    --ox-shadow-lg: 0 12px 28px rgba(0,0,0,.1), 0 4px 8px rgba(0,0,0,.06);
    --ox-transition: .2s ease;
}

/* ── Hero ── */
.ox-hero {
    background: #fff;
    padding: 100px 0 72px;
    text-align: center;
}
.ox-hero-label {
    display: inline-block;
    background: var(--ox-primary-l);
    color: var(--ox-primary);
    border-radius: 50px;
    padding: 5px 18px;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    margin-bottom: 22px;
}
.ox-hero-title {
    font-size: clamp(1.9rem, 5vw, 3rem);
    font-weight: 800;
    color: var(--ox-text);
    line-height: 1.15;
    margin-bottom: 18px;
    letter-spacing: -.3px;
}
.ox-hero-title .ox-accent { color: var(--ox-primary); }
.ox-hero-sub {
    font-size: 1.08rem;
    color: var(--ox-muted);
    max-width: 520px;
    margin: 0 auto 36px;
    line-height: 1.7;
}
.ox-hero-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.ox-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--ox-primary);
    color: #fff;
    border-radius: var(--ox-radius);
    padding: 13px 26px;
    font-weight: 600;
    font-size: .93rem;
    text-decoration: none;
    border: 2px solid var(--ox-primary);
    transition: background var(--ox-transition), transform var(--ox-transition);
}
.ox-btn-primary:hover {
    background: var(--ox-primary-d);
    border-color: var(--ox-primary-d);
    color: #fff;
    transform: translateY(-1px);
}
.ox-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: var(--ox-text);
    border: 2px solid var(--ox-border);
    border-radius: var(--ox-radius);
    padding: 13px 26px;
    font-weight: 600;
    font-size: .93rem;
    text-decoration: none;
    transition: border-color var(--ox-transition), color var(--ox-transition), transform var(--ox-transition);
}
.ox-btn-secondary:hover {
    border-color: var(--ox-primary);
    color: var(--ox-primary);
    transform: translateY(-1px);
}

/* ── Trust bar ── */
.ox-trust-bar {
    background: var(--ox-bg-alt);
    border-top: 1px solid var(--ox-border);
    border-bottom: 1px solid var(--ox-border);
    padding: 18px 0;
}
.ox-trust-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: .85rem;
    color: var(--ox-muted);
    font-weight: 500;
}
.ox-trust-dot {
    width: 8px; height: 8px;
    background: #22c55e;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── Section helpers ── */
.ox-section-tag {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: var(--ox-primary);
    margin-bottom: 10px;
}
.ox-section-title {
    font-size: clamp(1.45rem, 3vw, 2rem);
    font-weight: 800;
    color: var(--ox-text);
    margin-bottom: 10px;
    line-height: 1.25;
    letter-spacing: -.2px;
}
.ox-section-sub {
    font-size: .97rem;
    color: var(--ox-muted);
    line-height: 1.65;
    max-width: 480px;
}

/* ── Pricing Section ── */
.ox-pricing-section {
    padding: 80px 0;
    background: var(--ox-bg-alt);
}
.ox-pricing-card {
    background: #fff;
    border: 1.5px solid var(--ox-border);
    border-radius: var(--ox-radius-lg);
    padding: 32px 26px;
    height: 100%;
    position: relative;
    transition: box-shadow var(--ox-transition), transform var(--ox-transition);
}
.ox-pricing-card:hover {
    box-shadow: var(--ox-shadow-lg);
    transform: translateY(-4px);
}
.ox-pricing-card.ox-popular {
    border-color: var(--ox-primary);
    box-shadow: 0 0 0 1px var(--ox-primary), var(--ox-shadow);
}
.ox-popular-badge {
    display: inline-block;
    background: var(--ox-primary);
    color: #fff;
    border-radius: 50px;
    padding: 3px 13px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    margin-bottom: 14px;
}
.ox-pricing-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ox-text);
    margin-bottom: 14px;
}
.ox-pricing-price {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--ox-text);
    line-height: 1;
    margin-bottom: 2px;
}
.ox-pricing-price .ox-currency {
    font-size: 1.1rem;
    font-weight: 600;
    vertical-align: top;
    margin-top: 8px;
    display: inline-block;
    color: var(--ox-muted);
}
.ox-pricing-period {
    font-size: .83rem;
    color: var(--ox-muted);
    margin-bottom: 22px;
}
.ox-pricing-divider { border-color: var(--ox-border); margin: 18px 0; }
.ox-pricing-features {
    list-style: none;
    padding: 0;
    margin: 0 0 26px;
}
.ox-pricing-features li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: .875rem;
    color: var(--ox-text);
    padding: 5px 0;
    line-height: 1.45;
}
.ox-check {
    width: 18px; height: 18px;
    background: #dcfce7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .6rem;
    color: #15803d;
    margin-top: 1px;
}
.ox-pricing-cta {
    display: block;
    text-align: center;
    padding: 12px 20px;
    border-radius: var(--ox-radius);
    font-weight: 600;
    font-size: .9rem;
    text-decoration: none;
    border: 2px solid var(--ox-primary);
    color: var(--ox-primary);
    transition: background var(--ox-transition), color var(--ox-transition);
}
.ox-pricing-cta:hover,
.ox-pricing-card.ox-popular .ox-pricing-cta {
    background: var(--ox-primary);
    color: #fff;
}
.ox-pricing-card.ox-popular .ox-pricing-cta:hover {
    background: var(--ox-primary-d);
    border-color: var(--ox-primary-d);
    color: #fff;
}

/* ── Features ── */
.ox-features-section {
    padding: 80px 0;
    background: #fff;
}
.ox-feature-card {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    padding: 22px;
    border: 1px solid var(--ox-border);
    border-radius: var(--ox-radius);
    height: 100%;
    background: #fff;
    transition: border-color var(--ox-transition), box-shadow var(--ox-transition);
}
.ox-feature-card:hover {
    border-color: #c7d2fe;
    box-shadow: var(--ox-shadow);
}
.ox-feature-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.ox-feature-body h6 {
    font-size: .92rem;
    font-weight: 700;
    color: var(--ox-text);
    margin-bottom: 4px;
}
.ox-feature-body p {
    font-size: .83rem;
    color: var(--ox-muted);
    line-height: 1.6;
    margin: 0;
}

/* ── Portals ── */
.ox-portals-section {
    padding: 80px 0;
    background: var(--ox-bg-alt);
}
.ox-portal-card {
    background: #fff;
    border: 1.5px solid var(--ox-border);
    border-radius: var(--ox-radius);
    padding: 28px 20px;
    text-align: center;
    text-decoration: none;
    color: var(--ox-text);
    display: block;
    height: 100%;
    transition: border-color var(--ox-transition), box-shadow var(--ox-transition), transform var(--ox-transition);
}
.ox-portal-card:hover {
    border-color: var(--ox-primary);
    box-shadow: var(--ox-shadow-lg);
    transform: translateY(-4px);
    color: var(--ox-text);
}
.ox-portal-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 1.35rem;
}
.ox-portal-name {
    font-weight: 700;
    font-size: .9rem;
    margin-bottom: 3px;
}
.ox-portal-desc {
    font-size: .77rem;
    color: var(--ox-muted);
}

/* ── CTA bottom ── */
.ox-cta-section {
    padding: 80px 0;
    background: var(--ox-primary);
    text-align: center;
}
.ox-cta-section h2 {
    font-size: clamp(1.5rem, 3vw, 2.1rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
}
.ox-cta-section p {
    font-size: 1rem;
    color: rgba(255,255,255,.82);
    max-width: 460px;
    margin: 0 auto 32px;
    line-height: 1.65;
}
.ox-btn-white {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: var(--ox-primary);
    border-radius: var(--ox-radius);
    padding: 13px 26px;
    font-weight: 600;
    font-size: .93rem;
    text-decoration: none;
    border: 2px solid #fff;
    transition: background var(--ox-transition), transform var(--ox-transition);
}
.ox-btn-white:hover {
    background: #f0f0ff;
    color: var(--ox-primary-d);
    transform: translateY(-1px);
}
.ox-btn-white-outline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: #fff;
    border: 2px solid rgba(255,255,255,.45);
    border-radius: var(--ox-radius);
    padding: 13px 26px;
    font-weight: 600;
    font-size: .93rem;
    text-decoration: none;
    transition: border-color var(--ox-transition), background var(--ox-transition), transform var(--ox-transition);
}
.ox-btn-white-outline:hover {
    border-color: rgba(255,255,255,.85);
    background: rgba(255,255,255,.12);
    color: #fff;
    transform: translateY(-1px);
}

/* ── Responsive ── */
@media (max-width: 767.98px) {
    .ox-hero { padding: 80px 0 60px; }
    .ox-hero-actions { flex-direction: column; align-items: center; }
    .ox-pricing-section, .ox-features-section,
    .ox-portals-section, .ox-cta-section { padding: 60px 0; }
    .ox-trust-item { justify-content: flex-start; }
}
</style>
@endpush

@section('content')

{{-- ══ HERO ══ --}}
<section class="ox-hero">
    <div class="container">
        <div class="ox-hero-label">ISP Management Platform</div>
        <h1 class="ox-hero-title">
            Manage your ISP<br>
            <span class="ox-accent">smarter &amp; faster</span>
        </h1>
        <p class="ox-hero-sub">
            OxNet is a complete multi-tenant platform for Kenyan internet service providers.
            Automate billing, MikroTik, M-Pesa payments, and subscriber self-service — all in one place.
        </p>
        <div class="ox-hero-actions">
            <a href="{{ route('admin.login') }}" class="ox-btn-primary">
                <i class="bx bx-shield-quarter"></i> Admin Login
            </a>
            <a href="{{ route('login') }}" class="ox-btn-secondary">
                <i class="bx bx-user"></i> Customer Portal
            </a>
        </div>
    </div>
</section>

{{-- ══ TRUST BAR ══ --}}
<div class="ox-trust-bar">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-sm-3">
                <div class="ox-trust-item">
                    <span class="ox-trust-dot"></span> 500+ ISPs Managed
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="ox-trust-item">
                    <span class="ox-trust-dot"></span> 99.9% Uptime SLA
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="ox-trust-item">
                    <span class="ox-trust-dot"></span> 24/7 Support
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="ox-trust-item">
                    <span class="ox-trust-dot"></span> M-Pesa Integrated
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ PRICING ══ --}}
@if(count($packages) > 0)
<section class="ox-pricing-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="ox-section-tag">Pricing</div>
            <h2 class="ox-section-title">Internet Packages</h2>
            <p class="ox-section-sub mx-auto">Transparent pricing, no hidden fees. Choose the plan that fits your needs.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach($packages as $index => $package)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="ox-pricing-card {{ $index === 1 ? 'ox-popular' : '' }}">
                    @if($index === 1)
                        <div class="ox-popular-badge">Most Popular</div>
                    @endif
                    <div class="ox-pricing-name">{{ $package->name }}</div>
                    <div class="ox-pricing-price">
                        <span class="ox-currency">{{ config('settings.system_general.currency_symbol', 'KSh') }}</span>{{ number_format($package->price) }}
                    </div>
                    <div class="ox-pricing-period">per {{ $package->valid }}</div>
                    <hr class="ox-pricing-divider">
                    <ul class="ox-pricing-features">
                        <li>
                            <span class="ox-check"><i class="bx bx-check"></i></span>
                            {{ $package->profile }}
                        </li>
                        <li>
                            <span class="ox-check"><i class="bx bx-check"></i></span>
                            M-Pesa STK Push payment
                        </li>
                        <li>
                            <span class="ox-check"><i class="bx bx-check"></i></span>
                            Self-service customer portal
                        </li>
                        <li>
                            <span class="ox-check"><i class="bx bx-check"></i></span>
                            Auto-activation on payment
                        </li>
                        <li>
                            <span class="ox-check"><i class="bx bx-check"></i></span>
                            24/7 AI-assisted support
                        </li>
                    </ul>
                    <a href="{{ route('login') }}" class="ox-pricing-cta">Get Started</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ FEATURES ══ --}}
<section class="ox-features-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="ox-section-tag">Features</div>
            <h2 class="ox-section-title">Everything you need to run your ISP</h2>
            <p class="ox-section-sub mx-auto">Powerful tools built specifically for Kenyan internet service providers.</p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#ede9fe;"><i class="bx bx-buildings" style="color:#7c3aed;"></i></div>
                    <div class="ox-feature-body">
                        <h6>Multi-Tenant Platform</h6>
                        <p>Each ISP gets their own isolated environment with custom branding and subscriber database.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#fce7f3;"><i class="bx bx-router" style="color:#db2777;"></i></div>
                    <div class="ox-feature-body">
                        <h6>MikroTik Integration</h6>
                        <p>Auto-configure PPPoE profiles and manage hotspot users via RouterOS API in real-time.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#dcfce7;"><i class="bx bx-money" style="color:#16a34a;"></i></div>
                    <div class="ox-feature-body">
                        <h6>M-Pesa STK Push</h6>
                        <p>Payments are automatically reconciled and PPPoE accounts activated instantly after payment.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#fef3c7;"><i class="bx bx-group" style="color:#d97706;"></i></div>
                    <div class="ox-feature-body">
                        <h6>Subscriber Management</h6>
                        <p>Handle expirations, renewals, speed upgrades, and bulk operations effortlessly.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#e0f2fe;"><i class="bx bx-line-chart" style="color:#0284c7;"></i></div>
                    <div class="ox-feature-body">
                        <h6>Revenue Reports</h6>
                        <p>Daily, weekly, and monthly analytics with visual charts and growth metrics.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="ox-feature-card">
                    <div class="ox-feature-icon" style="background:#fff7ed;"><i class="bx bx-bot" style="color:#ea580c;"></i></div>
                    <div class="ox-feature-body">
                        <h6>AI Assistant (OxBot)</h6>
                        <p>Built-in AI chatbot that helps customers troubleshoot and navigate the portal 24/7.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ PORTALS ══ --}}
<section class="ox-portals-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="ox-section-tag">Portals</div>
            <h2 class="ox-section-title">Quick Access</h2>
            <p class="ox-section-sub mx-auto">Sign in to your portal to get started instantly.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('login') }}" class="ox-portal-card">
                    <div class="ox-portal-icon" style="background:#ede9fe;">
                        <i class="bx bx-user" style="color:#7c3aed;"></i>
                    </div>
                    <div class="ox-portal-name">Customer</div>
                    <div class="ox-portal-desc">Self-service portal</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('seller.login') }}" class="ox-portal-card">
                    <div class="ox-portal-icon" style="background:#fef3c7;">
                        <i class="bx bx-store" style="color:#d97706;"></i>
                    </div>
                    <div class="ox-portal-name">Seller / Agent</div>
                    <div class="ox-portal-desc">Reseller dashboard</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('admin.login') }}" class="ox-portal-card">
                    <div class="ox-portal-icon" style="background:#e0e7ff;">
                        <i class="bx bx-shield-quarter" style="color:#4f46e5;"></i>
                    </div>
                    <div class="ox-portal-name">ISP Admin</div>
                    <div class="ox-portal-desc">Tenant management</div>
                </a>
            </div>
            @if(Route::has('community.login'))
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('community.login') }}" class="ox-portal-card">
                    <div class="ox-portal-icon" style="background:#d1fae5;">
                        <i class="bx bx-group" style="color:#059669;"></i>
                    </div>
                    <div class="ox-portal-name">Community</div>
                    <div class="ox-portal-desc">Forum &amp; support</div>
                </a>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- ══ BOTTOM CTA ══ --}}
<section class="ox-cta-section">
    <div class="container">
        <h2>Ready to get started?</h2>
        <p>Sign in to your portal or contact us to set up your ISP account today.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('admin.login') }}" class="ox-btn-white">
                <i class="bx bx-shield-quarter"></i> ISP Admin Login
            </a>
            <a href="{{ route('login') }}" class="ox-btn-white-outline">
                <i class="bx bx-user"></i> Customer Login
            </a>
        </div>
    </div>
</section>

@endsection