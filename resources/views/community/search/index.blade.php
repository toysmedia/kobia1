@extends('community.layouts.app')
@section('title', 'Search: ' . $query)
@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('community.search') }}" class="d-flex gap-2">
            <input type="text" name="q" class="form-control" value="{{ $query }}" placeholder="Search..." autofocus>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

@if(strlen($query) < 2)
<div class="alert alert-info">Please enter at least 2 characters to search.</div>
@else

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link {{ $tab === 'posts' ? 'active' : '' }}" href="?q={{ urlencode($query) }}&tab=posts">Posts ({{ is_object($posts) ? $posts->total() : 0 }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'replies' ? 'active' : '' }}" href="?q={{ urlencode($query) }}&tab=replies">Replies ({{ is_object($replies) ? $replies->total() : 0 }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'users' ? 'active' : '' }}" href="?q={{ urlencode($query) }}&tab=users">Users ({{ count($users) }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'tags' ? 'active' : '' }}" href="?q={{ urlencode($query) }}&tab=tags">Tags ({{ count($tags) }})</a></li>
</ul>

@if($tab === 'posts')
    @forelse(is_object($posts) ? $posts : [] as $post)
    <div class="card post-card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <span class="badge bg-secondary-subtle text-secondary-emphasis post-type-badge mb-2">{{ $post->type }}</span>
            @if($post->category)<a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none post-type-badge mb-2 ms-1">{{ $post->category->name }}</a>@endif
            <h6 class="fw-semibold mb-1"><a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a></h6>
            <p class="text-muted small mb-2">{{ Str::limit(strip_tags($post->body), 150) }}</p>
            <small class="text-muted">by {{ $post->user->name ?? 'Unknown' }} &middot; {{ $post->created_at->diffForHumans() }}</small>
        </div>
    </div>
    @empty
    <p class="text-muted">No posts found for "{{ $query }}".</p>
    @endforelse
    @if(is_object($posts) && $posts->hasPages()){{ $posts->links() }}@endif

@elseif($tab === 'replies')
    @forelse(is_object($replies) ? $replies : [] as $reply)
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <p class="mb-2">{{ Str::limit($reply->body, 200) }}</p>
            <small class="text-muted">
                by {{ $reply->user->name ?? 'Unknown' }} in
                <a href="{{ route('community.posts.show', $reply->post->slug ?? '#') }}" class="text-decoration-none">{{ $reply->post->title ?? 'Unknown Post' }}</a>
                &middot; {{ $reply->created_at->diffForHumans() }}
            </small>
        </div>
    </div>
    @empty
    <p class="text-muted">No replies found for "{{ $query }}".</p>
    @endforelse

@elseif($tab === 'users')
    @forelse($users as $user)
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:44px;height:44px;flex-shrink:0;">{{ substr($user->name,0,1) }}</div>
            <div>
                <a href="{{ route('community.profile.show', $user->id) }}" class="fw-semibold text-decoration-none">{{ $user->name }}</a>
                @if($user->bio)<p class="text-muted small mb-0">{{ Str::limit($user->bio, 100) }}</p>@endif
            </div>
            <span class="ms-auto badge bg-warning-subtle text-warning-emphasis">{{ $user->reputation }} rep</span>
        </div>
    </div>
    @empty
    <p class="text-muted">No users found for "{{ $query }}".</p>
    @endforelse

@elseif($tab === 'tags')
    @forelse($tags as $tag)
    <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis text-decoration-none me-2 mb-2" style="font-size:.9rem; padding:.4rem .8rem;">
        #{{ $tag->name }} <span class="opacity-75">({{ $tag->usage_count }})</span>
    </a>
    @empty
    <p class="text-muted">No tags found for "{{ $query }}".</p>
    @endforelse
@endif

@endif
@endsection
