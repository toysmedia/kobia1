@extends('layouts.super-admin')
@section('title', 'Conversation Detail')
@section('page-title', 'Conversation Detail')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-chat-square-quote me-2 text-info"></i>Conversation Thread</h4>
    <a href="{{ route('super-admin.ai.conversations') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom"><strong>Metadata</strong></div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Session ID</dt>
                    <dd class="col-7"><code>{{ $conversation->session_id }}</code></dd>
                    <dt class="col-5 text-muted">Portal</dt>
                    <dd class="col-7"><span class="badge bg-info text-dark">{{ $conversation->portal }}</span></dd>
                    <dt class="col-5 text-muted">User Type</dt>
                    <dd class="col-7">{{ $conversation->user_type ?? 'guest' }}</dd>
                    <dt class="col-5 text-muted">Tenant ID</dt>
                    <dd class="col-7">{{ $conversation->tenant_id ?? '—' }}</dd>
                    <dt class="col-5 text-muted">IP Address</dt>
                    <dd class="col-7">{{ $conversation->ip_address ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Tokens Used</dt>
                    <dd class="col-7">{{ number_format($conversation->openai_tokens_used ?? 0) }}</dd>
                    <dt class="col-5 text-muted">Response Time</dt>
                    <dd class="col-7">{{ $conversation->response_time_ms ? $conversation->response_time_ms . 'ms' : '—' }}</dd>
                    <dt class="col-5 text-muted">Flagged</dt>
                    <dd class="col-7">{!! $conversation->flagged_for_review ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>' !!}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom"><strong>Full Thread ({{ $thread->count() }} messages)</strong></div>
            <div class="card-body d-flex flex-column gap-3" style="max-height:500px;overflow-y:auto;">
                @foreach($thread as $msg)
                <div>
                    <div class="d-flex justify-content-end mb-1">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary" style="max-width:85%;font-size:.9rem;">
                            {{ $msg->question }}
                        </div>
                    </div>
                    @if($msg->answer)
                    <div class="d-flex justify-content-start">
                        <div class="p-3 rounded-3 bg-light" style="max-width:85%;font-size:.9rem;">
                            {!! nl2br(e($msg->answer)) !!}
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
