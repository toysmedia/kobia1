@extends('community.layouts.app')
@section('title', '#' . $tag->name)
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <span class="badge bg-primary-subtle text-primary-emphasis" style="font-size:1.2rem; padding:.5rem 1rem;">#{{ $tag->name }}</span>
    <span class="text-muted">{{ $tag->usage_count }} {{ Str::plural('post', $tag->usage_count) }}</span>
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
                    @if($post->category)<a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none post-type-badge">{{ $post->category->name }}</a>@endif
                    @foreach($post->tags->take(3) as $t)
                    <a href="{{ route('community.tags.show', $t->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis">#{{ $t->name }}</a>
                    @endforeach
                </div>
                <h6 class="fw-semibold mb-1"><a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a></h6>
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
        <i class="bi bi-tags fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No posts with this tag</h5>
    </div>
</div>
@endforelse
<div class="d-flex justify-content-center mt-4">{{ $posts->links() }}</div>
@endsection
