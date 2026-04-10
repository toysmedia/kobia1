@extends('layouts.super-admin')
@section('title', 'Recycle Bin')
@section('page-title', 'System Recycle Bin')

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

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-3 pb-0">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-funnel me-2 text-primary"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.recycle-bin.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Tenant</label>
                <select name="tenant_id" class="form-select form-select-sm">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected(request('tenant_id') == $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Model Type</label>
                <select name="model_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" @selected(request('model_type') === $type)>{{ class_basename($type) }}</option>
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
                <a href="{{ route('super-admin.recycle-bin.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-trash me-2 text-danger"></i>Soft-Deleted Records</h6>
        <small class="text-muted">{{ $items->total() }} items</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Identifier</th>
                        <th>Tenant</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ class_basename($item->model_type) }}</span>
                        </td>
                        <td class="small">
                            @php
                                $data = is_array($item->data) ? $item->data : json_decode($item->data, true);
                                $identifier = $data['name'] ?? $data['title'] ?? $data['email'] ?? $data['subdomain'] ?? '#' . $item->model_id;
                            @endphp
                            <span class="fw-semibold">{{ $identifier }}</span>
                            <br><code class="text-muted">ID: {{ $item->model_id }}</code>
                        </td>
                        <td class="small">{{ $item->tenant->name ?? '—' }}</td>
                        <td class="small text-muted">
                            {{ ucfirst($item->deleted_by_type ?? '—') }}
                            @if($item->deleted_by_id)<br><code>#{{ $item->deleted_by_id }}</code>@endif
                        </td>
                        <td class="small text-muted text-nowrap">
                            {{ $item->created_at->format('d M Y H:i') }}
                            <br><span class="text-danger">{{ $item->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <form method="POST" action="{{ route('super-admin.recycle-bin.restore', $item->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this record?')">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('super-admin.recycle-bin.destroy', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this record? This cannot be undone.')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-trash fs-2 d-block mb-2 opacity-25"></i>
                            Recycle bin is empty.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</small>
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
