@extends('community.layouts.app')
@section('title', 'Posts')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-chat-square-text me-2 text-primary"></i>All Posts</h5>
    @auth('community')
    <a href="{{ route('community.posts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Post</a>
    @endauth
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('community.posts.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search posts..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="question" {{ request('type') === 'question' ? 'selected' : '' }}>Question</option>
                    <option value="discussion" {{ request('type') === 'discussion' ? 'selected' : '' }}>Discussion</option>
                    <option value="article" {{ request('type') === 'article' ? 'selected' : '' }}>Article</option>
                    <option value="announcement" {{ request('type') === 'announcement' ? 'selected' : '' }}>Announcement</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select form-select-sm">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Popular</option>
                    <option value="unanswered" {{ request('sort') === 'unanswered' ? 'selected' : '' }}>Unanswered</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

@forelse($posts as $post)
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
                    <span class="d-none d-md-inline"><i class="bi bi-eye me-1"></i>{{ number_format($post->views_count) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-search fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No posts found</h5>
        @auth('community')
        <a href="{{ route('community.posts.create') }}" class="btn btn-primary mt-2">Create the first post</a>
        @endauth
    </div>
</div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $posts->links() }}
</div>
@endsection

@section('sidebar')
<div class="card sidebar-widget mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-grid me-2 text-primary"></i>Categories</div>
    <div class="card-body p-2">
        @foreach($categories as $cat)
        <a href="{{ route('community.categories.show', $cat->slug) }}" class="d-flex align-items-center p-2 rounded text-decoration-none mb-1" style="color:inherit;">
            <i class="bi bi-chevron-right me-2 text-muted small"></i>{{ $cat->name }}
        </a>
        @endforeach
    </div>
</div>
<div class="card sidebar-widget">
    <div class="card-header fw-semibold"><i class="bi bi-tags me-2 text-primary"></i>Tags</div>
    <div class="card-body">
        @foreach($tags as $tag)
        <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis me-1 mb-1 text-decoration-none">#{{ $tag->name }}</a>
        @endforeach
    </div>
</div>
@endsection
