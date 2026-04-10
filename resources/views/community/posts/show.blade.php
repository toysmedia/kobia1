@extends('community.layouts.app')
@section('title', $post->title)
@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : ($post->type === 'question' ? 'info' : 'secondary') }}-subtle text-{{ $post->type === 'announcement' ? 'danger' : ($post->type === 'question' ? 'info' : 'secondary') }}-emphasis post-type-badge">{{ $post->type }}</span>
            @if($post->category)
            <a href="{{ route('community.categories.show', $post->category->slug) }}" class="badge bg-primary-subtle text-primary-emphasis text-decoration-none">{{ $post->category->name }}</a>
            @endif
            @if($post->is_locked)<span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-lock me-1"></i>Locked</span>@endif
            @if($post->is_pinned)<span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-pin me-1"></i>Pinned</span>@endif
        </div>

        <h3 class="fw-bold mb-3">{{ $post->title }}</h3>

        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
            <a href="{{ route('community.profile.show', $post->user->id ?? 0) }}" class="d-flex align-items-center gap-2 text-decoration-none text-muted">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="fw-semibold">{{ $post->user->name ?? 'Unknown' }}</span>
            </a>
            <span class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $post->created_at->format('M d, Y H:i') }}</span>
            <span class="text-muted small"><i class="bi bi-eye me-1"></i>{{ number_format($post->views_count) }} views</span>
        </div>

        <div class="post-body mb-4" style="line-height:1.7;">
            {!! nl2br(e($post->body)) !!}
        </div>

        @if($post->tags->count())
        <div class="mb-4">
            @foreach($post->tags as $tag)
            <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis me-1 text-decoration-none">#{{ $tag->name }}</a>
            @endforeach
        </div>
        @endif

        <div class="d-flex gap-2 align-items-center">
            @auth('community')
            <button class="btn btn-sm {{ $userLiked ? 'btn-primary' : 'btn-outline-primary' }} like-btn"
                    data-type="post" data-id="{{ $post->id }}">
                <i class="bi bi-heart{{ $userLiked ? '-fill' : '' }} me-1"></i>
                <span class="likes-count">{{ $post->likes_count }}</span> Likes
            </button>
            <button class="btn btn-sm btn-outline-secondary follow-btn" data-type="post" data-id="{{ $post->id }}">
                <i class="bi bi-bell me-1"></i>Follow
            </button>
            <button class="btn btn-sm btn-outline-danger report-btn" data-type="post" data-id="{{ $post->id }}">
                <i class="bi bi-flag me-1"></i>Report
            </button>
            @if(auth('community')->id() === $post->community_user_id)
            <a href="{{ route('community.posts.edit', $post->slug) }}" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('community.posts.destroy', $post->slug) }}" class="d-inline" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
            @endif
            @else
            <a href="{{ route('community.login') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-heart me-1"></i>{{ $post->likes_count }} Likes
            </a>
            @endauth
        </div>
    </div>
</div>

