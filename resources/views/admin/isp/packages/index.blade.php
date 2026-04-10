@extends('admin.layouts.app')
@section('title', 'ISP Packages')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">ISP Packages</h5>
            </div>
            <a href="{{ route('admin.isp.packages.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Package
            </a>
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
                                <th>Type</th>
                                <th>Speed (Up / Down)</th>
                                <th>Price (KES)</th>
                                <th>Validity</th>
                                <th>Data Limit</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $package)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $package->name }}</strong></td>
                                <td>
                                    <span class="badge bg-label-{{ $package->type === 'pppoe' ? 'primary' : ($package->type === 'hotspot' ? 'warning' : 'info') }}">
                                        {{ strtoupper($package->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success">↑ {{ $package->speed_upload }} Mbps</span>
                                    /
                                    <span class="text-primary">↓ {{ $package->speed_download }} Mbps</span>
                                </td>
                                <td>KES {{ number_format($package->price, 2) }}</td>
                                <td>
                                    @if($package->validity_days)
                                        {{ $package->validity_days }}d
                                    @endif
                                    @if($package->validity_hours)
                                        {{ $package->validity_hours }}h
                                    @endif
                                </td>
                                <td>
                                    @if($package->data_limit_mb)
                                        {{ number_format($package->data_limit_mb / 1024, 1) }} GB
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </td>
                                <td>
                                    @if($package->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.isp.packages.edit', $package) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.isp.packages.destroy', $package) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete package {{ addslashes($package->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No packages found. <a href="{{ route('admin.isp.packages.create') }}">Create one now.</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($packages instanceof \Illuminate\Pagination\LengthAwarePaginator && $packages->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $packages->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
