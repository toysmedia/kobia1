@extends('customer.layouts.app')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="fa-solid fa-ticket text-primary me-2"></i>
                Ticket #{{ $ticket->id }}
            </h4>
            <a href="{{ route('customer.support.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        {{-- Ticket details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-1">{{ $ticket->subject }}</h5>
                    <div class="small text-muted">
                        {{ $ticket->category ?? '' }}
                        @if($ticket->category) &middot; @endif
                        {{ $ticket->created_at->format('d M Y H:i') }}
                    </div>
                </div>
                <div class="d-flex gap-1 flex-wrap justify-content-end">
                    @php
                        $pBadge = match($ticket->priority) { 'urgent'=>'bg-danger','high'=>'bg-warning text-dark','medium'=>'bg-info',default=>'bg-secondary' };
                        $sBadge = match($ticket->status) { 'open'=>'bg-primary','in_progress'=>'bg-warning text-dark','resolved'=>'bg-success','closed'=>'bg-secondary',default=>'bg-light text-dark' };
                    @endphp
                    <span class="badge {{ $pBadge }} text-capitalize">{{ $ticket->priority }}</span>
                    <span class="badge {{ $sBadge }} text-capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="p-3 rounded bg-body-secondary">{{ $ticket->message }}</div>
            </div>
        </div>

        {{-- Replies thread --}}
        @if($ticket->replies->isNotEmpty())
        <div class="mb-4">
            <h6 class="fw-bold text-muted mb-3">Replies ({{ $ticket->replies->count() }})</h6>
            @foreach($ticket->replies as $reply)
            <div class="d-flex mb-3 {{ $reply->user_type === 'customer' ? 'flex-row-reverse' : '' }}">
                <div class="flex-shrink-0 mx-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                         style="width:38px;height:38px;background:{{ $reply->user_type === 'customer' ? '#0d6efd' : '#198754' }}">
                        {{ $reply->user_type === 'customer' ? 'You' : 'ISP' }}
                    </div>
                </div>
                <div class="flex-grow-1" style="max-width:80%">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small mb-1">
                                {{ $reply->user_type === 'customer' ? 'You' : 'Support Team' }}
                                &middot; {{ $reply->created_at->format('d M H:i') }}
                            </div>
                            <div>{{ $reply->message }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Reply form (only if not closed) --}}
        @if(!in_array($ticket->status, ['closed']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="fw-bold mb-0">Add Reply</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('customer.support.reply', $ticket->id) }}">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" rows="4" class="form-control @error('message') is-invalid @enderror"
                                  placeholder="Type your reply here..." required>{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i>Send Reply
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-secondary text-center">
            <i class="fa-solid fa-lock me-1"></i>This ticket is closed.
        </div>
        @endif
    </div>
</div>
@endsection
