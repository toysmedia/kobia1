@extends('layouts.super-admin')
@section('title', 'Community - Reports')
@section('page-title', 'Community Reports')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-flag me-2 text-warning"></i>Content Reports</h5>
    <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                    <option value="reviewed" {{ request('status')==='reviewed'?'selected':'' }}>Reviewed</option>
                    <option value="dismissed" {{ request('status')==='dismissed'?'selected':'' }}>Dismissed</option>
                    <option value="actioned" {{ request('status')==='actioned'?'selected':'' }}>Actioned</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Reporter</th><th>Type</th><th>Reason</th><th>Details</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($reports as $report)
                <tr>
                    <td><small class="fw-semibold">{{ $report->reporter->name ?? 'Unknown' }}</small></td>
                    <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ class_basename($report->reportable_type ?? '') }}</span></td>
                    <td><span class="badge bg-danger-subtle text-danger-emphasis">{{ $report->reason }}</span></td>
                    <td><small class="text-muted">{{ Str::limit($report->details, 60) }}</small></td>
                    <td>
                        <span class="badge bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'actioned' ? 'danger' : 'secondary') }}-subtle text-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'actioned' ? 'danger' : 'secondary') }}-emphasis">{{ $report->status }}</span>
                    </td>
                    <td><small class="text-muted">{{ $report->created_at->diffForHumans() }}</small></td>
                    <td>
                        @if($report->status === 'pending')
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $report->id }}" style="font-size:.75rem;">Review</button>
                        @else
                        <small class="text-muted">Done</small>
                        @endif
                        {{-- Review Modal --}}
                        <div class="modal fade" id="reviewModal{{ $report->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('super-admin.community.reports.review', $report->id) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Review Report</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="alert alert-secondary py-2 mb-3 small">
                                                <strong>Reason:</strong> {{ $report->reason }}<br>
                                                <strong>Details:</strong> {{ $report->details ?? 'N/A' }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Action</label>
                                                <select name="action" class="form-select form-select-sm" required>
                                                    <option value="dismiss">Dismiss (no action)</option>
                                                    <option value="hide_content">Hide Content</option>
                                                    <option value="ban_user">Ban User</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea name="action_taken" class="form-control form-control-sm" rows="2" placeholder="Optional notes..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-check-circle me-2"></i>No reports found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $reports->links() }}</div>
</div>
@endsection
