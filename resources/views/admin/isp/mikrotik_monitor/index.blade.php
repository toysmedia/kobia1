@extends('admin.layouts.app')
@section('title', 'MikroTik Monitor')
@push('styles')
<style>
.pulse-badge { animation: pulseBadge 1.8s infinite; }
@keyframes pulseBadge { 0%,100%{opacity:1} 50%{opacity:.55} }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-router me-2"></i>MikroTik Monitor</h5>
        <button class="btn btn-sm btn-outline-secondary" id="refreshBtn" onclick="refreshStatuses()">
            <i class="bx bx-refresh me-1"></i>Refresh
        </button>
    </div>

    @forelse($routers as $router)
    <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
        <a href="{{ route('admin.isp.mikrotik_monitor.show', $router['id']) }}" class="text-decoration-none">
            <div class="card router-card h-100 border-2" id="router-card-{{ $router['id'] }}"
                 data-router-id="{{ $router['id'] }}"
                 style="border-left: 4px solid {{ $router['online'] ? '#71dd37' : '#ff3e1d' }};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ $router['name'] }}</h6>
                        <span class="badge router-status-badge {{ $router['online'] ? 'bg-success pulse-badge' : 'bg-danger pulse-badge' }}" id="status-badge-{{ $router['id'] }}">
                            <i class="bx {{ $router['online'] ? 'bx-wifi' : 'bx-wifi-off' }}"></i>
                            {{ $router['online'] ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <p class="small text-muted mb-1"><i class="bx bx-globe bx-xs me-1"></i>{{ $router['wan_ip'] ?? 'N/A' }}</p>
                    @if($router['online'])
                    <div class="row g-1 mt-2" id="router-stats-{{ $router['id'] }}">
                        <div class="col-6">
                            <small class="text-muted d-block">CPU</small>
                            <span class="fw-bold">{{ $router['cpu'] }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Memory</small>
                            <span class="fw-bold">{{ $router['memory'] }}</span>
                        </div>
                        <div class="col-12 mt-1">
                            <small class="text-muted d-block">Uptime</small>
                            <span>{{ $router['uptime'] }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Version</small>
                            <span class="small">{{ $router['version'] }}</span>
                        </div>
                    </div>
                    @else
                    <div id="router-stats-{{ $router['id'] }}">
                        <p class="text-danger small mt-2 mb-0"><i class="bx bx-error-circle me-1"></i>Router unreachable</p>
                    </div>
                    @endif
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="bx bx-router" style="font-size:3rem"></i>
            <p class="mt-2">No active routers found. <a href="{{ route('admin.isp.routers.create') }}">Add a router</a></p>
        </div></div>
    </div>
    @endforelse
</div>
@endsection
@push('scripts')
<script>
var routerStatusUrl = '{{ route("admin.routers.status") }}';

function refreshStatuses() {
    fetch(routerStatusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(routers) {
            routers.forEach(function(router) {
                var card  = document.getElementById('router-card-' + router.id);
                var badge = document.getElementById('status-badge-' + router.id);
                var stats = document.getElementById('router-stats-' + router.id);

                if (!card) {
                    console.warn('Router card not found for id:', router.id);
                    return;
                }

                if (router.online) {
                    card.style.borderLeft  = '4px solid #71dd37';
                    badge.className        = 'badge router-status-badge bg-success pulse-badge';
                    badge.innerHTML        = '<i class="bx bx-wifi"></i> Online';
                } else {
                    card.style.borderLeft  = '4px solid #ff3e1d';
                    badge.className        = 'badge router-status-badge bg-danger pulse-badge';
                    badge.innerHTML        = '<i class="bx bx-wifi-off"></i> Offline';
                    if (stats) {
                        stats.innerHTML = '<p class="text-danger small mt-2 mb-0"><i class="bx bx-error-circle me-1"></i>Router unreachable</p>';
                    }
                }
            });
        })
        .catch(function() { /* silent fail */ });
}

setInterval(refreshStatuses, 10000);
</script>
@endpush
