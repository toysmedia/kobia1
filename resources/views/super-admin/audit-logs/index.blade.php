@extends('layouts.super-admin')
@section('title', 'Audit Logs')
@section('page-title', 'System Audit Logs')

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-3 pb-0">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-funnel me-2 text-primary"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.audit-logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Action</label>
                <input type="text" name="action" value="{{ request('action') }}" class="form-control form-control-sm" placeholder="e.g. tenant_created">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Tenant</label>
                <select name="tenant_id" class="form-select form-select-sm">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected(request('tenant_id') == $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Audit Trail</h6>
        <small class="text-muted">{{ $logs->total() }} total records</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Tenant</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small text-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td>
                            @php
                                $actionColor = match(true) {
                                    str_contains($log->action, 'created')    => 'success',
                                    str_contains($log->action, 'deleted')    => 'danger',
                                    str_contains($log->action, 'updated')    => 'warning',
                                    str_contains($log->action, 'login')      => 'info',
                                    str_contains($log->action, 'suspended')  => 'warning',
                                    default                                  => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $actionColor }}">{{ $log->action }}</span>
                        </td>
                        <td class="small">
                            <span class="text-muted">{{ ucfirst($log->user_type ?? 'system') }}</span>
                            @if($log->user_id)<br><code>#{{ $log->user_id }}</code>@endif
                        </td>
                        <td class="small">{{ $log->tenant->name ?? '—' }}</td>
                        <td class="small text-muted text-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        <td class="small text-muted" style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $log->user_agent }}">
                            {{ \Illuminate\Support\Str::limit($log->user_agent ?? '—', 40) }}
                        </td>
                        <td class="small">
                            @if(!empty($log->new_values))
                                @php $details = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true); @endphp
                                @if(isset($details['description']))
                                    {{ $details['description'] }}
                                @else
                                    <a href="#" class="text-decoration-none" data-bs-toggle="tooltip" title="{{ json_encode($details, JSON_PRETTY_PRINT) }}">View data</a>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-journal-text fs-2 d-block mb-2 opacity-25"></i>
                            No audit log entries found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</small>
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Enable Bootstrap tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
</script>
@endpush
