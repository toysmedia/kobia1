@extends('layouts.super-admin')
@section('title', 'AI Reports')
@section('page-title', 'AI Reports')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-bar-chart me-2 text-secondary"></i>AI Reports</h4>
    <a href="{{ route('super-admin.ai.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

{{-- Date range filter --}}
<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm">Apply</button>
            </div>
        </div>
    </div>
</form>

{{-- Stats cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-primary">{{ number_format($totalQueries) }}</div>
            <div class="text-muted small">Total Queries</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-success">{{ $avgResponseTime ? round($avgResponseTime) . 'ms' : '—' }}</div>
            <div class="text-muted small">Avg Response Time</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-info">{{ $kbHitRate }}%</div>
            <div class="text-muted small">KB Hit Rate</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-warning">{{ $unansweredRate }}%</div>
            <div class="text-muted small">Unanswered Rate</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom"><strong>Queries by Portal</strong></div>
            <div class="card-body"><canvas id="portalChart" height="200"></canvas></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom"><strong>Queries Over Time</strong></div>
            <div class="card-body"><canvas id="timeChart" height="120"></canvas></div>
        </div>
    </div>
</div>

{{-- Top questions --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom"><strong>Top 20 Questions</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>#</th><th>Question</th><th>Count</th></tr></thead>
            <tbody>
                @forelse($topQuestions as $i => $q)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ Str::limit($q->question, 100) }}</td>
                    <td><span class="badge bg-primary">{{ $q->cnt }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// Portal pie chart
new Chart(document.getElementById('portalChart'), {
    type: 'doughnut',
    data: {
        labels: @json($byPortal->pluck('portal')),
        datasets: [{ data: @json($byPortal->pluck('cnt')), backgroundColor: ['#4f46e5','#16a34a','#f59e0b','#0891b2','#dc2626'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
// Over time line chart
new Chart(document.getElementById('timeChart'), {
    type: 'line',
    data: {
        labels: @json($overTime->pluck('date')),
        datasets: [{ label: 'Queries', data: @json($overTime->pluck('cnt')), fill: true, backgroundColor: 'rgba(79,70,229,.1)', borderColor: '#4f46e5', tension: .4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
