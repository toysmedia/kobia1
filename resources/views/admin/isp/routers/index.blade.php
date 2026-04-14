@extends('admin.layouts.app')
@section('title', 'Routers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
.status-badge { min-width: 80px; }
.spinner-status { width: 12px; height: 12px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Routers</h5>
            </div>
            <a href="{{ route('admin.isp.routers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Router
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="routersTable" class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>REF</th>
                                <th>IDENTITY</th>
                                <th>MODEL</th>
                                <th>VERSION</th>
                                <th>WAN IP</th>
                                <th>VPN IP</th>
                                <th>TUNNEL</th>
                                <th class="text-center">RADIUS</th>
                                <th>STATUS</th>
                                <th>WINBOX / MAC</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routers as $router)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                {{-- REF --}}
                                <td><code>{{ $router->ref_code ?? 'RTR-' . str_pad($router->id, 3, '0', STR_PAD_LEFT) }}</code></td>

                                {{-- IDENTITY --}}
                                <td><strong>{{ $router->name }}</strong></td>

                                {{-- MODEL --}}
                                <td>{{ $router->model ?? '-' }}</td>

                                {{-- VERSION --}}
                                <td>{{ $router->routeros_version ?? '-' }}</td>

                                {{-- WAN IP --}}
                                <td>
                                    @if($router->wan_ip)
                                        <span class="text-success">
                                            <i class="bx bx-check-circle me-1"></i>{{ $router->wan_ip }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bx bx-time me-1"></i> Awaiting Script
                                        </span>
                                    @endif
                                </td>

                                {{-- VPN IP --}}
                                <td>
                                    @if($router->vpn_ip)
                                        <span class="text-success">
                                            <i class="bx bx-check-circle me-1"></i>{{ $router->vpn_ip }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bx bx-time me-1"></i> Awaiting Script
                                        </span>
                                    @endif
                                </td>

                                {{-- TUNNEL — OpenVPN connected indicator --}}
                                <td class="text-center">
                                    @if($router->vpn_ip)
                                        <i class="bx bx-lock text-success fs-5" title="OpenVPN tunnel active ({{ $router->vpn_ip }})"></i>
                                    @else
                                        <i class="bx bx-lock-open text-secondary fs-5" title="OpenVPN tunnel not yet connected"></i>
                                    @endif
                                </td>

                                {{-- RADIUS — NAS table presence --}}
                                <td class="text-center">
                                    @php $ip = $router->vpn_ip ?: $router->wan_ip; @endphp
                                    @if($ip && isset($nasIps[$ip]))
                                        <span class="badge bg-success" title="NAS entry exists for {{ $ip }}"><i class="bx bx-check me-1"></i>NAS ✓</span>
                                    @elseif($ip)
                                        <span class="badge bg-warning text-dark" title="No NAS entry for {{ $ip }}"><i class="bx bx-x me-1"></i>No NAS</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- STATUS — real-time online/offline badge + provision phase --}}
                                <td>
                                    @php $phase = (int)($router->provision_phase ?? 0); @endphp
                                    @if($router->vpn_ip || $router->wan_ip)
                                        <span class="badge status-badge bg-secondary status-badge-{{ $router->id }}"
                                              id="status-{{ $router->id }}"
                                              data-router-id="{{ $router->id }}"
                                              data-ping-url="{{ route('admin.isp.routers.ping_status', $router) }}">
                                            <span class="spinner-border spinner-status" role="status"></span>
                                            Checking…
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark status-badge-{{ $router->id }}"
                                              id="status-{{ $router->id }}"
                                              data-router-id="{{ $router->id }}"
                                              data-ping-url="{{ route('admin.isp.routers.ping_status', $router) }}">
                                            Not Provisioned
                                        </span>
                                    @endif
                                    @if($phase === 0)
                                        <br><span class="badge bg-secondary mt-1" title="Phase 0 — not provisioned">P0</span>
                                    @elseif($phase === 1)
                                        <br><span class="badge bg-warning text-dark mt-1" title="Phase 1 — connecting">⏳ P1</span>
                                    @elseif($phase === 2)
                                        <br><span class="badge bg-info text-dark mt-1" title="Phase 2 — services configured">⏳ P2</span>
                                    @else
                                        <br><span class="badge bg-success mt-1" title="Phase 3 — fully secured">✅ P3</span>
                                    @endif
                                </td>

                                {{-- WINBOX / MAC --}}
                                <td>
                                    @php
                                        $connectIp = $router->vpn_ip ?: $router->wan_ip;
                                        $mac       = $router->mac_address ?? null;
                                    @endphp
                                    @if($connectIp)
                                        <div>
                                            <a href="winbox://{{ $connectIp }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1 me-1" title="Open WinBox via IP">
                                                <i class="bx bx-desktop me-1"></i>WinBox
                                            </a>
                                        </div>
                                        @if($mac)
                                        <div class="mt-1">
                                            <a href="winbox://{{ $mac }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1" title="Open WinBox via MAC">
                                                <i class="bx bx-chip me-1"></i><code class="small">{{ $mac }}</code>
                                            </a>
                                        </div>
                                        @else
                                        <div class="mt-1">
                                            <small class="text-muted" id="mac-{{ $router->id }}">
                                                <i class="bx bx-chip me-1"></i>MAC: <em>run Test Connection</em>
                                            </small>
                                        </div>
                                        @endif
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.show', $router) }}">
    <i class="bx bx-show me-2 text-info"></i> View Configuration
</a>
<a class="dropdown-item" href="{{ route('admin.isp.routers.edit', $router) }}">
    <i class="bx bx-edit me-2 text-primary"></i> Edit
</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.script', $router) }}" target="_blank">
                                                    <i class="bx bx-code-alt me-2 text-warning"></i> Generate Script
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.download_script', $router) }}">
                                                    <i class="bx bx-download me-2 text-success"></i> Download Script
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.hotspot_files', $router) }}">
                                                    <i class="bx bx-wifi me-2 text-info"></i> Download Hotspot Files
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-success"
                                                        onclick="testConnection({{ $router->id }}, {{ json_encode($router->name) }}, '{{ route('admin.isp.routers.test_connection', $router) }}')">
                                                    <i class="bx bx-broadcast me-2"></i> Test Connection
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-primary"
                                                        onclick="configureRouter({{ $router->id }}, {{ json_encode($router->name) }}, '{{ route('admin.isp.routers.configure', $router) }}')">
                                                    <i class="bx bx-cog me-2"></i> Configure (Auto)
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.isp.routers.destroy', $router) }}" method="POST"
                                                      onsubmit="return confirm('Delete router {{ addslashes($router->name) }}? This cannot be undone.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    No routers found. <a href="{{ route('admin.isp.routers.create') }}">Add one now.</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Test Connection Modal --}}
<div class="modal fade" id="testConnModal" tabindex="-1" aria-labelledby="testConnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnModalLabel">Test Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="testConnBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Configure Router Modal --}}
<div class="modal fade" id="configureRouterModal" tabindex="-1" aria-labelledby="configureRouterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configureRouterModalLabel">Configure Router</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="configureBanner"></div>

                <div class="progress mb-3" style="height: 8px;">
                    <div id="configureProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                </div>

                <div class="list-group" id="configureSteps">
                    @php
                        $configureSteps = [
                            1 => 'Connecting to MikroTik API…',
                            2 => 'Registering NAS entry in FreeRADIUS…',
                            3 => 'Pushing RADIUS configuration…',
                            4 => 'Creating PPPoE server profile…',
                            5 => 'Finalizing provisioning…',
                        ];
                    @endphp
                    @foreach($configureSteps as $stepNo => $stepText)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary">Step {{ $stepNo }}</span>
                                    <span>{{ $stepText }}</span>
                                </div>
                                <span id="cfg-step-status-{{ $stepNo }}" class="text-muted"><i class="bx bx-minus"></i></span>
                            </div>
                            <small id="cfg-step-detail-{{ $stepNo }}" class="text-muted d-block mt-1"></small>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="configureRetryBtn" class="btn btn-outline-warning me-auto" style="display:none;"></button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#routersTable').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [7, 8, 9, 10, 11] }]
    });

    // ── Real-time status check for every provisioned router ───────────────
    // Runs on page load and refreshes every 30 seconds.
    function checkAllStatuses() {
        $('[data-ping-url]').each(function () {
            var $badge    = $(this);
            var routerId  = $badge.data('router-id');
            var pingUrl   = $badge.data('ping-url');

            $.ajax({
                url:  pingUrl,
                type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    if (res.online) {
                        $badge.removeClass('bg-secondary bg-danger bg-warning')
                              .addClass('bg-success')
                              .html('<i class="bx bx-radio-circle-marked me-1"></i> Online');
                    } else {
                        $badge.removeClass('bg-secondary bg-success bg-warning')
                              .addClass('bg-danger')
                              .html('<i class="bx bx-x-circle me-1"></i> Offline');
                    }
                },
                error: function () {
                    $badge.removeClass('bg-secondary bg-success bg-warning')
                          .addClass('bg-danger')
                          .html('<i class="bx bx-x-circle me-1"></i> Offline');
                }
            });
        });
    }

    checkAllStatuses();
    setInterval(checkAllStatuses, 1000); // refresh every second
});

