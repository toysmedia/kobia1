@extends('layouts.super-admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary"><i class="bi bi-buildings fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Tenants</div>
                    <div class="fs-4 fw-bold">{{ $totalTenants }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle fs-4"></i></div>
                <div>
                    <div class="text-muted small">Active Tenants</div>
                    <div class="fs-4 fw-bold">{{ $activeTenants }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning"><i class="bi bi-pause-circle fs-4"></i></div>
                <div>
                    <div class="text-muted small">Suspended</div>
                    <div class="fs-4 fw-bold">{{ $suspendedTenants }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info"><i class="bi bi-currency-dollar fs-4"></i></div>
                <div>
                    <div class="text-muted small">MRR (KES)</div>
                    <div class="fs-4 fw-bold">{{ number_format($mrr, 0) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold">Monthly Revenue (Last 12 Months)</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold">Quick Actions</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-plus-circle me-2"></i>Add New Tenant</a>
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-buildings me-2"></i>View All Tenants</a>
                <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-credit-card me-2"></i>Subscriptions</a>
                <a href="{{ route('super-admin.sms-gateway.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-chat-dots me-2"></i>SMS Gateway</a>
                <a href="{{ route('super-admin.pricing-plans.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-tags me-2"></i>Pricing Plans</a>
                <a href="{{ route('super-admin.cms.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-file-text me-2"></i>CMS Content</a>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-journal-text me-2"></i>Audit Logs</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Recent Registrations</h6>
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>Subdomain</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentTenants as $tenant)
                            <tr>
                                <td><a href="{{ route('super-admin.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                                <td><code>{{ $tenant->subdomain }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $tenant->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No tenants yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Recent Payments</h6>
                <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Tenant</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->tenant->name ?? 'N/A' }}</td>
                                <td>KES {{ number_format($payment->amount, 0) }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($payment->payment_method) }}</span></td>
                                <td class="text-muted small">{{ $payment->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No payments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const revenueData = @json($monthlyRevenue);
const labels = Object.keys(revenueData);
const values = Object.values(revenueData);
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{ label: 'Revenue (KES)', data: values, backgroundColor: 'rgba(13,110,253,0.7)', borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
