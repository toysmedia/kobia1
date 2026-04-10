@extends('community.layouts.app')
@section('title', $user->name . "'s Profile")
@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-4">
            @if($user->avatar)
            <img src="{{ Storage::url($user->avatar) }}" class="rounded-circle" width="80" height="80" alt="{{ $user->name }}">
            @else
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:80px;height:80px;font-size:2rem;flex-shrink:0;">
                {{ substr($user->name, 0, 1) }}
            </div>
            @endif
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                    @if($user->is_verified)<span class="badge bg-primary-subtle text-primary-emphasis"><i class="bi bi-patch-check me-1"></i>Verified</span>@endif
                    @if($user->is_banned)<span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-ban me-1"></i>Banned</span>@endif
                </div>
                <div class="d-flex flex-wrap gap-3 text-muted small mb-2">
                    @if($user->location)<span><i class="bi bi-geo-alt me-1"></i>{{ $user->location }}</span>@endif
                    @if($user->website)<a href="{{ $user->website }}" target="_blank" class="text-muted"><i class="bi bi-link-45deg me-1"></i>Website</a>@endif
                    <span><i class="bi bi-calendar3 me-1"></i>Joined {{ $user->created_at->format('M Y') }}</span>
                    <span><i class="bi bi-clock me-1"></i>Last seen {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
                @if($user->bio)<p class="text-muted mb-3">{{ $user->bio }}</p>@endif
                <div class="d-flex gap-4">
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-primary">{{ $posts->total() }}</div>
                        <small class="text-muted">Posts</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-success">{{ $replies->total() }}</div>
                        <small class="text-muted">Replies</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-warning">{{ $user->reputation }}</div>
                        <small class="text-muted">Reputation</small>
                    </div>
                </div>
            </div>
            @auth('community')
            @if(auth('community')->id() === $user->id)
            <a href="{{ route('community.profile.edit') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit Profile</a>
            @else
            <button class="btn btn-outline-primary btn-sm follow-btn" data-type="user" data-id="{{ $user->id }}"><i class="bi bi-person-plus me-1"></i>Follow</button>
            @endif
            @endauth
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="profileTabs">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#posts-tab">Posts ({{ $posts->total() }})</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#replies-tab">Replies ({{ $replies->total() }})</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="posts-tab">
        @forelse($posts as $post)
        <div class="card post-card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis post-type-badge">{{ $post->type }}</span>
                    @if($post->category)<a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none post-type-badge">{{ $post->category->name }}</a>@endif
                </div>
                <h6 class="fw-semibold mb-1"><a href="{{ route('community.posts.show', $post->slug) }}" class="text-decoration-none">{{ $post->title }}</a></h6>
                <small class="text-muted">{{ $post->created_at->diffForHumans() }} &middot; {{ $post->views_count }} views &middot; {{ $post->replies_count }} replies</small>
            </div>
        </div>
        @empty<p class="text-muted">No posts yet.</p>
        @endforelse
        {{ $posts->links() }}
    </div>
    <div class="tab-pane fade" id="replies-tab">
        @forelse($replies as $reply)
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <p class="mb-1">{{ Str::limit($reply->body, 150) }}</p>
                <small class="text-muted">
                    In: <a href="{{ route('community.posts.show', $reply->post->slug ?? '#') }}" class="text-decoration-none">{{ $reply->post->title ?? 'Unknown' }}</a>
                    &middot; {{ $reply->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
        @empty<p class="text-muted">No replies yet.</p>
        @endforelse
        {{ $replies->links() }}
    </div>
</div>
@endsection
