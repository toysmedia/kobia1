@extends('community.layouts.app')
@section('title', 'Notifications')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-bell me-2 text-primary"></i>Followed Posts Activity</h5>
</div>

@forelse($followedPosts as $post)
<div class="card post-card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-bell-fill text-primary"></i>
            <div class="flex-grow-1">
                <h6 class="fw-semibold mb-1">
                    <a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a>
                </h6>
                <small class="text-muted">
                    {{ $post->replies_count }} {{ Str::plural('reply', $post->replies_count) }} &middot;
                    Updated {{ $post->updated_at->diffForHumans() }} &middot;
                    by {{ $post->user->name ?? 'Unknown' }}
                </small>
            </div>
            @if($post->category)
            <a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none">{{ $post->category->name }}</a>
            @endif
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No notifications</h5>
        <p class="text-muted">Follow posts to get notified of activity.</p>
        <a href="{{ route('community.posts.index') }}" class="btn btn-primary">Browse Posts</a>
    </div>
</div>
@endforelse
<div class="d-flex justify-content-center mt-4">{{ $followedPosts->links() }}</div>
@endsection
