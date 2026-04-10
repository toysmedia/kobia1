@extends('layouts.super-admin')
@section('title', 'Payment #' . $subscription->id)
@section('page-title', 'Payment Detail')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.subscriptions.index') }}">Subscriptions</a></li>
        <li class="breadcrumb-item active">Payment #{{ $subscription->id }}</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-info"></i>Payment Record</h6>
                <span class="badge bg-{{ $subscription->status === 'completed' ? 'success' : ($subscription->status === 'pending' ? 'warning' : 'danger') }} fs-6">
                    {{ ucfirst($subscription->status) }}
                </span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Tenant</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('super-admin.tenants.show', $subscription->tenant_id) }}">
                            {{ $subscription->tenant->name ?? 'N/A' }}
                        </a>
                    </dd>

                    <dt class="col-sm-4 text-muted">Plan</dt>
                    <dd class="col-sm-8">{{ $subscription->plan->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Amount</dt>
                    <dd class="col-sm-8 fw-bold fs-5">KES {{ number_format($subscription->amount, 0) }}</dd>

                    <dt class="col-sm-4 text-muted">Payment Method</dt>
                    <dd class="col-sm-8"><span class="badge bg-secondary">{{ strtoupper($subscription->payment_method ?? 'N/A') }}</span></dd>

                    <dt class="col-sm-4 text-muted">M-Pesa Receipt</dt>
                    <dd class="col-sm-8"><code>{{ $subscription->mpesa_receipt ?? '—' }}</code></dd>

                    <dt class="col-sm-4 text-muted">Phone</dt>
                    <dd class="col-sm-8">{{ $subscription->phone ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Paid At</dt>
                    <dd class="col-sm-8">{{ $subscription->created_at->format('d M Y, H:i:s') }}</dd>

                    <dt class="col-sm-4 text-muted">Subscription Period</dt>
                    <dd class="col-sm-8">
                        @if($subscription->starts_at && $subscription->expires_at)
                            {{ \Carbon\Carbon::parse($subscription->starts_at)->format('d M Y') }}
                            — {{ \Carbon\Carbon::parse($subscription->expires_at)->format('d M Y') }}
                        @else
                            N/A
                        @endif
                    </dd>
                </dl>

                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                    <form method="POST" action="{{ route('super-admin.subscriptions.extend', $subscription) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Extend subscription by 30 days?')">
                            <i class="bi bi-calendar-plus me-1"></i>Extend 30 Days
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
