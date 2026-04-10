{{-- Pricing Sidebar (Offcanvas)
     Shows the tenant's current plan and available upgrades.
--}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="pricingSidebar"
     aria-labelledby="pricingSidebarLabel" style="width:400px;max-width:100vw">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="pricingSidebarLabel">
            <i class="bx bx-tag text-primary"></i>
            Plans & Pricing
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        {{-- Current Plan --}}
        @if($currentPlan)
            <div class="card border-primary mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-0">{{ $currentPlan->name }}</h6>
                            <small class="text-muted">Current Plan</small>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="fs-4 fw-bold text-primary mb-2">
                        KES {{ number_format($currentPlan->price, 0) }}
                        <small class="fs-6 text-muted fw-normal">/ {{ $currentPlan->billing_cycle }}</small>
                    </div>
                    @if($currentPlan->description)
                        <p class="text-muted small mb-2">{{ $currentPlan->description }}</p>
                    @endif
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1">
                            <i class="bx bx-check text-success me-1"></i>
                            Up to {{ number_format($currentPlan->max_customers) }} customers
                        </li>
                        <li class="mb-1">
                            <i class="bx bx-check text-success me-1"></i>
                            Up to {{ number_format($currentPlan->max_routers) }} routers
                        </li>
                        @foreach((array) ($currentPlan->feature_flags ?? []) as $feature => $enabled)
                            @if($enabled)
                                <li class="mb-1">
                                    <i class="bx bx-check text-success me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $feature)) }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="bx bx-info-circle me-1"></i>
                No active subscription plan found.
            </div>
        @endif

        {{-- Subscription expiry --}}
        @if($tenant?->subscription_expires_at)
            <div class="alert {{ $tenant->isExpired() ? 'alert-danger' : 'alert-info' }} py-2 mb-4">
                <i class="bx bx-calendar me-1"></i>
                @if($tenant->isExpired())
                    Subscription expired on {{ $tenant->subscription_expires_at->format('d M Y') }}
                @else
                    Expires on {{ $tenant->subscription_expires_at->format('d M Y') }}
                    ({{ $tenant->subscription_expires_at->diffForHumans() }})
                @endif
            </div>
        @endif

        {{-- Available Plans --}}
        <h6 class="fw-semibold mb-3">Available Plans</h6>

        @foreach($allPlans as $plan)
            @if($plan->id !== ($currentPlan?->id))
                <div class="card mb-3 {{ $plan->price > ($currentPlan?->price ?? 0) ? 'border-primary' : 'border-0 bg-light' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">{{ $plan->name }}</span>
                            @if($plan->price > ($currentPlan?->price ?? 0))
                                <span class="badge bg-primary-subtle text-primary">Upgrade</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Downgrade</span>
                            @endif
                        </div>
                        <div class="fs-5 fw-bold text-primary mb-2">
                            KES {{ number_format($plan->price, 0) }}
                            <small class="fs-6 text-muted fw-normal">/ {{ $plan->billing_cycle }}</small>
                        </div>
                        <a href="/subscription/renew?plan={{ $plan->slug }}"
                           class="btn btn-sm {{ $plan->price > ($currentPlan?->price ?? 0) ? 'btn-primary' : 'btn-outline-secondary' }} w-100">
                            {{ $plan->price > ($currentPlan?->price ?? 0) ? 'Upgrade Now' : 'Switch Plan' }}
                        </a>
                    </div>
                </div>
            @endif
        @endforeach

        @if($allPlans->where('id', '!=', $currentPlan?->id)->isEmpty())
            <p class="text-muted small text-center py-3">No other plans available.</p>
        @endif

        <div class="text-center mt-3">
            <a href="/subscription/renew" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-refresh me-1"></i>Renew Current Plan
            </a>
        </div>
    </div>
</div>
