@extends('layouts.super-admin')
@section('title', 'Subscriptions')
@section('page-title', 'Subscription Payments')

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

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary"><i class="bi bi-currency-dollar fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Revenue</div>
                    <div class="fs-5 fw-bold">KES {{ number_format($payments->sum('amount'), 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success"><i class="bi bi-receipt fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Payments</div>
                    <div class="fs-5 fw-bold">{{ $payments->total() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.subscriptions.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Tenant</label>
                <select name="tenant_id" class="form-select form-select-sm">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" @selected(request('tenant_id') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tenant</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>M-Pesa Receipt</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <a href="{{ route('super-admin.tenants.show', $payment->tenant_id) }}" class="fw-semibold text-decoration-none">
                                {{ $payment->tenant->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td>{{ $payment->plan->name ?? '—' }}</td>
                        <td class="fw-semibold">KES {{ number_format($payment->amount, 0) }}</td>
                        <td><code>{{ $payment->mpesa_receipt ?? '—' }}</code></td>
                        <td><span class="badge bg-secondary">{{ strtoupper($payment->payment_method ?? 'N/A') }}</span></td>
                        <td>
                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $payment->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <a href="{{ route('super-admin.subscriptions.show', $payment) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-receipt fs-2 d-block mb-2 opacity-25"></i>
                            No subscription payments found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</small>
        {{ $payments->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