// ── Full test connection modal ────────────────────────────────────────────
function testConnection(routerId, routerName, url) {
    $('#testConnModalLabel').text('Test Connection — ' + routerName);
    $('#testConnBody').html(
        '<div class="text-center py-3">' +
        '<div class="spinner-border text-primary" role="status"></div>' +
        '<p class="mt-2 text-muted">Connecting to router…</p>' +
        '</div>'
    );
    var modal = new bootstrap.Modal(document.getElementById('testConnModal'));
    modal.show();

    $.ajax({
        url:  url,
        type: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            var e = function (s) {
                return $('<div>').text(s != null ? String(s) : '—').html();
            };

            var statusHtml = res.online
                ? '<span class="badge bg-success fs-6"><i class="bx bx-radio-circle-marked me-1"></i> Online</span>'
                : '<span class="badge bg-danger fs-6"><i class="bx bx-x-circle me-1"></i> Offline</span>';

            var html = '<ul class="list-group">';

            // Online/Offline status
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span><i class="bx bx-broadcast me-2"></i>Connection Status</span>' +
                    statusHtml + '</li>';

            // API reachable
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span><i class="bx bx-wifi me-2"></i>API Reachable</span>' +
                    (res.api_reachable
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-danger">No</span>') +
                    '</li>';

            // RADIUS
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span><i class="bx bx-server me-2"></i>RADIUS Configured</span>' +
                    (res.radius_configured
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-warning text-dark">Not in NAS table</span>') +
                    '</li>';

            // Identity
            if (res.router_identity) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-id-card me-2"></i>Identity</span>' +
                        '<span class="text-muted">' + e(res.router_identity) + '</span></li>';
            }

            // Board
            if (res.board_name) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-chip me-2"></i>Board</span>' +
                        '<span class="text-muted">' + e(res.board_name) + '</span></li>';
            }

            // RouterOS version
            if (res.version) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-code-alt me-2"></i>RouterOS</span>' +
                        '<span class="text-muted">' + e(res.version) + '</span></li>';
            }

            // Uptime
            if (res.uptime) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-time me-2"></i>Uptime</span>' +
                        '<span class="text-muted">' + e(res.uptime) + '</span></li>';
            }

            // CPU
            if (res.cpu_load) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-tachometer me-2"></i>CPU Load</span>' +
                        '<span class="text-muted">' + e(res.cpu_load) + '</span></li>';
            }

            // Memory
            if (res.free_memory) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-memory-card me-2"></i>Memory</span>' +
                        '<span class="text-muted">' + e(res.free_memory) + '</span></li>';
            }

            // MAC address
            if (res.mac_address) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                        '<span><i class="bx bx-network-chart me-2"></i>MAC Address</span>' +
                        '<span><code>' + e(res.mac_address) + '</code>' +
                        ' <a href="winbox://' + e(res.mac_address) + '" class="btn btn-xs btn-sm btn-outline-primary py-0 px-1 ms-2">' +
                        '<i class="bx bx-desktop"></i> WinBox</a></span></li>';

                // Update the MAC display in the table row without page reload
                $('#mac-' + routerId).html(
                    '<i class="bx bx-chip me-1"></i>' +
                    '<a href="winbox://' + e(res.mac_address) + '" class="btn btn-xs btn-sm btn-outline-secondary py-0 px-1">' +
                    '<code class="small">' + e(res.mac_address) + '</code></a>'
                );
            }

            html += '</ul>';

            if (res.error) {
                html += '<div class="alert alert-warning mt-3 mb-0">' +
                        '<i class="bx bx-info-circle me-1"></i>' +
                        $('<div>').text(res.error).html() +
                        '</div>';
            }

            $('#testConnBody').html(html);

            // Update the status badge in the table row
            var $badge = $('#status-' + routerId);
            if (res.online) {
                $badge.removeClass('bg-secondary bg-danger bg-warning')
                      .addClass('bg-success')
                      .html('<i class="bx bx-radio-circle-marked me-1"></i> Online');
            } else {
                $badge.removeClass('bg-secondary bg-success bg-warning')
                      .addClass('bg-danger')
                      .html('<i class="bx bx-x-circle me-1"></i> Offline');
            }
        },
        error: function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                      ? xhr.responseJSON.message : 'Unknown error';
            $('#testConnBody').html(
                '<div class="alert alert-danger mb-0">' +
                '<i class="bx bx-error me-1"></i>Request failed: ' +
                $('<div>').text(msg).html() + '</div>'
            );
        }
    });
}

