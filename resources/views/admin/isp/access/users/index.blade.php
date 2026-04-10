@extends('admin.layouts.app')
@section('title', 'Workers')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Workers / Admin Users</h5>
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
                <h6 class="mb-0">All Workers</h6>
                <a href="{{ route('admin.isp.access.users.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-user-plus me-1"></i> Add Worker</a>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Mobile</th><th>Roles</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($workers as $worker)
                        <tr>
                            <td>{{ $worker->id }}</td>
                            <td>{{ $worker->name }}</td>
                            <td>{{ $worker->email }}</td>
                            <td>{{ $worker->mobile ?? '-' }}</td>
                            <td>
                                @foreach($worker->roles as $role)
                                <span class="badge bg-info">{{ $role->display_name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.isp.access.users.edit', $worker) }}" class="btn btn-xs btn-outline-warning"><i class="bx bx-edit"></i></a>
                                @if($worker->id !== auth('admin')->id())
                                <form action="{{ route('admin.isp.access.users.destroy', $worker) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this worker?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No workers found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
