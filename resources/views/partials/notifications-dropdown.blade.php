{{-- Notifications Dropdown Partial
     Included in the admin navbar.
--}}
@php
    use App\Models\Tenant\TenantNotification;
    try {
        $navUnread = TenantNotification::unread()->count();
        $navNotifications = TenantNotification::latest()->limit(6)->get();
    } catch (\Throwable) {
        $navUnread = 0;
        $navNotifications = collect();
    }
@endphp

<li class="nav-item dropdown me-2">
    <a class="nav-link position-relative p-2" href="#" role="button"
       data-bs-toggle="dropdown" aria-expanded="false"
       data-tour="notifications-bell"
       title="Notifications">
        <i class="bx bx-bell bx-sm"></i>
        <span id="notifications-badge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:0.6rem; {{ $navUnread > 0 ? '' : 'display:none' }}">
            {{ $navUnread > 99 ? '99+' : $navUnread }}
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow" style="width:340px; max-width:95vw">
        <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
            <span class="fw-semibold">Notifications</span>
            @if($navUnread > 0)
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-muted">Mark all read</button>
                </form>
            @endif
        </li>

        @forelse($navNotifications as $notif)
            <li>
                <a class="dropdown-item py-2 {{ $notif->is_read ? '' : 'fw-semibold' }}"
                   href="{{ route('admin.notifications.index') }}">
                    <div class="d-flex align-items-start gap-2">
                        <span class="mt-1 flex-shrink-0">
                            @switch($notif->type)
                                @case('subscription_alert') <i class="bx bx-time text-warning"></i> @break
                                @case('system_warning')     <i class="bx bx-error text-danger"></i>  @break
                                @case('feature_release')    <i class="bx bx-gift text-success"></i>  @break
                                @default                    <i class="bx bx-bell text-primary"></i>
                            @endswitch
                        </span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="text-truncate small">{{ $notif->title }}</div>
                            <div class="text-muted" style="font-size:0.72rem">
                                {{ $notif->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @if(!$notif->is_read)
                            <span class="flex-shrink-0 mt-1 rounded-circle bg-primary"
                                  style="width:8px;height:8px;display:inline-block"></span>
                        @endif
                    </div>
                </a>
            </li>
        @empty
            <li class="px-3 py-3 text-center text-muted small">No notifications yet.</li>
        @endforelse

        <li class="border-top">
            <a class="dropdown-item text-center small py-2"
               href="#">View All Notifications</a>
        </li>
    </ul>
</li>
