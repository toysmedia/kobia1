{{-- Subscription Countdown Partial
     Shows a live countdown to subscription expiry in the navbar.
--}}
@php
    $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
    $expiresAt = $tenant?->subscription_expires_at;
@endphp

@if($tenant)
<li class="nav-item me-2 d-none d-xl-flex align-items-center">
    <div class="subscription-wrapper d-flex align-items-center gap-1"
         data-tour="subscription-countdown">
        <small class="text-muted d-none d-xxl-inline">Expires:</small>
        <div id="subscription-countdown"
             data-expires-at="{{ $expiresAt?->toISOString() ?? '' }}">
            {{-- Populated by subscription-countdown.js --}}
            <span class="badge bg-secondary">
                <i class="bx bx-loader-alt bx-spin"></i>
            </span>
        </div>
    </div>
</li>
@endif
