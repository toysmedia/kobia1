@php $user = auth('admin')->user() ?? auth()->user(); @endphp
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="bx bx-menu bx-md"></i>
        </a>
    </div>
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center position-relative">
                <i class="bx bx-search bx-md"></i>
                <input
                    name="user_search_item"
                    id="navUserSearch"
                    type="text"
                    class="form-control border-0 shadow-none ps-1 ps-sm-2"
                    placeholder="Search..."
                    aria-label="Search..." autocomplete="new-username"/>
                <div id="suggestions" style="display: none;"></div>
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            {{-- ⏱ Subscription Countdown --}}
            @include('partials.subscription-countdown')

            {{-- 🔔 Notifications Bell --}}
            @include('partials.notifications-dropdown')

            {{-- 🌙☀️ Dark/Light Mode Toggle --}}
            <li class="nav-item me-1">
                <button id="theme-toggle"
                        class="nav-link btn btn-link p-2"
                        title="Toggle Dark/Light Mode"
                        aria-label="Toggle theme">
                    <i class="bx bx-moon bx-sm"></i>
                </button>
            </li>

            {{-- 💬 Support Chat --}}
            <li class="nav-item me-1" data-tour="support-chat">
                <button class="nav-link btn btn-link p-2"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#supportChatPanel"
                        aria-controls="supportChatPanel"
                        title="Support Chat"
                        aria-label="Open support chat">
                    <i class="bx bx-support bx-sm"></i>
                </button>
            </li>

            {{-- 🔗 View Pricing --}}
            <li class="nav-item me-1">
                <button class="nav-link btn btn-link p-2"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#pricingSidebar"
                        aria-controls="pricingSidebar"
                        title="View Pricing &amp; Plans"
                        aria-label="View pricing">
                    <i class="bx bx-tag bx-sm"></i>
                </button>
            </li>

            {{-- 🔗 Community Portal --}}
            <li class="nav-item me-2 d-none d-xl-flex">
                <a class="nav-link p-2 d-flex align-items-center gap-1 text-muted"
                   href="/community"
                   title="OxNet Community">
                    <i class="bx bx-group bx-sm"></i>
                    <span class="d-none d-xxl-inline small">Community</span>
                </a>
            </li>

            <!-- User dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                    class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/default_profile.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/default_profile.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $user?->name }}</h6>
                                    <small class="text-muted">Admin</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.change_password_form') }}">
                            <i class="bx bx-cog bx-md me-3"></i><span>Change Password</span>
                        </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                        <a class="dropdown-item" href="#" data-action="restart-tour">
                            <i class="bx bx-help-circle bx-md me-3"></i><span>Restart Tour</span>
                        </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="event.preventDefault(); if(confirm('Are you sure you want to logout?')) document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

{{-- Support Chat Offcanvas (initialised lazily when first opened) --}}
@php
    try {
        $chatMessages = \App\Models\Tenant\SupportMessage::latest()->limit(50)->get()->reverse()->values();
    } catch (\Throwable) {
        $chatMessages = collect();
    }
@endphp
@component('partials.support-chat', ['messages' => $chatMessages])
@endcomponent

{{-- Pricing Sidebar Offcanvas --}}
@php
    try {
        $pricingTenant   = app()->bound('current_tenant') ? app('current_tenant') : null;
        $pricingCurrent  = $pricingTenant?->plan;
        $pricingAllPlans = \App\Models\System\PricingPlan::where('is_active', true)->orderBy('sort_order')->get();
    } catch (\Throwable) {
        $pricingTenant   = null;
        $pricingCurrent  = null;
        $pricingAllPlans = collect();
    }
@endphp
@component('partials.pricing-sidebar', ['currentPlan' => $pricingCurrent, 'allPlans' => $pricingAllPlans, 'tenant' => $pricingTenant])
@endcomponent