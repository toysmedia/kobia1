@extends('admin.layouts.app')
@section('title', 'Subscribers')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Subscribers</h5>
            </div>
            <a href="{{ route('admin.isp.subscribers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Subscriber
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="col-sm-12 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Search / Filter</h6>
                <div>
                    <button id="btnSearch" type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('filterForm').submit()">Search</button>
                    <a href="{{ route('admin.isp.subscribers.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Clear</a>
                </div>
            </div>
            <div class="card-body pb-2">
                <form id="filterForm" method="GET" action="{{ route('admin.isp.subscribers.index') }}">
                    <div class="row">
                        <div class="col-sm-3 mb-3">
                            <input type="text" name="q" class="form-control" placeholder="Name / Username / Phone"
                                   value="{{ request('q') }}">
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="package_id" class="form-select">
                                <option value="">All Packages</option>
                                @foreach($packages ?? [] as $pkg)
                                    <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="pppoe"   {{ request('type') == 'pppoe'   ? 'selected' : '' }}>PPPoE</option>
                                <option value="hotspot" {{ request('type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                            </select>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="expired"  {{ request('status') == 'expired'  ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="router_id" class="form-select">
                                <option value="">All Routers</option>
                                @foreach($routers ?? [] as $router)
                                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>{{ $router->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Form --}}
    <div class="col-sm-12">
        <form id="bulkForm" action="{{ route('admin.isp.subscribers.bulk') }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="bulkAction">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div id="bulkActionsBar" class="d-none">
                        <span class="me-3 text-muted small"><span id="selectedCount">0</span> selected</span>
                        <button type="button" class="btn btn-sm btn-outline-success me-2"
                                onclick="submitBulk('activate')">Activate</button>
                        <button type="button" class="btn btn-sm btn-outline-warning me-2"
                                onclick="submitBulk('deactivate')">Deactivate</button>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="submitBulk('delete')">Delete</button>
                    </div>
                    <div class="ms-auto">
                        <small class="text-muted">Total: {{ $subscribers->total() ?? count($subscribers) }}</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="30"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Phone</th>
                                    <th>Package</th>
                                    <th>Type</th>
                                    <th>Router</th>
                                    <th>Status</th>
                                    <th>Expires At</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $subscriber->id }}" class="form-check-input row-check"></td>
                                    <td><strong>{{ $subscriber->name }}</strong></td>
                                    <td><code>{{ $subscriber->username }}</code></td>
                                    <td>{{ $subscriber->phone ?? '-' }}</td>
                                    <td>{{ $subscriber->package->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $subscriber->type === 'pppoe' ? 'primary' : 'warning' }}">
                                            {{ strtoupper($subscriber->type ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>{{ $subscriber->router->name ?? '-' }}</td>
                                    <td>
                                        @if($subscriber->is_active)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subscriber->expires_at)
                                            @if($subscriber->expires_at->isPast())
                                                <span class="text-danger">{{ $subscriber->expires_at->format('d M Y') }}</span>
                                            @elseif($subscriber->expires_at->diffInDays() <= 3)
                                                <span class="text-warning">{{ $subscriber->expires_at->format('d M Y') }}</span>
                                            @else
                                                {{ $subscriber->expires_at->format('d M Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.isp.subscribers.show', $subscriber) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.isp.subscribers.edit', $subscriber) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.isp.subscribers.destroy', $subscriber) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete subscriber {{ addslashes($subscriber->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No subscribers found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator && $subscribers->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $subscribers->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const checkAll  = document.getElementById('checkAll');
const bulkBar   = document.getElementById('bulkActionsBar');
const countSpan = document.getElementById('selectedCount');

checkAll.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    const count = document.querySelectorAll('.row-check:checked').length;
    countSpan.textContent = count;
    bulkBar.classList.toggle('d-none', count === 0);
}

function submitBulk(action) {
    const count = document.querySelectorAll('.row-check:checked').length;
    if (!count) return;
    if (action === 'delete' && !confirm('Delete ' + count + ' subscriber(s)?')) return;
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkForm').submit();
}
</script>
@endpush
