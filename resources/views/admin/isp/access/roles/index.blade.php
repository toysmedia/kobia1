@extends('admin.layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Roles &amp; Permissions</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">All Roles</h6>
                <a href="{{ route('admin.isp.access.roles.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus me-1"></i> Create Role</a>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($roles as $role)
                    <div class="col-md-4 mb-3">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0">{{ $role->display_name }}</h6>
                                        <small class="text-muted">{{ $role->name }}</small>
                                    </div>
                                    <span class="badge bg-primary">{{ $role->permissions_count }} permissions</span>
                                </div>
                                @if($role->description)
                                <p class="small text-muted mb-2">{{ $role->description }}</p>
                                @endif
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.isp.access.roles.edit', $role) }}" class="btn btn-xs btn-outline-warning"><i class="bx bx-edit"></i> Edit</a>
                                    @if($role->name !== 'super_admin')
                                    <form action="{{ route('admin.isp.access.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete role?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="bx bx-trash"></i> Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">No roles found. <a href="{{ route('admin.isp.access.roles.create') }}">Create one</a></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