{{-- Replies --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2 text-primary"></i>{{ $post->replies_count }} {{ Str::plural('Reply', $post->replies_count) }}</h6>
</div>

@foreach($replies as $reply)
<div class="card mb-3 border-0 shadow-sm {{ $reply->is_accepted ? 'accepted-answer' : '' }}" id="reply-{{ $reply->id }}">
    <div class="card-body p-3">
        @if($reply->is_accepted)
        <div class="text-success small mb-2 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Accepted Answer</div>
        @endif
        <div class="d-flex gap-3">
            <div class="d-none d-sm-block text-center" style="min-width:40px;">
                <i class="bi bi-person-circle fs-4 text-muted"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <a href="{{ route('community.profile.show', $reply->user->id ?? 0) }}" class="fw-semibold text-decoration-none">{{ $reply->user->name ?? 'Unknown' }}</a>
                        <span class="text-muted small ms-2">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div style="line-height:1.6;">{!! nl2br(e($reply->body)) !!}</div>
                <div class="d-flex gap-2 mt-2">
                    @auth('community')
                    <button class="btn btn-sm btn-outline-primary like-btn" data-type="reply" data-id="{{ $reply->id }}" style="font-size:.75rem;">
                        <i class="bi bi-heart me-1"></i><span class="likes-count">{{ $reply->likes_count }}</span>
                    </button>
                    @if(auth('community')->id() === $post->community_user_id && $post->type === 'question' && !$reply->is_accepted)
                    <form method="POST" action="{{ route('community.replies.accept', $reply->id) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-success" style="font-size:.75rem;"><i class="bi bi-check2 me-1"></i>Accept</button>
                    </form>
                    @endif
                    @if(auth('community')->id() === $reply->community_user_id)
                    <form method="POST" action="{{ route('community.replies.destroy', $reply->id) }}" class="d-inline" onsubmit="return confirm('Delete reply?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" style="font-size:.75rem;"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                    @endauth
                </div>
                {{-- Children replies --}}
                @foreach($reply->children as $child)
                <div class="card mt-2 border-0 bg-body-secondary" id="reply-{{ $child->id }}">
                    <div class="card-body p-2">
                        <span class="fw-semibold small">{{ $child->user->name ?? 'Unknown' }}</span>
                        <span class="text-muted small ms-2">{{ $child->created_at->diffForHumans() }}</span>
                        <div class="mt-1 small">{!! nl2br(e($child->body)) !!}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach

@auth('community')
@if(!$post->is_locked)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header fw-semibold"><i class="bi bi-reply me-2 text-primary"></i>Post a Reply</div>
    <div class="card-body">
        <form method="POST" action="{{ route('community.replies.store', $post->id) }}">
            @csrf
            <div class="mb-3">
                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="5"
                    placeholder="Write your reply..." required>{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-2"></i>Post Reply</button>
        </form>
    </div>
</div>
@else
<div class="alert alert-warning mt-4"><i class="bi bi-lock me-2"></i>This post is locked. No new replies allowed.</div>
@endif
@else
<div class="alert alert-info mt-4">
    <a href="{{ route('community.login') }}" class="fw-semibold">Login</a> or <a href="{{ route('community.register') }}" class="fw-semibold">register</a> to reply.
</div>
@endauth

{{-- Report Modal --}}
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Report Content</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportForm">
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select class="form-select form-select-sm" id="reportReason">
                            <option value="spam">Spam</option>
                            <option value="harassment">Harassment</option>
                            <option value="inappropriate">Inappropriate</option>
                            <option value="misinformation">Misinformation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control form-control-sm" id="reportDetails" placeholder="Additional details..." rows="3"></textarea>
                    </div>
                    <button class="btn btn-danger btn-sm w-100" id="submitReport">Submit Report</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let reportType, reportId;

document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const type = this.dataset.type;
        const id = this.dataset.id;
        fetch('{{ route("community.like") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({type, id})
        }).then(r => r.json()).then(data => {
            this.querySelector('.likes-count').textContent = data.likes_count;
            if (data.liked) {
                this.classList.replace('btn-outline-primary', 'btn-primary');
                this.querySelector('i').classList.replace('bi-heart', 'bi-heart-fill');
            } else {
                this.classList.replace('btn-primary', 'btn-outline-primary');
                this.querySelector('i').classList.replace('bi-heart-fill', 'bi-heart');
            }
        });
    });
});

document.querySelectorAll('.follow-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        fetch('{{ route("community.follow") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({type: this.dataset.type, id: this.dataset.id})
        }).then(r => r.json()).then(data => {
            this.innerHTML = data.following ? '<i class="bi bi-bell-fill me-1"></i>Following' : '<i class="bi bi-bell me-1"></i>Follow';
        });
    });
});

document.querySelectorAll('.report-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        reportType = this.dataset.type;
        reportId = this.dataset.id;
        new bootstrap.Modal(document.getElementById('reportModal')).show();
    });
});

document.getElementById('submitReport')?.addEventListener('click', function() {
    fetch('{{ route("community.report") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({type: reportType, id: reportId, reason: document.getElementById('reportReason').value, details: document.getElementById('reportDetails').value})
    }).then(r => r.json()).then(data => {
        bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
        alert(data.message);
    });
});
</script>
@endpush