var configureState = {
    routerId: null,
    routerName: '',
    url: '',
    failedStep: null
};

function configureRouter(routerId, routerName, url) {
    configureState.routerId = routerId;
    configureState.routerName = routerName;
    configureState.url = url;
    configureState.failedStep = null;

    $('#configureRouterModalLabel').text('Configure Router — ' + routerName);
    resetConfigureUi();

    var modal = new bootstrap.Modal(document.getElementById('configureRouterModal'));
    modal.show();

    runConfigureFromStep(1);
}

function resetConfigureUi() {
    $('#configureBanner').html('');
    $('#configureProgressBar').css('width', '0%').removeClass('bg-danger bg-success').addClass('bg-primary');
    $('#configureRetryBtn').hide().off('click');

    for (var step = 1; step <= 5; step++) {
        setConfigureStepStatus(step, 'pending', '');
    }
}

function setConfigureStepStatus(step, state, detail) {
    var $status = $('#cfg-step-status-' + step);
    var $detail = $('#cfg-step-detail-' + step);

    if (state === 'running') {
        $status.removeClass('text-success text-danger text-muted').addClass('text-primary')
               .html('<span class="spinner-border spinner-border-sm" role="status"></span>');
    } else if (state === 'success') {
        $status.removeClass('text-primary text-danger text-muted').addClass('text-success')
               .html('<i class="bx bx-check-circle"></i>');
    } else if (state === 'failed') {
        $status.removeClass('text-primary text-success text-muted').addClass('text-danger')
               .html('<i class="bx bx-x-circle"></i>');
    } else {
        $status.removeClass('text-primary text-success text-danger').addClass('text-muted')
               .html('<i class="bx bx-minus"></i>');
    }

    $detail.text(detail || '');
}

