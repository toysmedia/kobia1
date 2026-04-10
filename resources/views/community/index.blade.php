@extends('community.layouts.app')
@section('title', 'Home')
@section('content')

@if($pinnedPosts->count())
<div class="mb-4">
    @foreach($pinnedPosts as $pinned)
    <div class="card post-card mb-2 border-warning">
        <div class="card-body py-2 px-3 d-flex align-items-center gap-3">
            <i class="bi bi-pin-fill text-warning"></i>
            <a href="{{ route('community.posts.show', $pinned->slug) }}" class="text-decoration-none fw-semibold flex-grow-1">{{ $pinned->title }}</a>
            <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $pinned->category->name ?? '' }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($featuredPosts->count())
<div class="mb-4">
    <h6 class="fw-bold text-muted text-uppercase small mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Featured</h6>
    <div class="row g-3">
        @foreach($featuredPosts as $featured)
        <div class="col-sm-6">
            <div class="card post-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="badge bg-{{ $featured->type === 'announcement' ? 'danger' : ($featured->type === 'question' ? 'info' : 'secondary') }}-subtle text-{{ $featured->type === 'announcement' ? 'danger' : ($featured->type === 'question' ? 'info' : 'secondary') }}-emphasis post-type-badge mb-2">{{ $featured->type }}</span>
                    <h6 class="fw-semibold mb-1">
                        <a href="{{ route('community.posts.show', $featured->slug) }}" class="text-decoration-none">{{ Str::limit($featured->title, 70) }}</a>
                    </h6>
                    <small class="text-muted">by {{ $featured->user->name ?? 'Unknown' }} &middot; {{ $featured->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-clock me-2 text-primary"></i>Latest Posts</h6>
    <a href="{{ route('community.posts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Post</a>
</div>

@forelse($latestPosts as $post)
<div class="card post-card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-3">
            <div class="d-none d-sm-flex flex-column align-items-center text-center" style="min-width:50px;">
                <span class="fw-bold text-primary fs-5">{{ $post->likes_count }}</span>
                <small class="text-muted" style="font-size:.65rem;">likes</small>
                <span class="fw-bold mt-2">{{ $post->replies_count }}</span>
                <small class="text-muted" style="font-size:.65rem;">replies</small>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : ($post->type === 'question' ? 'info' : 'secondary') }}-subtle text-{{ $post->type === 'announcement' ? 'danger' : ($post->type === 'question' ? 'info' : 'secondary') }}-emphasis post-type-badge">{{ $post->type }}</span>
                    @if($post->category)
                    <a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none post-type-badge">{{ $post->category->name }}</a>
                    @endif
                    @foreach($post->tags->take(3) as $tag)
                    <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge bg-secondary-subtle text-secondary-emphasis">#{{ $tag->name }}</a>
                    @endforeach
                </div>
                <h6 class="fw-semibold mb-1">
                    <a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a>
                </h6>
                <p class="text-muted small mb-2">{{ Str::limit(strip_tags($post->body), 120) }}</p>
                <div class="d-flex align-items-center gap-3 text-muted small">
                    <a href="{{ route('community.profile.show', $post->user->id ?? 0) }}" class="text-decoration-none text-muted">
                        <i class="bi bi-person-circle me-1"></i>{{ $post->user->name ?? 'Unknown' }}
                    </a>
                    <span><i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}</span>
                    <span><i class="bi bi-eye me-1"></i>{{ number_format($post->views_count) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-square-text fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No posts yet</h5>
        <p class="text-muted">Be the first to start a discussion!</p>
        <a href="{{ route('community.posts.create') }}" class="btn btn-primary">Create Post</a>
    </div>
</div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $latestPosts->links() }}
</div>
@endsection

@section('sidebar')
@include('community.partials.sidebar', ['popularCategories' => $popularCategories, 'trendingTags' => $trendingTags])
@endsection
