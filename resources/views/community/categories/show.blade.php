@extends('community.layouts.app')
@section('title', $category->name)
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    @if($category->color)
    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:50px;height:50px;background:{{ $category->color }};">
        @if($category->icon)<i class="bi bi-{{ $category->icon }}"></i>@else{{ substr($category->name,0,1) }}@endif
    </div>
    @endif
    <div>
        <h4 class="fw-bold mb-1">{{ $category->name }}</h4>
        @if($category->description)<p class="text-muted mb-0">{{ $category->description }}</p>@endif
    </div>
    @auth('community')
    <a href="{{ route('community.posts.create') }}" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg me-1"></i>New Post</a>
    @endauth
</div>

@forelse($posts as $post)
<div class="card post-card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-3">
            <div class="d-none d-sm-flex flex-column align-items-center" style="min-width:44px;">
                <span class="fw-bold text-primary">{{ $post->likes_count }}</span>
                <small class="text-muted" style="font-size:.65rem;">likes</small>
                <span class="fw-bold mt-1">{{ $post->replies_count }}</span>
                <small class="text-muted" style="font-size:.65rem;">replies</small>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis post-type-badge">{{ $post->type }}</span>
                    @foreach($post->tags->take(3) as $tag)
                    <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis">#{{ $tag->name }}</a>
                    @endforeach
                </div>
                <h6 class="fw-semibold mb-1">
                    <a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a>
                </h6>
                <p class="text-muted small mb-1">{{ Str::limit(strip_tags($post->body), 100) }}</p>
                <small class="text-muted">
                    <a href="{{ route('community.profile.show', $post->user->id ?? 0) }}" class="text-decoration-none text-muted">{{ $post->user->name ?? 'Unknown' }}</a>
                    &middot; {{ $post->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-square fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No posts in this category yet</h5>
    </div>
</div>
@endforelse

<div class="d-flex justify-content-center mt-4">{{ $posts->links() }}</div>
@endsection
