@extends('admin.layouts.app')
@section('title', 'Expense Report')
@push('styles')
<style>
.report-card { border-left: 4px solid; }
.report-card.red { border-left-color: #ff3e1d; }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Expense Report</h5>
    </div>

    {{-- Date Filter --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form class="row g-2 align-items-end" method="GET">
                    <div class="col-auto">
                        <label class="form-label">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">To</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="{{ route('admin.isp.expenses.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-success">Export CSV</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Total Card --}}
    <div class="col-md-4 mb-4">
        <div class="card h-100 report-card red">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Total Expenses</p>
                    <h4 class="mb-0 fw-bold text-danger">KES {{ number_format($totalExpenses, 2) }}</h4>
                    <small class="text-muted">{{ $from }} to {{ $to }}</small>
                </div>
                <div class="text-danger" style="font-size:2.5rem"><i class="bx bx-wallet-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- Pie Chart --}}
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">By Category</h6></div>
            <div class="card-body"><canvas id="categoryPie" height="200"></canvas></div>
        </div>
    </div>

    {{-- Monthly Trend --}}
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Monthly Trend</h6></div>
            <div class="card-body"><canvas id="monthlyBar" height="200"></canvas></div>
        </div>
    </div>

    {{-- Category Table --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Breakdown by Category</h6></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="table-light"><tr><th>Category</th><th>Total (KES)</th><th>%</th></tr></thead>
                    <tbody>
                        @foreach($byCategory->sortDesc() as $cat => $amount)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $cat)) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            <td>{{ $totalExpenses > 0 ? number_format($amount / $totalExpenses * 100, 1) : 0 }}%</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var catLabels = @json($byCategory->keys()->map(fn($k) => ucfirst(str_replace('_',' ',$k))));
var catData   = @json($byCategory->values());
new Chart(document.getElementById('categoryPie'), {
    type: 'pie',
    data: { labels: catLabels, datasets: [{ data: catData, backgroundColor: ['#ff3e1d','#ff9f40','#ffcd56','#4bc0c0','#36a2eb','#9966ff','#ff6384','#c9cbcf'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});

var monthLabels = @json(array_keys($monthlyTrend));
var monthData   = @json(array_values($monthlyTrend));
new Chart(document.getElementById('monthlyBar'), {
    type: 'bar',
    data: { labels: monthLabels, datasets: [{ label: 'Expenses (KES)', data: monthData, backgroundColor: '#ff3e1d' }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
