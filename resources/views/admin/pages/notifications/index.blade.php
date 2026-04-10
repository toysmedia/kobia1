@extends('admin.layouts.app')

@section('title', 'Notifications')

@push('styles')
<style>
.notification-item {
    transition: background-color 0.2s;
}
.notification-item:hover {
    background-color: rgba(0,0,0,0.02);
}
.notification-item.unread {
    border-left: 3px solid #4f7ef8;
    background-color: rgba(79,126,248,0.04);
}
.notification-item.unread .fw-semibold { color: inherit; }
.notif-icon-broadcast       { color: #4f7ef8; }
.notif-icon-system_warning  { color: #dc3545; }
.notif-icon-feature_release { color: #198754; }
.notif-icon-subscription_alert { color: #ffc107; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">🔔 Notification Center</h4>
        <p class="text-muted mb-0">
            @if($unreadCount > 0)
                <span class="badge bg-danger">{{ $unreadCount }}</span> unread notification{{ $unreadCount > 1 ? 's' : '' }}
            @else
                All caught up!
            @endif
        </p>
    </div>
    @if($unreadCount > 0)
    <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-check-double me-1"></i>Mark All Read
        </button>
    </form>
    @endif
</div>

{{-- Filter tabs --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <ul class="nav nav-pills nav-sm gap-1 flex-wrap">
            @php
                $filters = [
                    'all'                => 'All',
                    'unread'             => 'Unread',
                    'broadcast'          => 'Broadcasts',
                    'system_warning'     => 'System Warnings',
                    'feature_release'    => 'Feature Releases',
                    'subscription_alert' => 'Subscription Alerts',
                ];
            @endphp
            @foreach($filters as $key => $label)
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $filter === $key ? 'active' : '' }}"
                       href="{{ route('admin.notifications.index', ['filter' => $key]) }}">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Notifications list --}}
<div class="card">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notif)
            <div class="list-group-item notification-item {{ $notif->is_read ? '' : 'unread' }} py-3">
                <div class="d-flex align-items-start gap-3">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 fs-4 mt-1">
                        @switch($notif->type)
                            @case('broadcast')
                                <i class="bx bx-broadcast notif-icon-broadcast"></i> @break
                            @case('system_warning')
                                <i class="bx bx-error-circle notif-icon-system_warning"></i> @break
                            @case('feature_release')
                                <i class="bx bx-gift notif-icon-feature_release"></i> @break
                            @case('subscription_alert')
                                <i class="bx bx-time-five notif-icon-subscription_alert"></i> @break
                            @default
                                <i class="bx bx-bell text-muted"></i>
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 {{ $notif->is_read ? 'fw-normal' : 'fw-semibold' }}">
                                {{ $notif->title }}
                            </h6>
                            <small class="text-muted flex-shrink-0 ms-2">
                                {{ $notif->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <p class="mb-0 text-muted small">{{ $notif->message }}</p>
                        <div class="mt-1 d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border" style="font-size:0.7rem">
                                {{ ucwords(str_replace('_', ' ', $notif->type)) }}
                            </span>
                            @if(!$notif->is_read)
                                <form action="{{ route('admin.notifications.mark-read', $notif) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0"
                                            style="font-size:0.75rem">Mark as read</button>
                                </form>
                            @else
                                <small class="text-success">
                                    <i class="bx bx-check me-1"></i>Read {{ $notif->read_at?->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item py-5 text-center text-muted">
                <i class="bx bx-bell-off bx-lg mb-2"></i>
                <p class="mb-0">
                    @if($filter !== 'all')
                        No {{ str_replace('_', ' ', $filter) }} notifications.
                        <a href="{{ route('admin.notifications.index') }}">View all</a>
                    @else
                        No notifications yet.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $notifications->links() }}
</div>
@endsection