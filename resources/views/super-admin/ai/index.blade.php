@extends('layouts.super-admin')
@section('title', 'AI Assistant Dashboard')
@section('page-title', 'AI Assistant')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-robot me-2 text-primary"></i>AI Assistant Dashboard</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('super-admin.ai.knowledge.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Knowledge</a>
        <a href="{{ route('super-admin.ai.unanswered') }}" class="btn btn-warning btn-sm"><i class="bi bi-flag me-1"></i>Unanswered Queue</a>
    </div>
</div>

{{-- Stats cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary"><i class="bi bi-chat-dots fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Conversations</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalConversations) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success"><i class="bi bi-calendar-check fs-4"></i></div>
                <div>
                    <div class="text-muted small">Today's Conversations</div>
                    <div class="fs-4 fw-bold">{{ number_format($todayConversations) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning"><i class="bi bi-question-circle fs-4"></i></div>
                <div>
                    <div class="text-muted small">Unanswered Rate</div>
                    <div class="fs-4 fw-bold">{{ $unansweredRate }}%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info"><i class="bi bi-database fs-4"></i></div>
                <div>
                    <div class="text-muted small">KB Entries</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalKbEntries) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Chart --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom"><strong>Conversations (Last 30 Days)</strong></div>
            <div class="card-body">
                <canvas id="dailyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    {{-- Quick links --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom"><strong>Quick Links</strong></div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('super-admin.ai.knowledge') }}" class="btn btn-outline-primary"><i class="bi bi-book me-2"></i>Knowledge Base ({{ $totalKbEntries }})</a>
                <a href="{{ route('super-admin.ai.unanswered') }}" class="btn btn-outline-warning"><i class="bi bi-flag me-2"></i>Unanswered Queue ({{ $unansweredCount }})</a>
                <a href="{{ route('super-admin.ai.conversations') }}" class="btn btn-outline-info"><i class="bi bi-chat-square-text me-2"></i>Conversation Logs</a>
                <a href="{{ route('super-admin.ai.reports') }}" class="btn btn-outline-secondary"><i class="bi bi-bar-chart me-2"></i>Reports</a>
                <hr class="my-1">
                <div class="small text-muted">Tokens used (all time): <strong>{{ number_format($tokensUsed) }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- Top questions --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom"><strong>Top 5 Most Asked Questions</strong></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Question</th><th>Count</th></tr></thead>
            <tbody>
                @forelse($topQuestions as $i => $q)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ Str::limit($q->question, 80) }}</td>
                    <td><span class="badge bg-primary">{{ $q->cnt }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No conversations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const labels = @json($dailyStats->pluck('date'));
const data   = @json($dailyStats->pluck('cnt'));
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Conversations',
            data,
            backgroundColor: 'rgba(79,70,229,.65)',
            borderRadius: 4,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
@endpush
