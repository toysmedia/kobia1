@extends('layouts.super-admin')
@section('title', $tenant->name . ' — Tenant Detail')
@section('page-title', 'Tenant Detail')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.tenants.index') }}">Tenants</a></li>
        <li class="breadcrumb-item active">{{ $tenant->name }}</li>
    </ol>
</nav>

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h5 class="mb-1 fw-bold">{{ $tenant->name }}</h5>
        <code class="text-muted">{{ $tenant->subdomain }}.{{ config('app.domain', 'oxnet.co.ke') }}</code>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('super-admin.tenants.impersonate', $tenant) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-person-badge me-1"></i>Impersonate
            </button>
        </form>
        <form method="POST" action="{{ route('super-admin.tenants.maintenance', $tenant) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-{{ $tenant->maintenance_mode ? 'success' : 'secondary' }} btn-sm">
                <i class="bi bi-tools me-1"></i>{{ $tenant->maintenance_mode ? 'Disable Maintenance' : 'Enable Maintenance' }}
            </button>
        </form>
        @if($tenant->status === 'active')
        <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="bi bi-pause-circle me-1"></i>Suspend
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-check-circle me-1"></i>Activate
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- General Info --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>General Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Name</dt>
                    <dd class="col-sm-7">{{ $tenant->name }}</dd>

                    <dt class="col-sm-5 text-muted">Email</dt>
                    <dd class="col-sm-7">{{ $tenant->email }}</dd>

                    <dt class="col-sm-5 text-muted">Subdomain</dt>
                    <dd class="col-sm-7"><code>{{ $tenant->subdomain }}</code></dd>

                    <dt class="col-sm-5 text-muted">Database</dt>
                    <dd class="col-sm-7"><code>{{ $tenant->database ?? 'N/A' }}</code></dd>

                    <dt class="col-sm-5 text-muted">Status</dt>
                    <dd class="col-sm-7">
                        @php
                            $badge = match($tenant->status) {
                                'active'    => 'success',
                                'suspended' => 'warning',
                                'expired'   => 'danger',
                                'trial'     => 'info',
                                default     => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($tenant->status) }}</span>
                    </dd>

                    <dt class="col-sm-5 text-muted">Maintenance Mode</dt>
                    <dd class="col-sm-7">
                        @if($tenant->maintenance_mode)
                            <span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>Enabled</span>
                        @else
                            <span class="text-muted">Disabled</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5 text-muted">Registered</dt>
                    <dd class="col-sm-7">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Subscription Info --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-credit-card me-2 text-success"></i>Subscription</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Plan</dt>
                    <dd class="col-sm-7">{{ $tenant->plan->name ?? '—' }}</dd>

                    <dt class="col-sm-5 text-muted">Price</dt>
                    <dd class="col-sm-7">KES {{ number_format($tenant->plan->price ?? 0, 0) }} / {{ $tenant->plan->billing_cycle ?? '—' }}</dd>

                    <dt class="col-sm-5 text-muted">Expires At</dt>
                    <dd class="col-sm-7">
                        @if($tenant->subscription_expires_at)
                            {{ \Carbon\Carbon::parse($tenant->subscription_expires_at)->format('d M Y') }}
                            @if(\Carbon\Carbon::parse($tenant->subscription_expires_at)->isPast())
                                <span class="badge bg-danger ms-1">Expired</span>
                            @elseif(\Carbon\Carbon::parse($tenant->subscription_expires_at)->diffInDays() <= 7)
                                <span class="badge bg-warning text-dark ms-1">Expiring Soon</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5 text-muted">Location</dt>
                    <dd class="col-sm-7">
                        @if($tenant->lat && $tenant->lng)
                            {{ $tenant->lat }}, {{ $tenant->lng }}
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-info"></i>Recent Subscription Payments</h6>
                <a href="{{ route('super-admin.subscriptions.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Plan</th>
                                <th>Method</th>
                                <th>M-Pesa Receipt</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($tenant->subscriptionPayments as $payment)
                            <tr>
                                <td class="text-muted small">{{ $payment->created_at->format('d M Y') }}</td>
                                <td>KES {{ number_format($payment->amount, 0) }}</td>
                                <td>{{ $payment->plan->name ?? '—' }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($payment->payment_method ?? 'N/A') }}</span></td>
                                <td><code>{{ $payment->mpesa_receipt ?? '—' }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No payments recorded</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
