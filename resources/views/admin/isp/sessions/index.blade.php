@extends('admin.layouts.app')
@section('title', 'Live Sessions')

@push('styles')
<style>
    .session-row-online { background: rgba(113, 221, 55, 0.05); }
    #autoRefreshBar { font-size: 0.85rem; }
    .bytes-cell { font-size: 0.8rem; color: #6c757d; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Live Sessions</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div id="autoRefreshBar">
                    <span class="text-muted">Auto-refresh in </span>
                    <span id="countdown" class="fw-bold text-primary">30</span>s
                    <button id="btnPause" class="btn btn-sm btn-outline-secondary ms-2" onclick="togglePause()">Pause</button>
                </div>
                <button class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="bx bx-refresh me-1"></i> Refresh Now
                </button>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="col-sm-12 mb-3">
        <div class="card">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Username"
                               value="{{ request('username') }}">
                    </div>
                    <div class="col-auto">
                        <input type="text" name="nas_ip" class="form-control form-control-sm" placeholder="NAS IP"
                               value="{{ request('nas_ip') }}">
                    </div>
                    <div class="col-auto">
                        <select name="router_id" class="form-select form-select-sm">
                            <option value="">All Routers</option>
                            @foreach($routers ?? [] as $router)
                                <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                    {{ $router->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('admin.isp.sessions.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                    </div>
                    <div class="col-auto ms-auto">
                        <span class="badge bg-label-success fs-6">{{ $sessions->total() ?? count($sessions) }} active</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>NAS IP</th>
                                <th>Framed IP</th>
                                <th>Session Time</th>
                                <th>Data In</th>
                                <th>Data Out</th>
                                <th>Start Time</th>
                                <th>NAS Port ID</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                            <tr class="session-row-online">
                                <td>
                                    <strong>{{ $session->username }}</strong>
                                </td>
                                <td><code>{{ $session->nasipaddress ?? '-' }}</code></td>
                                <td><code>{{ $session->framedipaddress ?? '-' }}</code></td>
                                <td>
                                    <span class="badge bg-label-info">{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</span>
                                </td>
                                <td class="bytes-cell">
                                    @php $inMB = ($session->acctinputoctets ?? 0) / 1048576; @endphp
                                    @if($inMB >= 1024)
                                        {{ number_format($inMB / 1024, 2) }} GB
                                    @else
                                        {{ number_format($inMB, 2) }} MB
                                    @endif
                                </td>
                                <td class="bytes-cell">
                                    @php $outMB = ($session->acctoutputoctets ?? 0) / 1048576; @endphp
                                    @if($outMB >= 1024)
                                        {{ number_format($outMB / 1024, 2) }} GB
                                    @else
                                        {{ number_format($outMB, 2) }} MB
                                    @endif
                                </td>
                                <td>
                                    {{ isset($session->acctstarttime) ? \Carbon\Carbon::parse($session->acctstarttime)->format('d M H:i:s') : '-' }}
                                </td>
                                <td>{{ $session->nasportid ?? '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.isp.sessions.disconnect', $session->radacctid ?? $session->id) }}" method="POST"
                                          onsubmit="return confirm('Disconnect {{ addslashes($session->username) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Disconnect">
                                            <i class="bx bx-wifi-off"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bx bx-wifi-off d-block mb-2" style="font-size:2rem;"></i>
                                    No active sessions found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sessions instanceof \Illuminate\Pagination\LengthAwarePaginator && $sessions->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $sessions->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let paused = false;
let countdown = 30;
let timer;

function tick() {
    if (paused) return;
    countdown--;
    document.getElementById('countdown').textContent = countdown;
    if (countdown <= 0) location.reload();
}

timer = setInterval(tick, 1000);

function togglePause() {
    paused = !paused;
    const btn = document.getElementById('btnPause');
    if (paused) {
        btn.textContent = 'Resume';
        btn.classList.replace('btn-outline-secondary', 'btn-outline-success');
    } else {
        btn.textContent = 'Pause';
        btn.classList.replace('btn-outline-success', 'btn-outline-secondary');
        countdown = 30;
    }
}
</script>
@endpush
