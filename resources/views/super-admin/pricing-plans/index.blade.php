@extends('layouts.super-admin')
@section('title', 'Pricing Plans')
@section('page-title', 'Pricing Plans')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Pricing Plans</h5>
        <small class="text-muted">Manage subscription plans and feature flags</small>
    </div>
    <a href="{{ route('super-admin.pricing-plans.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>New Plan
    </a>
</div>

<div class="row g-3">
@forelse($plans as $plan)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 {{ !$plan->is_active ? 'opacity-75' : '' }}">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 fw-bold">{{ $plan->name }}</h6>
                    <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="text-end">
                    <div class="fs-4 fw-bold text-primary">KES {{ number_format($plan->price, 0) }}</div>
                    <small class="text-muted">/ {{ $plan->billing_cycle }}</small>
                </div>
            </div>
            <div class="card-body">
                @if($plan->description)
                    <p class="text-muted small mb-3">{{ $plan->description }}</p>
                @endif
                <ul class="list-unstyled small mb-3">
                    @if($plan->max_customers)
                        <li class="mb-1"><i class="bi bi-people me-2 text-primary"></i>Up to {{ number_format($plan->max_customers) }} customers</li>
                    @else
                        <li class="mb-1"><i class="bi bi-people me-2 text-success"></i>Unlimited customers</li>
                    @endif
                    @if($plan->max_routers)
                        <li class="mb-1"><i class="bi bi-router me-2 text-primary"></i>Up to {{ $plan->max_routers }} routers</li>
                    @else
                        <li class="mb-1"><i class="bi bi-router me-2 text-success"></i>Unlimited routers</li>
                    @endif
                    @if($plan->featureFlags->count())
                        @foreach($plan->featureFlags->take(4) as $flag)
                            <li class="mb-1"><i class="bi bi-check2 me-2 text-success"></i>{{ $flag->feature_name }}</li>
                        @endforeach
                        @if($plan->featureFlags->count() > 4)
                            <li class="text-muted">+ {{ $plan->featureFlags->count() - 4 }} more features…</li>
                        @endif
                    @endif
                </ul>
                <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted"><i class="bi bi-buildings me-1"></i>{{ $plan->tenants_count }} tenant(s)</small>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3 d-flex gap-2">
                <a href="{{ route('super-admin.pricing-plans.edit', $plan) }}" class="btn btn-sm btn-outline-warning flex-grow-1">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('super-admin.pricing-plans.destroy', $plan) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            data-confirm="Delete plan {{ e($plan->name) }}? Tenants on this plan will be unlinked.">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-0">No pricing plans yet. <a href="{{ route('super-admin.pricing-plans.create') }}">Create your first plan</a>.</p>
            </div>
        </div>
    </div>
@endforelse
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.closest('form').addEventListener('submit', function (e) {
        if (!confirm(btn.dataset.confirm)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
