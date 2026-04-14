@extends('admin.layouts.app')
@section('title', 'Server Configuration')

@php
    $services = [
        'openvpn' => [
            'name' => 'OpenVPN',
            'config' => '/etc/openvpn/server.conf',
            'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>',
        ],
        'freeradius' => [
            'name' => 'FreeRADIUS',
            'config' => '/usr/local/etc/raddb/radiusd.conf',
            'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>',
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'config' => '/etc/supervisor/supervisord.conf',
            'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 8h8M8 12h6M8 16h8"/><path d="M4 4h3v3H4zM17 4h3v3h-3zM4 17h3v3H4zM17 17h3v3h-3z"/></svg>',
        ],
    ];
@endphp

@push('styles')
<style>
    .service-card {
        border-left: 4px solid #c7cdd4;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .service-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .25rem .75rem rgba(67, 89, 113, .12);
    }
    .service-card.is-running { border-left-color: #71dd37; }
    .service-card.is-stopped { border-left-color: #ff3e1d; }
    .service-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(105, 108, 255, .15);
        color: #696cff;
    }
    .service-card.is-running .service-icon { background: rgba(113, 221, 55, .15); color: #71dd37; }
    .service-card.is-stopped .service-icon { background: rgba(255, 62, 29, .15); color: #ff3e1d; }
    .skeleton {
        position: relative;
        overflow: hidden;
        background: #eef1f5;
        border-radius: .25rem;
        min-height: 1rem;
    }
    .skeleton::after {
        content: '';
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.7), transparent);
        animation: shimmer 1.3s infinite;
    }
    .skeleton-badge { width: 96px; height: 24px; }
    .skeleton-line-sm { width: 45%; height: 14px; }
    .skeleton-line-md { width: 70%; height: 14px; }
    .skeleton-line-lg { width: 85%; height: 14px; }
    @keyframes shimmer { 100% { transform: translateX(100%); } }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-sm-8">
        <h5 class="mb-0">Server Configuration</h5>
        <small class="text-muted">Monitor and restart core infrastructure services.</small>
    </div>
    <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
        <button type="button" id="refreshStatusBtn" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-refresh me-1"></i>Refresh Status
        </button>
    </div>
</div>

<div class="row" id="servicesRow">
    @foreach($services as $key => $service)
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 service-card" data-service-card="{{ $key }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="service-icon">{!! $service['icon'] !!}</div>
                    <span class="badge skeleton skeleton-badge" data-role="status-badge"></span>
                </div>
                <h5 class="mb-1">{{ $service['name'] }}</h5>
                <p class="mb-3 text-muted small" data-role="version"><span class="skeleton skeleton-line-md d-inline-block"></span></p>

                <p class="mb-2 small"><i class="bx bx-chip me-1"></i>PID: <span data-role="pid"><span class="skeleton skeleton-line-sm d-inline-block"></span></span></p>
                <p class="mb-2 small"><i class="bx bx-time me-1"></i>Uptime: <span data-role="uptime"><span class="skeleton skeleton-line-sm d-inline-block"></span></span></p>
                <p class="mb-3 small text-break"><i class="bx bx-file me-1"></i><code data-role="config"><span class="skeleton skeleton-line-lg d-inline-block"></span></code></p>

                <button type="button" class="btn btn-outline-warning btn-sm w-100 restart-service-btn" data-service="{{ $key }}">
                    <i class="bx bx-revision me-1"></i>Restart
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="alert alert-info mb-0">
    <i class="bx bx-info-circle me-1"></i>
    Service status auto-refreshes every 30 seconds. Restart requests may take a few seconds before updated status appears.
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const serviceMeta = @json($services);
    const statusUrl = "{{ route('admin.isp.configuration.services_status') }}";
    const restartUrl = "{{ route('admin.isp.configuration.restart_service') }}";
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function setCardLoading(serviceKey) {
        const card = $(`[data-service-card="${serviceKey}"]`);
        card.removeClass('is-running is-stopped');
        card.find('[data-role="status-badge"]').attr('class', 'badge skeleton skeleton-badge').html('');
        card.find('[data-role="version"]').html('<span class="skeleton skeleton-line-md d-inline-block"></span>');
        card.find('[data-role="pid"]').html('<span class="skeleton skeleton-line-sm d-inline-block"></span>');
        card.find('[data-role="uptime"]').html('<span class="skeleton skeleton-line-sm d-inline-block"></span>');
        card.find('[data-role="config"]').html('<span class="skeleton skeleton-line-lg d-inline-block"></span>');
    }

    function renderService(service) {
        const card = $(`[data-service-card="${service.key}"]`);
        const isRunning = !!service.running;

        card.removeClass('is-running is-stopped').addClass(isRunning ? 'is-running' : 'is-stopped');
        card.find('[data-role="version"]').text(service.version || 'Unknown');
        card.find('[data-role="pid"]').text(service.pid || 'N/A');
        card.find('[data-role="uptime"]').text(service.uptime || 'N/A');
        card.find('[data-role="config"]').text(service.config_path || serviceMeta[service.key].config);
        card.find('[data-role="status-badge"]').attr('class', 'badge ' + (isRunning ? 'bg-success' : 'bg-danger'))
            .html(`<i class="bx ${isRunning ? 'bx-check' : 'bx-x'} me-1"></i>${isRunning ? 'Running' : 'Stopped'}`);
    }

    function fetchStatuses() {
        $.get(statusUrl)
            .done(function (response) {
                if (!response.success || !response.data || !response.data.services) {
                    notify('Unable to load service statuses.', 'error');
                    return;
                }

                response.data.services.forEach(renderService);
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Failed to fetch service statuses.';
                notify(message, 'error');
            });
    }

    function restartService(service, button) {
        if (!confirm(`Restart ${serviceMeta[service].name}?`)) {
            return;
        }

        const originalHtml = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Restarting...');

        $.ajax({
            url: restartUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { service }
        }).done(function (response) {
            notify(response.message || 'Service restart requested successfully.', 'success');
            setTimeout(fetchStatuses, 3000);
        }).fail(function (xhr) {
            const message = xhr.responseJSON?.message || 'Failed to restart service.';
            notify(message, 'error');
        }).always(function () {
            button.prop('disabled', false).html(originalHtml);
        });
    }

    Object.keys(serviceMeta).forEach(setCardLoading);
    fetchStatuses();
    setInterval(fetchStatuses, 30000);

    $('#refreshStatusBtn').on('click', function () {
        fetchStatuses();
    });

    $('.restart-service-btn').on('click', function () {
        restartService($(this).data('service'), $(this));
    });
});
</script>
@endpush
