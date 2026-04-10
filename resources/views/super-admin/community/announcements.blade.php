@extends('layouts.super-admin')
@section('title', 'Community - Announcements')
@section('page-title', 'Community Announcements')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-megaphone me-2 text-danger"></i>Announcements</h5>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal"><i class="bi bi-plus-lg me-1"></i>Post Announcement</button>
        <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Author</th><th>Pinned</th><th>Views</th><th>Replies</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($announcements as $ann)
                <tr>
                    <td><a href="{{ route('community.posts.show', $ann->slug) }}" target="_blank" class="fw-semibold text-decoration-none">{{ Str::limit($ann->title, 60) }}</a></td>
                    <td><small>{{ $ann->user->name ?? 'Unknown' }}</small></td>
                    <td>
                        @if($ann->is_pinned)<span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-pin-fill"></i> Pinned</span>@else<span class="text-muted">—</span>@endif
                    </td>
                    <td><small>{{ number_format($ann->views_count) }}</small></td>
                    <td><small>{{ $ann->replies_count }}</small></td>
                    <td><small class="text-muted">{{ $ann->created_at->format('M d, Y') }}</small></td>
                    <td>
                        <form method="POST" action="{{ route('super-admin.community.posts.pin', $ann->id) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning" style="font-size:.75rem;" title="{{ $ann->is_pinned ? 'Unpin' : 'Pin' }}">
                                <i class="bi bi-pin{{ $ann->is_pinned ? '-fill' : '' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No announcements yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $announcements->links() }}</div>
</div>

{{-- New Announcement Modal --}}
<div class="modal fade" id="newAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('super-admin.community.announcements.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-bold"><i class="bi bi-megaphone me-2 text-danger"></i>Post Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required minlength="10" maxlength="200" placeholder="Announcement title...">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category...</option>
                                @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="announcementPinned">
                                <label class="form-check-label" for="announcementPinned">Pin this announcement</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="8" required minlength="20" placeholder="Write your announcement..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-megaphone me-2"></i>Post Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
