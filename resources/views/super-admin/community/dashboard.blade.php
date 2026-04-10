@extends('layouts.super-admin')
@section('title', 'Community - Dashboard')
@section('page-title', 'Community Portal')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Community Dashboard</h5>
    <a href="{{ route('community.index') }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i>View Community</a>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total Users', 'value'=>$stats['total_users'], 'icon'=>'people', 'color'=>'primary'],
        ['label'=>'Total Posts', 'value'=>$stats['total_posts'], 'icon'=>'chat-square-text', 'color'=>'success'],
        ['label'=>'Pending Posts', 'value'=>$stats['pending_posts'], 'icon'=>'hourglass', 'color'=>'warning'],
        ['label'=>'Flagged Posts', 'value'=>$stats['flagged_posts'], 'icon'=>'flag', 'color'=>'danger'],
        ['label'=>'Pending Reports', 'value'=>$stats['pending_reports'], 'icon'=>'exclamation-triangle', 'color'=>'warning'],
        ['label'=>'Banned Users', 'value'=>$stats['banned_users'], 'icon'=>'ban', 'color'=>'danger'],
        ['label'=>'Total Replies', 'value'=>$stats['total_replies'], 'icon'=>'reply', 'color'=>'info'],
    ] as $stat)
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-{{ $stat['color'] }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                    <i class="bi bi-{{ $stat['icon'] }} text-{{ $stat['color'] }}"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">{{ number_format($stat['value']) }}</div>
                    <small class="text-muted">{{ $stat['label'] }}</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent Posts</span>
                <a href="{{ route('super-admin.community.posts') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        @foreach($recentPosts as $post)
                        <tr>
                            <td><a href="{{ route('super-admin.community.posts') }}?search={{ urlencode($post->title) }}" class="text-decoration-none">{{ Str::limit($post->title, 50) }}</a></td>
                            <td><small>{{ $post->user->name ?? 'Unknown' }}</small></td>
                            <td>
                                <span class="badge bg-{{ $post->status === 'approved' ? 'success' : ($post->status === 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $post->status === 'approved' ? 'success' : ($post->status === 'pending' ? 'warning' : 'danger') }}-emphasis">{{ $post->status }}</span>
                            </td>
                            <td><small class="text-muted">{{ $post->created_at->diffForHumans() }}</small></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Pending Reports</span>
                <a href="{{ route('super-admin.community.reports') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($pendingReports as $report)
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-danger-subtle text-danger-emphasis">{{ $report->reason }}</span>
                        <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                    </div>
                    <small class="text-muted">by {{ $report->reporter->name ?? 'Unknown' }}</small>
                </div>
                @empty
                <div class="list-group-item text-muted text-center py-3"><i class="bi bi-check-circle me-2"></i>No pending reports</div>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header fw-semibold">Quick Actions</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('super-admin.community.posts') }}" class="list-group-item list-group-item-action"><i class="bi bi-chat-square-text me-2 text-primary"></i>Moderate Posts</a>
                <a href="{{ route('super-admin.community.users') }}" class="list-group-item list-group-item-action"><i class="bi bi-people me-2 text-success"></i>Manage Users</a>
                <a href="{{ route('super-admin.community.reports') }}" class="list-group-item list-group-item-action"><i class="bi bi-flag me-2 text-warning"></i>Review Reports</a>
                <a href="{{ route('super-admin.community.categories') }}" class="list-group-item list-group-item-action"><i class="bi bi-grid me-2 text-info"></i>Manage Categories</a>
                <a href="{{ route('super-admin.community.announcements') }}" class="list-group-item list-group-item-action"><i class="bi bi-megaphone me-2 text-danger"></i>Post Announcement</a>
            </div>
        </div>
    </div>
</div>
@endsection
