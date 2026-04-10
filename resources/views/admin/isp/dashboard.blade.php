@extends('admin.layouts.app')
@section('title', 'ISP Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .stat-card { border-left: 4px solid; transition: transform .15s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card.primary { border-left-color: #696cff; }
    .stat-card.blue    { border-left-color: #03c3ec; }
    .stat-card.green   { border-left-color: #71dd37; }
    .stat-card.orange  { border-left-color: #ffab00; }
    .stat-card.purple  { border-left-color: #8c57ff; }
    .stat-card.pink    { border-left-color: #e83e8c; }
    .stat-card.indigo  { border-left-color: #5f61e6; }
    .stat-card.teal    { border-left-color: #20c997; }
    .stat-card.red     { border-left-color: #ff3e1d; }
    .stat-card.dark    { border-left-color: #233446; }
    .stat-icon { font-size: 2.2rem; opacity: .85; }
    .icon-primary { color: #696cff; }
    .icon-blue    { color: #03c3ec; }
    .icon-green   { color: #71dd37; }
    .icon-orange  { color: #ffab00; }
    .icon-purple  { color: #8c57ff; }
    .icon-pink    { color: #e83e8c; }
    .icon-indigo  { color: #5f61e6; }
    .icon-teal    { color: #20c997; }
    .icon-red     { color: #ff3e1d; }
    .icon-dark    { color: #233446; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">ISP Dashboard</h5>
    </div>

    {{-- ========== 9 STAT CARDS ========== --}}
    {{-- 1: Active PPPoE --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Active PPPoE Users</p>
                    <h4 class="mb-0 fw-bold">{{ $activePppoeUsers }}</h4>
                </div>
                <div class="stat-icon icon-primary"><i class="bx bx-user"></i></div>
            </div>
        </div>
    </div>

    {{-- 2: Active Hotspot --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card blue">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Active Hotspot Users</p>
                    <h4 class="mb-0 fw-bold">{{ $activeHotspotUsers }}</h4>
                </div>
                <div class="stat-icon icon-blue"><i class="bx bx-wifi"></i></div>
            </div>
        </div>
    </div>

    {{-- 3: Today PPPoE Revenue --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card green">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Today's Revenue (PPPoE)</p>
                    <h4 class="mb-0 fw-bold">KES {{ number_format($todayRevenuePppoe, 2) }}</h4>
                </div>
                <div class="stat-icon icon-green"><i class="bx bx-money"></i></div>
            </div>
        </div>
    </div>

    {{-- 4: Today Hotspot Revenue --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card orange">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Today's Revenue (Hotspot)</p>
                    <h4 class="mb-0 fw-bold">KES {{ number_format($todayRevenueHotspot, 2) }}</h4>
                </div>
                <div class="stat-icon icon-orange"><i class="bx bx-money"></i></div>
            </div>
        </div>
    </div>

    {{-- 5: Total Revenue This Month --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card purple">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Total Revenue This Month</p>
                    <h4 class="mb-0 fw-bold">KES {{ number_format($totalRevenueMonth, 2) }}</h4>
                </div>
                <div class="stat-icon icon-purple"><i class="bx bx-bar-chart"></i></div>
            </div>
        </div>
    </div>

    {{-- 6: New PPPoE Today --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card pink">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">New PPPoE Customers Today</p>
                    <h4 class="mb-0 fw-bold">{{ $newPppoeToday }}</h4>
                </div>
                <div class="stat-icon icon-pink"><i class="bx bx-user-plus"></i></div>
            </div>
        </div>
    </div>

    {{-- 7: Total PPPoE --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card indigo">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Total PPPoE Customers</p>
                    <h4 class="mb-0 fw-bold">{{ $totalPppoeCustomers }}</h4>
                </div>
                <div class="stat-icon icon-indigo"><i class="bx bx-group"></i></div>
            </div>
        </div>
    </div>

    {{-- 8: Total Hotspot --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card teal">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Total Hotspot Customers</p>
                    <h4 class="mb-0 fw-bold">{{ $totalHotspotCustomers }}</h4>
                </div>
                <div class="stat-icon icon-teal"><i class="bx bx-wifi-2"></i></div>
            </div>
        </div>
    </div>

    {{-- 9: PPPoE Expiring Today --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card red">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">PPPoE Expiring Today</p>
                    <h4 class="mb-0 fw-bold {{ $pppoeExpiringToday > 0 ? 'text-danger' : '' }}">{{ $pppoeExpiringToday }}</h4>
                </div>
                <div class="stat-icon icon-red"><i class="bx bx-time"></i></div>
            </div>
        </div>
    </div>

    {{-- 10: Total Expenses This Month --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card h-100 stat-card dark">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Expenses This Month</p>
                    <h4 class="mb-0 fw-bold text-danger">KES {{ number_format($totalExpensesMonth, 0) }}</h4>
                </div>
                <div class="stat-icon icon-dark"><i class="bx bx-wallet-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- ========== EXPIRING TABLE ========== --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-alarm-exclamation me-2 text-danger"></i>PPPoE Customers Expiring Today</h5>
                @if($expiringSubscribers->isNotEmpty())
                <span class="badge bg-danger">{{ $expiringSubscribers->count() }} expiring</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($expiringSubscribers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-check-circle text-success" style="font-size:3rem;"></i>
                    <p class="mt-2 mb-0">No PPPoE customers expiring today ✓</p>
                </div>
                @else
                <div class="table-responsive">
                    <table id="expiringTable" class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Package</th>
                                <th>Expiry Date/Time</th>
                                <th>Router</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringSubscribers as $sub)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $sub->username }}</strong></td>
                                <td>{{ $sub->full_name ?? '-' }}</td>
                                <td>{{ $sub->phone ?? '-' }}</td>
                                <td>{{ $sub->package->name ?? '-' }}</td>
                                <td>
                                    <span class="{{ $sub->expires_at->isPast() ? 'text-danger' : 'text-warning' }}">
                                        {{ $sub->expires_at->format('d M Y H:i') }}
                                    </span>
                                </td>
                                <td>{{ $sub->router->name ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.isp.subscribers.edit', $sub) }}" class="btn btn-sm btn-success">
                                        <i class="bx bx-refresh me-1"></i>Renew
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========== CHARTS ========== --}}
    <div class="col-sm-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Revenue (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-sm-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Sessions by Router</h5>
            </div>
            <div class="card-body">
                <canvas id="packageChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- ========== RECENT PAYMENTS ========== --}}
    <div class="col-sm-7 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Payments</h5>
                <a href="{{ route('admin.isp.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Phone</th>
                                <th>Package</th>
                                <th>Amount (KES)</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->phone ?? '-' }}</td>
                                <td>{{ $payment->package->name ?? '-' }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No recent payments</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== RECENT SESSIONS ========== --}}
    <div class="col-sm-5 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Sessions</h5>
                <a href="{{ route('admin.isp.sessions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>IP</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSessions as $session)
                            <tr>
                                <td>{{ $session->username }}</td>
                                <td>{{ $session->framedipaddress ?? '-' }}</td>
                                <td>{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">No active sessions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue vs Expenses Chart --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Revenue vs Expenses (Last 6 Months)</h5></div>
            <div class="card-body"><canvas id="revVsExpChart" height="80"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    @if($expiringSubscribers->isNotEmpty())
    $('#expiringTable').DataTable({ pageLength: 10, order: [[5, 'asc']] });
    @endif

    const chartLabels = @json($chartLabels);
    const chartData   = @json($chartData);
    const pieLabels   = @json($pieLabels);
    const pieData     = @json($pieData);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Revenue (KES)',
                data: chartData,
                fill: true,
                backgroundColor: 'rgba(113,221,55,0.15)',
                borderColor: '#71dd37',
                tension: 0.4,
                pointRadius: 3,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('packageChart'), {
        type: 'doughnut',
        data: {
            labels: pieLabels.length ? pieLabels : ['No sessions'],
            datasets: [{
                data: pieData.length ? pieData : [1],
                backgroundColor: ['#696cff','#71dd37','#ffab00','#03c3ec','#ff3e1d','#20c997','#fd7e14'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Revenue vs Expenses Chart
    const rveLabels   = @json($revVsExpLabels);
    const rveRevenue  = @json($revVsExpRevenue);
    const rveExpenses = @json($revVsExpExpenses);
    const rveProfit   = @json($revVsExpProfit);

    new Chart(document.getElementById('revVsExpChart'), {
        type: 'bar',
        data: {
            labels: rveLabels,
            datasets: [
                { type: 'bar',  label: 'Revenue (KES)',  data: rveRevenue,  backgroundColor: 'rgba(40,167,69,.7)',  order: 2 },
                { type: 'bar',  label: 'Expenses (KES)', data: rveExpenses, backgroundColor: 'rgba(220,53,69,.7)',  order: 2 },
                { type: 'line', label: 'Net Profit',     data: rveProfit,   borderColor: '#007bff', backgroundColor: 'transparent', tension: .4, order: 1, pointRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
