<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" data-bg-class="bg-menu-theme">
    <div class="app-brand demo mb-3" style="border-bottom: 1px solid #eee;">
        <a href="{{ route('admin.isp.dashboard') }}" class="app-brand-link">
            @if(config('settings.system_general.logo_path'))
                <img class="w-100" src="{{ asset('storage/' . config('settings.system_general.logo_path')) }}">
            @else
            <i class='bx bx-station' style="font-size: 35px;"></i>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('settings.system_general.logo_text', 'iNetto') }}</span>
            @endif
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>
    <div class="menu-inner-shadow" style="display: none;"></div>
    <ul class="menu-inner py-1 ps ps--active-y">
        <li class="menu-item {{ is_active_menu('admin.isp.dashboard') }}">
            <a href="{{ route('admin.isp.dashboard') }}" class="menu-link" data-tour="dashboard">
                <i class="menu-icon tf-icons bx bx-tachometer"></i>
                <div class="text-truncate">Dashboard</div>
            </a>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.subscribers.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle" data-tour="subscribers">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div class="text-truncate">Subscribers</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.subscribers.pppoe') }}">
                    <a href="{{ route('admin.isp.subscribers.pppoe') }}" class="menu-link"><div class="text-truncate">PPPoE Subscribers</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.subscribers.hotspot') }}">
                    <a href="{{ route('admin.isp.subscribers.hotspot') }}" class="menu-link"><div class="text-truncate">Hotspot Subscribers</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.expired_pppoe.', true) }}">
                    <a href="{{ route('admin.isp.expired_pppoe.index') }}" class="menu-link"><div class="text-truncate">Expired PPPoE</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.packages.', true) }}">
            <a href="{{ route('admin.isp.packages.index') }}" class="menu-link" data-tour="packages">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div class="text-truncate">Packages</div>
            </a>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.routers.', true) }} {{ is_active_menu('admin.isp.mikrotik_monitor.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle" data-tour="routers">
                <i class="menu-icon tf-icons bx bx-wifi"></i>
                <div class="text-truncate">Routers</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.routers.', true) }}">
                    <a href="{{ route('admin.isp.routers.index') }}" class="menu-link"><div class="text-truncate">All Routers</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.mikrotik_monitor.', true) }}">
                    <a href="{{ route('admin.isp.mikrotik_monitor.index') }}" class="menu-link"><div class="text-truncate">MikroTik Monitor</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.payments.', true) }} {{ is_active_menu('admin.isp.ereceipts.', true) }} {{ is_active_menu('admin.isp.sessions.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle" data-tour="payments">
                <i class="menu-icon tf-icons bx bx-money-withdraw"></i>
                <div class="text-truncate">Payments</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.payments.', true) }}">
                    <a href="{{ route('admin.isp.payments.index') }}" class="menu-link"><div class="text-truncate">All Payments</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.ereceipts.', true) }}">
                    <a href="{{ route('admin.isp.ereceipts.index') }}" class="menu-link"><div class="text-truncate">e-Receipts</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.sessions.', true) }}">
                    <a href="{{ route('admin.isp.sessions.index') }}" class="menu-link"><div class="text-truncate">Live Sessions</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.expenses.', true) }} {{ is_active_menu('admin.isp.expense_categories.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
                <div class="text-truncate">Expenses</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.expenses.index') }}">
                    <a href="{{ route('admin.isp.expenses.index') }}" class="menu-link"><div class="text-truncate">All Expenses</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.expenses.create') }}">
                    <a href="{{ route('admin.isp.expenses.create') }}" class="menu-link"><div class="text-truncate">Add Expense</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.expense_categories.', true) }}">
                    <a href="{{ route('admin.isp.expense_categories.index') }}" class="menu-link"><div class="text-truncate">Categories</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.expenses.report') }}">
                    <a href="{{ route('admin.isp.expenses.report') }}" class="menu-link"><div class="text-truncate">Expense Report</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.messaging.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-message-rounded-dots"></i>
                <div class="text-truncate">Messaging</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.messaging.sms') }}">
                    <a href="{{ route('admin.isp.messaging.sms') }}" class="menu-link"><div class="text-truncate">SMS</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.messaging.whatsapp') }}">
                    <a href="{{ route('admin.isp.messaging.whatsapp') }}" class="menu-link"><div class="text-truncate">WhatsApp</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.messaging.email') }}">
                    <a href="{{ route('admin.isp.messaging.email') }}" class="menu-link"><div class="text-truncate">Email</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.messaging.logs') }}">
                    <a href="{{ route('admin.isp.messaging.logs') }}" class="menu-link"><div class="text-truncate">Message Logs</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.reports.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle" data-tour="reports">
                <i class="menu-icon tf-icons bx bx-line-chart"></i>
                <div class="text-truncate">Reports</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.reports.pppoe_sales') }}">
                    <a href="{{ route('admin.isp.reports.pppoe_sales') }}" class="menu-link"><div class="text-truncate">PPPoE Sales</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.reports.hotspot_sales') }}">
                    <a href="{{ route('admin.isp.reports.hotspot_sales') }}" class="menu-link"><div class="text-truncate">Hotspot Sales</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.reports.monthly_combined') }}">
                    <a href="{{ route('admin.isp.reports.monthly_combined') }}" class="menu-link"><div class="text-truncate">Monthly Combined</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.reports.sales_by_package') }}">
                    <a href="{{ route('admin.isp.reports.sales_by_package') }}" class="menu-link"><div class="text-truncate">Sales by Package</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.reports.revenue_summary') }}">
                    <a href="{{ route('admin.isp.reports.revenue_summary') }}" class="menu-link"><div class="text-truncate">Revenue Summary</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.maps.', true) }}">
            <a href="{{ route('admin.isp.maps.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map-alt"></i>
                <div class="text-truncate">Maps</div>
            </a>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.access.', true) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div class="text-truncate">Access Control</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ is_active_menu('admin.isp.access.roles.', true) }}">
                    <a href="{{ route('admin.isp.access.roles.index') }}" class="menu-link"><div class="text-truncate">Roles &amp; Permissions</div></a>
                </li>
                <li class="menu-item {{ is_active_menu('admin.isp.access.users.', true) }}">
                    <a href="{{ route('admin.isp.access.users.index') }}" class="menu-link"><div class="text-truncate">Users (Workers)</div></a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.resellers.', true) }}">
            <a href="{{ route('admin.isp.resellers.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div class="text-truncate">Resellers</div>
            </a>
        </li>
        <li class="menu-item {{ is_active_menu('admin.isp.settings.', true) }}">
            <a href="{{ route('admin.isp.settings.index') }}" class="menu-link" data-tour="settings">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div class="text-truncate">ISP Settings</div>
            </a>
        </li>
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Account</span></li>
        <li class="menu-item {{ is_active_menu('admin.notifications.index') }}">
            <a href="{{ route('admin.notifications.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div class="text-truncate">Notifications</div>
            </a>
        </li>
        <li class="menu-item {{ is_active_menu('admin.profile.index') }}">
            <a href="{{ route('admin.profile.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div class="text-truncate">Profile</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link" onclick="event.preventDefault(); if(confirm('Are you sure you want to logout?')) document.getElementById('logout-form').submit();">
                <i class="menu-icon tf-icons bx bx-power-off"></i>
                <div class="text-truncate">Logout</div>
            </a>
        </li>
    </ul>
</aside>