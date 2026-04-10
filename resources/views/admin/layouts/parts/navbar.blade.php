@php $user = auth('admin')->user(); @endphp
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

            <!-- Router Status Indicator -->
            <li class="nav-item me-2 d-none d-md-flex">
                <a href="{{ route('admin.isp.mikrotik_monitor.index') }}" id="navRouterStatus" title="MikroTik Router Status">
                    <span id="navRouterStatusDot"></span>
                    <span id="navRouterStatusText">Routers</span>
                </a>
            </li>
            <!-- /Router Status Indicator -->

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

            <!-- User -->
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
                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                    <small class="text-muted">Admin</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                            <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.change_password_form') }}"> <i class="bx bx-cog bx-md me-3"></i><span>Change Password</span> </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-action="restart-tour">
                            <i class="bx bx-help-circle bx-md me-3"></i><span>Restart Tour</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
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

{{-- Support Chat Offcanvas --}}
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
@push('scripts')
    <script>
        $(document).ready(function() {
            const users = @json(get_usernames());
            var data = [];for(const s in users)users.hasOwnProperty(s)&&data.push({value:users[s],url:window.BASE_URL+"/admin/users/"+s});function displaySuggestions(s){const e=$("#suggestions");e.empty(),0!==s.length?(s.forEach((s=>{e.append(`<div class="suggestion-item" data-url="${s.url}">${s.value}</div>`)})),e.show()):e.hide()}$("#navUserSearch").on("input",(function(){const s=$(this).val().toLowerCase();displaySuggestions(data.filter((e=>e.value.toLowerCase().includes(s))))})),$("#suggestions").on("click",".suggestion-item",(function(){const s=$(this).data("url");window.location.href=s})),$(document).click((function(s){$(s.target).closest("#navUserSearch, #suggestions").length||$("#suggestions").hide()}));
        });
    </script>
    <script>
        (function () {
            var statusUrl = '{{ route("admin.routers.status") }}';
            var dot  = document.getElementById('navRouterStatusDot');
            var text = document.getElementById('navRouterStatusText');

            function poll() {
                fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (routers) {
                        if (!routers || routers.length === 0) {
                            dot.className  = '';
                            text.textContent = 'No Routers';
                            return;
                        }
                        var total   = routers.length;
                        var online  = routers.filter(function (r) { return r.online; }).length;
                        var offline = total - online;

                        if (offline === 0) {
                            dot.className    = 'online';
                            text.textContent = online + ' Online';
                        } else if (online === 0) {
                            dot.className    = 'offline';
                            text.textContent = 'All Offline';
                        } else {
                            dot.className    = 'partial';
                            text.textContent = online + '/' + total + ' Online';
                        }
                    })
                    .catch(function () {
                        dot.className    = 'offline';
                        text.textContent = 'Status Error';
                    });
            }

            poll();
            setInterval(poll, 10000);
        })();
    </script>
@endpush