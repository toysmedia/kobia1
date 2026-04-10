@extends('layouts.super-admin')
@section('title', 'Community - Users')
@section('page-title', 'Community Users')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Manage Community Users</h5>
    <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                    <option value="banned" {{ request('status')==='banned'?'selected':'' }}>Banned</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>User</th><th>Email</th><th>Reputation</th><th>Joined</th><th>Last Login</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <a href="{{ route('community.profile.show', $user->id) }}" target="_blank" class="fw-semibold text-decoration-none">{{ $user->name }}</a>
                        @if($user->is_verified)<i class="bi bi-patch-check-fill text-primary ms-1" title="Verified"></i>@endif
                    </td>
                    <td><small class="text-muted">{{ $user->email }}</small></td>
                    <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $user->reputation }}</span></td>
                    <td><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small></td>
                    <td><small class="text-muted">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</small></td>
                    <td>
                        @if($user->is_banned)
                        <span class="badge bg-danger-subtle text-danger-emphasis">Banned</span>
                        @else
                        <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                        @endif
                    </td>
                    <td>
                        @if(!$user->is_banned)
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#banModal{{ $user->id }}" style="font-size:.75rem;">Ban</button>
                        @else
                        <form method="POST" action="{{ route('super-admin.community.users.unban', $user->id) }}" class="d-inline">
                            @csrf<button class="btn btn-sm btn-outline-success" style="font-size:.75rem;">Unban</button>
                        </form>
                        @endif
                        {{-- Ban Modal --}}
                        <div class="modal fade" id="banModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('super-admin.community.users.ban', $user->id) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Ban {{ $user->name }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <label class="form-label">Reason</label>
                                            <textarea name="ban_reason" class="form-control form-control-sm" rows="3" required placeholder="Enter ban reason..."></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger btn-sm">Ban User</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