function runConfigureFromStep(startStep) {
    configureState.failedStep = null;
    $('#configureBanner').html('');
    $('#configureRetryBtn').hide().off('click');
    $('#configureProgressBar')
        .css('width', Math.round(((startStep - 1) / 5) * 100) + '%')
        .removeClass('bg-danger bg-success')
        .addClass('bg-primary');

    for (var resetStep = startStep; resetStep <= 5; resetStep++) {
        setConfigureStepStatus(resetStep, 'pending', '');
    }

    function runStep(step) {
        if (step > 5) {
            $('#configureProgressBar')
                .css('width', '100%')
                .removeClass('bg-primary bg-danger')
                .addClass('bg-success');
            $('#configureBanner').html('<div class="alert alert-success mb-3"><i class="bx bx-check-circle me-1"></i>Router configuration completed successfully.</div>');
            refreshRouterStatusBadge(configureState.routerId);
            return;
        }

        setConfigureStepStatus(step, 'running', 'Running...');

        $.ajax({
            url: configureState.url,
            type: 'POST',
            timeout: 30000,
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                step: step
            },
            success: function (res) {
                var ok = !!(res && res.success);
                var message = (res && res.message) ? res.message : (ok ? 'Done' : 'Step failed');

                if (!ok) {
                    handleConfigureFailure(step, message);
                    return;
                }

                setConfigureStepStatus(step, 'success', message);
                var pct = Math.round((step / 5) * 100);
                $('#configureProgressBar').css('width', pct + '%');
                runStep(step + 1);
            },
            error: function (xhr, textStatus) {
                var message = 'Request failed.';
                if (textStatus === 'timeout') {
                    message = 'Step timed out after 30 seconds.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                handleConfigureFailure(step, message);
            }
        });
    }

    runStep(startStep);
}

function handleConfigureFailure(step, message) {
    configureState.failedStep = step;
    setConfigureStepStatus(step, 'failed', message);
    $('#configureProgressBar').removeClass('bg-primary bg-success').addClass('bg-danger');
    $('#configureBanner').html('<div class="alert alert-danger mb-3"><i class="bx bx-error me-1"></i>' + $('<div>').text(message).html() + '</div>');

    $('#configureRetryBtn')
        .text('Retry from Step ' + step)
        .show()
        .off('click')
        .on('click', function () {
            runConfigureFromStep(step);
        });
}

function refreshRouterStatusBadge(routerId) {
    var $badge = $('#status-' + routerId);
    if (!$badge.length) {
        return;
    }

    var pingUrl = $badge.data('ping-url');
    if (!pingUrl) {
        return;
    }

    $badge.removeClass('bg-success bg-danger bg-warning')
          .addClass('bg-secondary')
          .html('<span class="spinner-border spinner-status" role="status"></span> Checking…');

    $.ajax({
        url: pingUrl,
        type: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (res.online) {
                $badge.removeClass('bg-secondary bg-danger bg-warning')
                      .addClass('bg-success')
                      .html('<i class="bx bx-radio-circle-marked me-1"></i> Online');
            } else {
                $badge.removeClass('bg-secondary bg-success bg-warning')
                      .addClass('bg-danger')
                      .html('<i class="bx bx-x-circle me-1"></i> Offline');
            }
        },
        error: function () {
            $badge.removeClass('bg-secondary bg-success bg-warning')
                  .addClass('bg-danger')
                  .html('<i class="bx bx-x-circle me-1"></i> Offline');
        }
    });
}
</script>
@endpush
