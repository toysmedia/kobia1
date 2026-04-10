@extends('admin.layouts.app')
@section('title', 'Router: ' . $router->name)

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Router: {{ $router->name }}</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.isp.routers.script', $router) }}" class="btn btn-warning" target="_blank">
                    <i class="bx bx-code-alt me-1"></i> Generate Script
                </a>
                <a href="{{ route('admin.isp.routers.hotspot_files', $router) }}" class="btn btn-secondary">
                    <i class="bx bx-download me-1"></i> Hotspot Files
                </a>
                <button type="button" class="btn btn-success" onclick="testConnection()">
                    <i class="bx bx-broadcast me-1"></i> Test Connection
                </button>
                @if($router->vpn_ip || $router->wan_ip)
                <span class="badge bg-secondary align-self-center" id="liveStatusBadgeHeader">
                    <span class="spinner-border" style="width:.7rem;height:.7rem" role="status"></span> Checking…
                </span>
                <small class="text-muted align-self-center" id="lastCheckedHeader" style="display:none"></small>
                @endif
                <a href="{{ route('admin.isp.routers.edit', $router) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Provision Progress --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-loader-circle me-1"></i> Provisioning Status</h6>
                @php $phase = (int)($router->provision_phase ?? 0); @endphp
                @if($phase === 0)
                    <span class="badge bg-secondary">Not Provisioned</span>
                @elseif($phase === 1)
                    <span class="badge bg-warning text-dark">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Phase 1 — Connecting
                    </span>
                @elseif($phase === 2)
                    <span class="badge bg-info text-dark">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Phase 2 — Configuring
                    </span>
                @else
                    <span class="badge bg-success">✅ Fully Provisioned</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    {{-- Phase 0 --}}
                    <div class="col-sm-3">
                        <div class="p-3 rounded border {{ $phase >= 0 ? 'border-secondary bg-light' : 'border-secondary' }} text-center">
                            <div class="fs-4">⚙️</div>
                            <div class="fw-semibold small mt-1">Phase 0</div>
                            <div class="text-muted small">Not Provisioned</div>
                            @if($phase === 0)
                                <span class="badge bg-secondary mt-1">Current</span>
                            @endif
                        </div>
                    </div>
                    {{-- Phase 1 --}}
                    <div class="col-sm-3">
                        <div class="p-3 rounded border {{ $phase >= 1 ? 'border-warning bg-warning bg-opacity-10' : 'border-secondary opacity-50' }} text-center">
                            <div class="fs-4">{{ $phase >= 1 ? '✅' : '⏳' }}</div>
                            <div class="fw-semibold small mt-1">Phase 1</div>
                            <div class="text-muted small">Connection</div>
                            @if($phase === 1)
                                <span class="badge bg-warning text-dark mt-1">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>Connecting
                                </span>
                            @elseif($phase > 1)
                                <span class="badge bg-success mt-1">Connected</span>
                            @endif
                        </div>
                    </div>
                    {{-- Phase 2 --}}
                    <div class="col-sm-3">
                        <div class="p-3 rounded border {{ $phase >= 2 ? 'border-info bg-info bg-opacity-10' : 'border-secondary opacity-50' }} text-center">
                            <div class="fs-4">{{ $phase >= 2 ? '✅' : '⏳' }}</div>
                            <div class="fw-semibold small mt-1">Phase 2</div>
                            <div class="text-muted small">Services</div>
                            @if($phase === 2)
                                <span class="badge bg-info text-dark mt-1">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>Configuring
                                </span>
                            @elseif($phase > 2)
                                <span class="badge bg-success mt-1">Configured</span>
                            @endif
                        </div>
                    </div>
                    {{-- Phase 3 --}}
                    <div class="col-sm-3">
                        <div class="p-3 rounded border {{ $phase >= 3 ? 'border-success bg-success bg-opacity-10' : 'border-secondary opacity-50' }} text-center">
                            <div class="fs-4">{{ $phase >= 3 ? '🔒' : '⏳' }}</div>
                            <div class="fw-semibold small mt-1">Phase 3</div>
                            <div class="text-muted small">Security</div>
                            @if($phase >= 3)
                                <span class="badge bg-success mt-1">Secured</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Heartbeat status --}}
                @php
                    $hbAt = $router->last_heartbeat_at;
                    $isOnline = $hbAt && $hbAt->diffInMinutes(now()) <= 10;
                @endphp
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-heart-circle fs-5 {{ $isOnline ? 'text-success' : 'text-danger' }}"></i>
                    <span class="small text-muted">Last heartbeat:</span>
                    @if($hbAt)
                        <span class="small {{ $isOnline ? 'text-success' : 'text-danger' }}">
                            {{ $hbAt->diffForHumans() }}
                        </span>
                        @if($isOnline)
                            <span class="badge bg-success">Online</span>
                        @else
                            <span class="badge bg-danger">Stale</span>
                        @endif
                    @else
                        <span class="small text-muted">Never</span>
                        <span class="badge bg-secondary">No heartbeat</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Router Details --}}
    <div class="col-sm-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Basic Information</h6>
                @if($router->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" width="40%">Name</th>
                        <td>{{ $router->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">WAN IP</th>
                        <td><code>{{ $router->wan_ip }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">RADIUS Secret</th>
                        <td>
                            <span id="secretMask">••••••••</span>
                            <button class="btn btn-sm btn-link p-0 ms-2" onclick="toggleSecret()">Show</button>
                            <span id="secretValue" class="d-none"><code>{{ $router->radius_secret }}</code></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Billing Domain</th>
                        <td>{{ $router->billing_domain ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created</th>
                        <td>{{ $router->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Updated</th>
                        <td>{{ $router->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Network Config --}}
    <div class="col-sm-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Network Configuration</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" width="40%">WAN Interface</th>
                        <td><code>{{ $router->wan_interface }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Customer Interface</th>
                        <td><code>{{ $router->customer_interface }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">PPPoE Pool</th>
                        <td><code>{{ $router->pppoe_pool_range }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Hotspot Pool</th>
                        <td><code>{{ $router->hotspot_pool_range }}</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @if($router->notes)
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Notes</h6></div>
            <div class="card-body">{{ $router->notes }}</div>
        </div>
    </div>
    @endif

    {{-- Danger Zone --}}
    <div class="col-sm-12">
        <div class="card border-danger">
            <div class="card-header text-danger"><h6 class="mb-0"><i class="bx bx-error-circle me-1"></i> Danger Zone</h6></div>
            <div class="card-body">
                <p class="mb-3 text-muted">Permanently delete this router. All associated subscribers and sessions must be removed first.</p>
                <form action="{{ route('admin.isp.routers.destroy', $router) }}" method="POST"
                      onsubmit="return confirm('Permanently delete router &quot;{{ addslashes($router->name) }}&quot;? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Delete Router
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Test Connection Modal --}}
<div class="modal fade" id="testConnModal" tabindex="-1" aria-labelledby="testConnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnModalLabel">Test Connection — {{ $router->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="testConnBody">
            </div>
            <div class="modal-footer">
                <div class="me-auto small text-muted" id="lastCheckedWrap" style="display:none">
                    Last checked: <span id="lastCheckedTs">—</span>
                </div>
                @if($router->vpn_ip || $router->wan_ip)
                <span class="badge bg-secondary me-2" id="liveStatusBadge">
                    <span class="spinner-border" style="width:.7rem;height:.7rem" role="status"></span> Checking…
                </span>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSecret() {
    const mask  = document.getElementById('secretMask');
    const value = document.getElementById('secretValue');
    const btn   = event.target;
    if (value.classList.contains('d-none')) {
        mask.classList.add('d-none');
        value.classList.remove('d-none');
        btn.textContent = 'Hide';
    } else {
        mask.classList.remove('d-none');
        value.classList.add('d-none');
        btn.textContent = 'Show';
    }
}

var _testConnModal = null;
var _lastCheckedInterval = null;
var _lastCheckedAt = null;

function testConnection() {
    $('#testConnBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Testing...</span></div><p class="mt-2 text-muted">Connecting to router…</p></div>');
    if (!_testConnModal) {
        _testConnModal = new bootstrap.Modal(document.getElementById('testConnModal'));
    }
    _testConnModal.show();

    $.ajax({
        url: '{{ route('admin.isp.routers.test_connection', $router) }}',
        type: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            var e = function(s) {
                return $('<div>').text(s != null ? String(s) : '').html();
            };
            var html = '<ul class="list-group">';
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                  + '<span><i class="bx bx-wifi me-2"></i>API Reachable</span>'
                  + (res.api_reachable
                      ? '<span class="badge bg-success">Yes</span>'
                      : '<span class="badge bg-danger">No</span>')
                  + '</li>';
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                  + '<span><i class="bx bx-server me-2"></i>RADIUS Configured</span>'
                  + (res.radius_configured
                      ? '<span class="badge bg-success">Yes</span>'
                      : '<span class="badge bg-warning text-dark">Not in NAS table</span>')
                  + '</li>';
            if (res.router_identity) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-chip me-2"></i>Board</span>'
                      + '<span class="text-muted">' + e(res.router_identity) + '</span></li>';
            }
            if (res.version) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-code-alt me-2"></i>RouterOS</span>'
                      + '<span class="text-muted">' + e(res.version) + '</span></li>';
            }
            if (res.uptime) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-time me-2"></i>Uptime</span>'
                      + '<span class="text-muted">' + e(res.uptime) + '</span></li>';
            }
            html += '</ul>';
            if (res.error) {
                html += '<div class="alert alert-warning mt-3 mb-0"><i class="bx bx-info-circle me-1"></i>' + e(res.error) + '</div>';
            }
            $('#testConnBody').html(html);
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unknown error';
            $('#testConnBody').html('<div class="alert alert-danger mb-0"><i class="bx bx-error me-1"></i>Request failed: ' + $('<div>').text(msg).html() + '</div>');
        }
    });
}

function updateLastChecked() {
    if (_lastCheckedAt) {
        var diff = Math.floor((Date.now() - _lastCheckedAt) / 1000);
        var label = diff === 0 ? 'just now' : diff + 's ago';
        $('#lastCheckedTs').text(label);
        $('#lastCheckedWrap').show();
        $('#lastCheckedHeader').show().text('Last checked: ' + label);
    }
}

@if($router->vpn_ip || $router->wan_ip)
var _liveCheckUrl = '{{ route('admin.isp.routers.ping_status', $router) }}';
var _liveCheckToken = $('meta[name="csrf-token"]').attr('content');

function liveStatusCheck() {
    $.ajax({
        url: _liveCheckUrl,
        type: 'POST',
        data: { _token: _liveCheckToken },
        success: function(res) {
            _lastCheckedAt = Date.now();
            var cls = res.online ? 'bg-success' : 'bg-danger';
            var txt = res.online
                ? '<i class="bx bx-radio-circle-marked me-1"></i> Online'
                : '<i class="bx bx-x-circle me-1"></i> Offline';
            $('#liveStatusBadge, #liveStatusBadgeHeader')
                .removeClass('bg-secondary bg-danger bg-warning bg-success')
                .addClass(cls)
                .html(txt);
            $('#lastCheckedHeader').show().text('Last checked: just now');
        }
    });
}

$(function () {
    setTimeout(function () {
        testConnection();
        liveStatusCheck();
    }, 1000);

    setInterval(liveStatusCheck, 1000);

    _lastCheckedInterval = setInterval(updateLastChecked, 1000);
});
@endif
</script>
@endpush