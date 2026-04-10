@extends('layouts.super-admin')
@section('title', 'Tenants')
@section('page-title', 'Tenant Management')

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

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">All Tenants</h5>
        <small class="text-muted">Manage registered ISP tenants</small>
    </div>
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>New Tenant
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, email, subdomain…">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    <option value="trial" @selected(request('status') === 'trial')>Trial</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Plan</label>
                <select name="plan_id" class="form-select form-select-sm">
                    <option value="">All Plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(request('plan_id') == $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tenantsTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Subdomain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Maintenance</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="fw-semibold text-decoration-none">{{ $tenant->name }}</a>
                            <br><small class="text-muted">{{ $tenant->email }}</small>
                        </td>
                        <td><code>{{ $tenant->subdomain }}.{{ config('app.domain', 'oxnet.co.ke') }}</code></td>
                        <td>{{ $tenant->plan->name ?? '—' }}</td>
                        <td>
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
                        </td>
                        <td>
                            @if($tenant->maintenance_mode)
                                <span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>On</span>
                            @else
                                <span class="text-muted small">Off</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $tenant->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('super-admin.tenants.show', $tenant) }}">
                                            <i class="bi bi-eye me-2 text-info"></i>View
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('super-admin.tenants.edit', $tenant) }}">
                                            <i class="bi bi-pencil me-2 text-warning"></i>Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('super-admin.tenants.maintenance', $tenant) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-tools me-2 text-secondary"></i>
                                                {{ $tenant->maintenance_mode ? 'Disable Maintenance' : 'Enable Maintenance' }}
                                            </button>
                                        </form>
                                    </li>
                                    @if($tenant->status === 'active')
                                    <li>
                                        <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning">
                                                <i class="bi bi-pause-circle me-2"></i>Suspend
                                            </button>
                                        </form>
                                    </li>
                                    @else
                                    <li>
                                        <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-success">
                                                <i class="bi bi-check-circle me-2"></i>Activate
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    <li>
                                        <form method="POST" action="{{ route('super-admin.tenants.impersonate', $tenant) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-person-badge me-2 text-primary"></i>Impersonate
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('super-admin.tenants.destroy', $tenant) }}"
                                              class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    data-confirm="Delete tenant {{ e($tenant->name) }}? This cannot be easily undone.">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-buildings fs-2 d-block mb-2 opacity-25"></i>
                            No tenants found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tenants->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $tenants->firstItem() }}–{{ $tenants->lastItem() }} of {{ $tenants->total() }}</small>
        {{ $tenants->links('pagination::bootstrap-5') }}
    </div>
    @endif
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
