@extends('admin.layouts.app')
@section('title', 'Revenue Summary')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .summary-card { border-top: 4px solid; }
    .summary-card.today     { border-top-color: #71dd37; }
    .summary-card.yesterday { border-top-color: #ffab00; }
    .summary-card.week      { border-top-color: #03c3ec; }
    .summary-card.month     { border-top-color: #696cff; }
    .summary-card.lastmonth { border-top-color: #8592a3; }
    .summary-card.year      { border-top-color: #ff3e1d; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Revenue Summary</h5>
    </div>

    {{-- Summary Cards --}}
    @foreach([
        ['label' => 'Today',        'value' => $today,     'class' => 'today',     'icon' => 'bx-sun',      'color' => 'text-success'],
        ['label' => 'Yesterday',    'value' => $yesterday, 'class' => 'yesterday', 'icon' => 'bx-time-five','color' => 'text-warning'],
        ['label' => 'This Week',    'value' => $week,      'class' => 'week',      'icon' => 'bx-calendar', 'color' => 'text-info'],
        ['label' => 'This Month',   'value' => $month,     'class' => 'month',     'icon' => 'bx-bar-chart','color' => 'text-primary'],
        ['label' => 'Last Month',   'value' => $lastMonth, 'class' => 'lastmonth', 'icon' => 'bx-history',  'color' => 'text-secondary'],
        ['label' => 'This Year',    'value' => $year,      'class' => 'year',      'icon' => 'bx-trophy',   'color' => 'text-danger'],
    ] as $card)
    <div class="col-xl-2 col-md-4 col-sm-6 col-12 mb-4">
        <div class="card summary-card {{ $card['class'] }} text-center">
            <div class="card-body">
                <i class="bx {{ $card['icon'] }} {{ $card['color'] }}" style="font-size:2rem;"></i>
                <p class="text-muted small mt-2 mb-1">{{ $card['label'] }}</p>
                <h5 class="fw-bold {{ $card['color'] }}">KES {{ number_format($card['value'], 2) }}</h5>
            </div>
        </div>
    </div>
    @endforeach

    {{-- 12-Month Trend --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">12-Month Revenue Trend</h6></div>
            <div class="card-body">
                <canvas id="trendChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- Top 5 Packages --}}
    <div class="col-md-6 col-sm-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-package me-2 text-primary"></i>Top 5 Packages by Revenue</h6>
            </div>
            <div class="card-body p-0">
                <table id="topPkgTable" class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Package</th>
                            <th>Count</th>
                            <th>Revenue (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPackages as $pkg)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pkg->package?->name ?? 'Unknown' }}</td>
                            <td>{{ $pkg->count }}</td>
                            <td><strong>{{ number_format($pkg->total_revenue, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 5 PPPoE Customers --}}
    <div class="col-md-6 col-sm-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-user me-2 text-success"></i>Top 5 PPPoE Customers by Spend</h6>
            </div>
            <div class="card-body p-0">
                <table id="topCustTable" class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Payments</th>
                            <th>Total Spend (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $c->subscriber?->username ?? '-' }}</td>
                            <td>{{ $c->count }}</td>
                            <td><strong>{{ number_format($c->total_spend, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
    $('#topPkgTable').DataTable({ searching: false, paging: false, info: false });
    $('#topCustTable').DataTable({ searching: false, paging: false, info: false });

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                label: 'Total Revenue (KES)',
                data: @json($trendData),
                fill: true,
                backgroundColor: 'rgba(105,108,255,0.1)',
                borderColor: '#696cff',
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#696cff'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
