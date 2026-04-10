@extends('layouts.super-admin')
@section('title', 'Unanswered Questions')
@section('page-title', 'Unanswered Questions Queue')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-flag me-2 text-warning"></i>Unanswered Questions</h4>
    <a href="{{ route('super-admin.ai.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Question</th>
                    <th>Portal</th>
                    <th>Tenant</th>
                    <th>Asked</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td style="max-width:280px;">{{ Str::limit($item->question, 80) }}</td>
                    <td><span class="badge bg-info text-dark">{{ $item->portal }}</span></td>
                    <td>{{ $item->tenant_id ?? '—' }}</td>
                    <td>{{ $item->created_at->diffForHumans() }}</td>
                    <td>
                        @if($item->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($item->status === 'answered')
                            <span class="badge bg-success">Answered</span>
                        @else
                            <span class="badge bg-secondary">Dismissed</span>
                        @endif
                    </td>
                    <td>
                        @if($item->status === 'pending')
                        <button class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $item->id }}"><i class="bi bi-check-lg"></i> Resolve</button>
                        <form method="POST" action="{{ route('super-admin.ai.unanswered.dismiss', $item->id) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-x-lg"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>

                {{-- Resolve Modal --}}
                <div class="modal fade" id="resolveModal{{ $item->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('super-admin.ai.unanswered.resolve', $item->id) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Resolve Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="fw-semibold text-muted small mb-2">Original question:</p>
                                    <p class="border rounded p-2 bg-light">{{ $item->question }}</p>
                                    <label class="form-label fw-semibold">Your Answer <span class="text-danger">*</span></label>
                                    <textarea name="resolved_answer" class="form-control mb-3" rows="4" required placeholder="Provide a clear answer…"></textarea>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="add_to_kb" id="addKb{{ $item->id }}" value="1">
                                        <label class="form-check-label" for="addKb{{ $item->id }}">Also add to Knowledge Base</label>
                                    </div>
                                    <input type="text" name="kb_category" class="form-control form-control-sm mt-2" placeholder="KB Category (e.g. billing)" value="general">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success btn-sm">Save Answer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No unanswered questions. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-transparent">{{ $items->links() }}</div>
    @endif
</div>
@endsection
