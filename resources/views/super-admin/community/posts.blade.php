@extends('layouts.super-admin')
@section('title', 'Community - Posts')
@section('page-title', 'Community Posts')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-chat-square-text me-2 text-primary"></i>Manage Posts</h5>
    <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search posts..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                    <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>Rejected</option>
                    <option value="flagged" {{ request('status')==='flagged'?'selected':'' }}>Flagged</option>
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
                    <tr><th>Title</th><th>Author</th><th>Category</th><th>Type</th><th>Status</th><th>Views</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                <tr>
                    <td style="max-width:250px;">
                        <a href="{{ route('community.posts.show', $post->slug) }}" target="_blank" class="text-decoration-none fw-semibold">{{ Str::limit($post->title, 60) }}</a>
                        <div>
                            @if($post->is_pinned)<span class="badge bg-warning-subtle text-warning-emphasis me-1"><i class="bi bi-pin"></i></span>@endif
                            @if($post->is_featured)<span class="badge bg-info-subtle text-info-emphasis me-1"><i class="bi bi-star"></i></span>@endif
                            @if($post->is_locked)<span class="badge bg-secondary-subtle text-secondary-emphasis"><i class="bi bi-lock"></i></span>@endif
                        </div>
                    </td>
                    <td><small>{{ $post->user->name ?? 'Unknown' }}</small></td>
                    <td><small>{{ $post->category->name ?? 'N/A' }}</small></td>
                    <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $post->type }}</span></td>
                    <td><span class="badge bg-{{ $post->status === 'approved' ? 'success' : ($post->status === 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $post->status === 'approved' ? 'success' : ($post->status === 'pending' ? 'warning' : 'danger') }}-emphasis">{{ $post->status }}</span></td>
                    <td><small>{{ number_format($post->views_count) }}</small></td>
                    <td><small class="text-muted">{{ $post->created_at->format('M d') }}</small></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            @if($post->status !== 'approved')
                            <form method="POST" action="{{ route('super-admin.community.posts.approve', $post->id) }}" class="d-inline">
                                @csrf<button class="btn btn-xs btn-success" title="Approve" style="font-size:.7rem; padding:.2rem .5rem;"><i class="bi bi-check"></i></button>
                            </form>
                            @endif
                            <button class="btn btn-xs btn-warning" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $post->id }}" style="font-size:.7rem; padding:.2rem .5rem;"><i class="bi bi-x"></i></button>
                            <form method="POST" action="{{ route('super-admin.community.posts.pin', $post->id) }}" class="d-inline">
                                @csrf<button class="btn btn-xs {{ $post->is_pinned ? 'btn-warning' : 'btn-outline-warning' }}" title="{{ $post->is_pinned ? 'Unpin' : 'Pin' }}" style="font-size:.7rem; padding:.2rem .5rem;"><i class="bi bi-pin"></i></button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.community.posts.feature', $post->id) }}" class="d-inline">
                                @csrf<button class="btn btn-xs {{ $post->is_featured ? 'btn-info' : 'btn-outline-info' }}" title="{{ $post->is_featured ? 'Unfeature' : 'Feature' }}" style="font-size:.7rem; padding:.2rem .5rem;"><i class="bi bi-star"></i></button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.community.posts.lock', $post->id) }}" class="d-inline">
                                @csrf<button class="btn btn-xs {{ $post->is_locked ? 'btn-secondary' : 'btn-outline-secondary' }}" title="{{ $post->is_locked ? 'Unlock' : 'Lock' }}" style="font-size:.7rem; padding:.2rem .5rem;"><i class="bi bi-lock"></i></button>
                            </form>
                        </div>
                        {{-- Reject Modal --}}
                        <div class="modal fade" id="rejectModal{{ $post->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('super-admin.community.posts.reject', $post->id) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Reject Post</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <label class="form-label">Reason</label>
                                            <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required placeholder="Explain why..."></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No posts found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $posts->links() }}</div>
</div>
@endsection
