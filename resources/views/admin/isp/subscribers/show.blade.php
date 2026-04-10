@extends('admin.layouts.app')
@section('title', 'Subscriber: ' . $subscriber->name)
@push('styles')
<style>
.stat-mini { border-left: 3px solid; padding-left: 10px; }
.stat-mini.blue { border-left-color: #007bff; }
.stat-mini.green { border-left-color: #28a745; }
.stat-mini.orange { border-left-color: #fd7e14; }
.stat-mini.purple { border-left-color: #6f42c1; }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ $subscriber->name }}</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.isp.subscribers.edit', $subscriber) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i> Edit</a>
                <a href="{{ route('admin.isp.subscribers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
            </div>
        </div>
    </div>

    {{-- Customer Info --}}
    <div class="col-md-5 mb-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-user me-1"></i> Customer Info</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th>Name</th><td>{{ $subscriber->name }}</td></tr>
                    <tr><th>Username</th><td><code>{{ $subscriber->username }}</code></td></tr>
                    <tr><th>Phone</th><td>{{ $subscriber->phone }}</td></tr>
                    <tr><th>Email</th><td>{{ $subscriber->email ?? '-' }}</td></tr>
                    <tr><th>Package</th><td>{{ $subscriber->package->name ?? '-' }}</td></tr>
                    <tr><th>Router</th><td>{{ $subscriber->router->name ?? '-' }}</td></tr>
                    <tr><th>Type</th><td><span class="badge bg-info">{{ strtoupper($subscriber->connection_type) }}</span></td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge bg-{{ $subscriber->status === 'active' ? 'success' : ($subscriber->status === 'expired' ? 'danger' : 'warning') }}">
                            {{ ucfirst($subscriber->status) }}
                        </span></td></tr>
                    <tr><th>Created</th><td>{{ $subscriber->created_at->format('d M Y') }}</td></tr>
                    <tr><th>Expires</th><td>{{ $subscriber->expires_at ? $subscriber->expires_at->format('d M Y H:i') : '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Usage Stats --}}
    <div class="col-md-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0"><i class="bx bx-data me-1"></i> Usage Statistics</h6>
                @if($activeSession)
                <span class="badge bg-success pulse">Active Session</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="stat-mini blue">
                            <div class="small text-muted">Downloaded</div>
                            <div class="fw-bold">
                                @php $dl = $radacctStats->total_download ?? 0; @endphp
                                {{ $dl >= 1073741824 ? round($dl/1073741824,2).' GB' : round($dl/1048576,2).' MB' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-mini green">
                            <div class="small text-muted">Uploaded</div>
                            <div class="fw-bold">
                                @php $ul = $radacctStats->total_upload ?? 0; @endphp
                                {{ $ul >= 1073741824 ? round($ul/1073741824,2).' GB' : round($ul/1048576,2).' MB' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-mini orange">
                            <div class="small text-muted">Sessions</div>
                            <div class="fw-bold">{{ $radacctStats->total_sessions ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-mini purple">
                            <div class="small text-muted">Total Time</div>
                            @php $secs = $radacctStats->total_time ?? 0; $hrs = floor($secs/3600); $mins = floor(($secs%3600)/60); @endphp
                            <div class="fw-bold">{{ $hrs }}h {{ $mins }}m</div>
                        </div>
                    </div>
                </div>

                @if($activeSession)
                <div class="alert alert-success py-2 mb-3" id="activeSessionCard">
                    <small class="d-block fw-bold mb-1">Current Session</small>
                    <div class="row">
                        <div class="col-6"><small class="text-muted">IP</small><div>{{ $activeSession->framedipaddress }}</div></div>
                        <div class="col-6"><small class="text-muted">NAS</small><div>{{ $activeSession->nasipaddress }}</div></div>
                        <div class="col-6"><small class="text-muted">Started</small><div>{{ \Carbon\Carbon::parse($activeSession->acctstarttime)->format('H:i') }}</div></div>
                        <div class="col-6"><small class="text-muted">RX/TX</small><div id="liveRxTx">{{ round(($activeSession->acctinputoctets??0)/1048576,1) }}MB / {{ round(($activeSession->acctoutputoctets??0)/1048576,1) }}MB</div></div>
                    </div>
                </div>
                @endif

                <canvas id="usageChart" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-credit-card me-1"></i> Payment History</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Amount</th><th>Trans ID</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td class="fw-bold">KES {{ number_format($payment->amount, 2) }}</td>
                                <td><small>{{ $payment->transaction_id ?? $payment->mpesa_code ?? '-' }}</small></td>
                                <td><span class="badge bg-{{ ($payment->status ?? 'completed') === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($payment->status ?? 'completed') }}</span></td>
                                <td><small>{{ $payment->created_at->format('d M Y H:i') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No payments found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Session History --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-history me-1"></i> Session History</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Start</th><th>Duration</th><th>RX/TX</th><th>IP</th></tr></thead>
                        <tbody>
                            @forelse($sessions as $session)
                            @php
                                $dur = $session->acctsessiontime ?? 0;
                                $durStr = floor($dur/3600).'h '.floor(($dur%3600)/60).'m';
                                $rx = round(($session->acctinputoctets??0)/1048576, 1);
                                $tx = round(($session->acctoutputoctets??0)/1048576, 1);
                            @endphp
                            <tr>
                                <td><small>{{ \Carbon\Carbon::parse($session->acctstarttime)->format('d M H:i') }}</small></td>
                                <td>{{ $durStr }}</td>
                                <td><small>{{ $rx }}M/{{ $tx }}M</small></td>
                                <td><small>{{ $session->framedipaddress ?? '-' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No sessions found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Usage chart
var chartData = @json($chartData);
var usageChart = new Chart(document.getElementById('usageChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: [
            { label: 'Download (MB)', data: chartData.download, borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,.1)', fill: true, tension: .4, pointRadius: 2 },
            { label: 'Upload (MB)', data: chartData.upload, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,.1)', fill: true, tension: .4, pointRadius: 2 },
        ]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }, scales: { y: { beginAtZero: true } }, animation: false }
});

@if($activeSession)
// Auto-refresh active session data every 30 seconds
function refreshSession() {
    fetch('{{ route("admin.isp.subscribers.usage_data", $subscriber) }}')
        .then(r => r.json())
        .then(data => {
            // Update the usage chart with fresh data
            if (data.labels && usageChart) {
                usageChart.data.labels = data.labels;
                usageChart.data.datasets[0].data = data.download;
                usageChart.data.datasets[1].data = data.upload;
                usageChart.update();
            }
        })
        .catch(err => console.warn('Session refresh failed:', err));
}
setInterval(refreshSession, 30000);
@endif
</script>
@endpush
