@extends('admin.layouts.app')
@section('title', 'Resellers')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Resellers</h5>
            </div>
            <a href="{{ route('admin.isp.resellers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Reseller
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="col-sm-12 mb-3">
        <div class="card">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Name / Phone / Email"
                               value="{{ request('q') }}">
                    </div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="{{ route('admin.isp.resellers.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Balance (KES)</th>
                                <th>Subscribers</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resellers as $reseller)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $reseller->name }}</strong></td>
                                <td>{{ $reseller->phone ?? '-' }}</td>
                                <td>{{ $reseller->email ?? '-' }}</td>
                                <td>{{ $reseller->location ?? '-' }}</td>
                                <td>
                                    <span class="{{ ($reseller->balance ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($reseller->balance ?? 0, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">{{ $reseller->subscribers_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($reseller->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.isp.resellers.edit', $reseller) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.isp.resellers.destroy', $reseller) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete reseller {{ addslashes($reseller->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No resellers found. <a href="{{ route('admin.isp.resellers.create') }}">Add one now.</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($resellers instanceof \Illuminate\Pagination\LengthAwarePaginator && $resellers->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $resellers->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
