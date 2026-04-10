@extends('admin.layouts.app')
@section('title', 'Monitor: ' . $router->name)
@push('styles')
<style>
.temp-green { color: #28a745; }
.temp-yellow { color: #ffc107; }
.temp-red { color: #dc3545; }
.metric-card { border-left: 4px solid #696cff; }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-chip me-2"></i>{{ $router->name }} — Live Monitor</h5>
        <a href="{{ route('admin.isp.mikrotik_monitor.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>Back
        </a>
    </div>

    {{-- Offline alert banner (shown when router is down) --}}
    <div class="col-12 mb-3 d-none" id="offlineBanner">
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
            <i class="bx bx-wifi-off bx-sm"></i>
            <div><strong>Router Offline</strong> — {{ $router->name }} ({{ $router->wan_ip }}) is currently unreachable. Check your network connection.</div>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="col-12 mb-3">
        <div class="alert alert-secondary d-flex align-items-center gap-3" id="statusBar">
            <span class="spinner-border spinner-border-sm"></span>
            Connecting to {{ $router->wan_ip }}...
        </div>
    </div>

    {{-- System Info --}}
    <div class="col-md-6 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-chip me-1"></i> System Info</h6></div>
            <div class="card-body" id="sysInfo"><div class="text-muted">Loading...</div></div>
        </div>
    </div>

    {{-- Health --}}
    <div class="col-md-3 mb-4">
        <div class="card metric-card h-100" style="border-left-color:#71dd37">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-heart-circle me-1"></i> Health</h6></div>
            <div class="card-body" id="healthInfo"><div class="text-muted">Loading...</div></div>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="col-md-3 mb-4">
        <div class="card metric-card h-100" style="border-left-color:#ffab00">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-group me-1"></i> Active Users</h6></div>
            <div class="card-body" id="usersInfo"><div class="text-muted">Loading...</div></div>
        </div>
    </div>

    {{-- Interface Traffic --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-trending-up me-1"></i> Interface Traffic <small class="text-muted">(auto-refresh every 10s)</small></h6></div>
            <div class="card-body">
                <div id="interfaceList" class="row g-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var dataUrl = '{{ route("admin.isp.mikrotik_monitor.data", $router) }}';
var ifCharts = {};
var ifHistory = {};

function formatBytes(bytes) {
    if (bytes >= 1073741824) return (bytes/1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes/1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes/1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

function formatUptime(str) { return str || 'N/A'; }

function tempClass(val) {
    if (!val) return '';
    var n = parseFloat(val);
    if (n > 75) return 'temp-red';
    if (n > 60) return 'temp-yellow';
    return 'temp-green';
}

function fetchData() {
    fetch(dataUrl)
        .then(r => r.json())
        .then(data => {
            if (!data.online) {
                document.getElementById('offlineBanner').classList.remove('d-none');
                document.getElementById('statusBar').className = 'alert alert-danger d-flex align-items-center gap-3';
                document.getElementById('statusBar').innerHTML = '<i class="bx bx-wifi-off bx-sm"></i> Router is offline or unreachable';
                return;
            }

            document.getElementById('offlineBanner').classList.add('d-none');
            document.getElementById('statusBar').className = 'alert alert-success d-flex align-items-center gap-3';
            document.getElementById('statusBar').innerHTML = '<i class="bx bx-wifi"></i> Online — Last updated: ' + new Date().toLocaleTimeString();

            // System info
            var r = data.resource || {};
            var b = data.board || {};
            var memUsed = r['total-memory'] && r['free-memory'] ? r['total-memory'] - r['free-memory'] : 0;
            var memPct  = r['total-memory'] ? Math.round(memUsed / r['total-memory'] * 100) : 0;
            var hddUsed = r['total-hdd-space'] && r['free-hdd-space'] ? r['total-hdd-space'] - r['free-hdd-space'] : 0;
            document.getElementById('sysInfo').innerHTML = `
                <table class="table table-sm table-borderless mb-0">
                    <tr><th>Board</th><td>${b['board-name'] || r['board-name'] || 'N/A'}</td></tr>
                    <tr><th>Serial</th><td>${b['serial-number'] || 'N/A'}</td></tr>
                    <tr><th>Version</th><td>${r['version'] || 'N/A'}</td></tr>
                    <tr><th>Uptime</th><td>${r['uptime'] || 'N/A'}</td></tr>
                    <tr><th>CPU Load</th><td>${r['cpu-load'] || 0}%</td></tr>
                    <tr><th>Memory</th><td>${formatBytes(memUsed)} / ${formatBytes(r['total-memory'] || 0)} (${memPct}%)</td></tr>
                    <tr><th>Storage</th><td>${formatBytes(hddUsed)} / ${formatBytes(r['total-hdd-space'] || 0)}</td></tr>
                </table>`;

            // Health
            var h = data.health || [];
            var healthHtml = '<table class="table table-sm table-borderless mb-0">';
            if (Array.isArray(h)) {
                h.forEach(item => {
                    var cls = item.name && item.name.includes('temperature') ? tempClass(item.value) : '';
                    healthHtml += `<tr><th>${item.name}</th><td class="${cls}">${item.value} ${item.type || ''}</td></tr>`;
                });
            }
            healthHtml += '</table>';
            document.getElementById('healthInfo').innerHTML = h.length ? healthHtml : '<p class="text-muted small">Health data not available</p>';

            // Users
            var u = data.users || {};
            document.getElementById('usersInfo').innerHTML = `
                <div class="text-center py-2">
                    <div class="display-6 fw-bold text-primary">${(u.pppoe||0) + (u.hotspot||0)}</div>
                    <div class="small text-muted">Total Active</div>
                </div>
                <hr>
                <div class="d-flex justify-content-around">
                    <div class="text-center"><div class="fw-bold">${u.pppoe||0}</div><div class="small text-muted">PPPoE</div></div>
                    <div class="text-center"><div class="fw-bold">${u.hotspot||0}</div><div class="small text-muted">Hotspot</div></div>
                </div>`;

            // Interfaces
            var interfaces = data.interfaces || [];
            var container = document.getElementById('interfaceList');
            interfaces.filter(i => i['running'] === 'true' || i['running'] === true).slice(0, 8).forEach(iface => {
                var name = iface['name'];
                var txBytes = parseInt(iface['tx-byte'] || 0);
                var rxBytes = parseInt(iface['rx-byte'] || 0);

                if (!ifHistory[name]) ifHistory[name] = { tx: [], rx: [], labels: [] };
                ifHistory[name].labels.push(new Date().toLocaleTimeString());
                ifHistory[name].tx.push(txBytes);
                ifHistory[name].rx.push(rxBytes);
                if (ifHistory[name].labels.length > 30) {
                    ifHistory[name].labels.shift();
                    ifHistory[name].tx.shift();
                    ifHistory[name].rx.shift();
                }

                if (!document.getElementById('iface_' + name.replace(/[^a-z0-9]/gi, '_'))) {
                    var div = document.createElement('div');
                    div.className = 'col-md-4 col-sm-6';
                    div.innerHTML = `<div class="card border"><div class="card-header py-2"><small class="fw-bold">${name}</small></div><div class="card-body py-2"><canvas id="iface_${name.replace(/[^a-z0-9]/gi,'_')}" height="80"></canvas></div></div>`;
                    container.appendChild(div);
                }

                var canvasId = 'iface_' + name.replace(/[^a-z0-9]/gi, '_');
                if (!ifCharts[name]) {
                    ifCharts[name] = new Chart(document.getElementById(canvasId), {
                        type: 'line',
                        data: {
                            labels: ifHistory[name].labels,
                            datasets: [
                                { label: 'TX', data: ifHistory[name].tx, borderColor: '#007bff', fill: false, tension: .4, pointRadius: 2 },
                                { label: 'RX', data: ifHistory[name].rx, borderColor: '#28a745', fill: false, tension: .4, pointRadius: 2 },
                            ]
                        },
                        options: { plugins: { legend: { display: true, position: 'bottom' } }, scales: { y: { beginAtZero: true } }, animation: false }
                    });
                } else {
                    ifCharts[name].data.labels = ifHistory[name].labels;
                    ifCharts[name].data.datasets[0].data = ifHistory[name].tx;
                    ifCharts[name].data.datasets[1].data = ifHistory[name].rx;
                    ifCharts[name].update();
                }
            });
        })
        .catch(err => {
            document.getElementById('statusBar').className = 'alert alert-danger';
            document.getElementById('statusBar').innerHTML = 'Error fetching data: ' + err.message;
        });
}

fetchData();
setInterval(fetchData, 10000);
</script>
@endpush
