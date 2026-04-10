@extends('layouts.super-admin')
@section('title', 'AI Conversations')
@section('page-title', 'AI Conversation Logs')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-chat-square-text me-2 text-info"></i>Conversation Logs</h4>
    <a href="{{ route('super-admin.ai.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="portal" class="form-select form-select-sm">
                    <option value="">All Portals</option>
                    @foreach($portals as $p)
                        <option value="{{ $p }}" @selected(request('portal') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <input type="number" name="tenant_id" class="form-control form-control-sm" placeholder="Tenant ID" value="{{ request('tenant_id') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('super-admin.ai.conversations') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Session</th>
                    <th>Portal</th>
                    <th>User Type</th>
                    <th>Question</th>
                    <th>Answered?</th>
                    <th>Helpful?</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversations as $c)
                <tr>
                    <td><code style="font-size:.75rem;">{{ Str::limit($c->session_id, 12) }}</code></td>
                    <td><span class="badge bg-info text-dark">{{ $c->portal }}</span></td>
                    <td>{{ $c->user_type ?? 'guest' }}</td>
                    <td>{{ Str::limit($c->question, 60) }}</td>
                    <td>{!! $c->was_answered ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td>
                    <td>
                        @if(is_null($c->was_helpful)) <span class="text-muted">—</span>
                        @elseif($c->was_helpful) <span class="text-success"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                        @else <span class="text-danger"><i class="bi bi-hand-thumbs-down-fill"></i></span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $c->created_at->format('d M, H:i') }}</td>
                    <td><a href="{{ route('super-admin.ai.conversations.show', $c->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No conversations logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conversations->hasPages())
    <div class="card-footer bg-transparent">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection
