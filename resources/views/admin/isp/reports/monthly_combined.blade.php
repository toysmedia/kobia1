@extends('admin.layouts.app')
@section('title', 'Monthly Combined Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Monthly Combined Report</h5>
    </div>

    {{-- Month/Year Selector --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Stacked Chart --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Daily Revenue — {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</h6>
            </div>
            <div class="card-body">
                <canvas id="combinedChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="combinedTable" class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>PPPoE Sales (KES)</th>
                                <th>Hotspot Sales (KES)</th>
                                <th>Combined Total (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            @if($row['combined'] > 0)
                            <tr>
                                <td>{{ date('d M Y', strtotime($row['date'])) }}</td>
                                <td>{{ number_format($row['pppoe'], 2) }}</td>
                                <td>{{ number_format($row['hotspot'], 2) }}</td>
                                <td><strong>{{ number_format($row['combined'], 2) }}</strong></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>KES {{ number_format(collect($rows)->sum('pppoe'), 2) }}</strong></td>
                                <td><strong>KES {{ number_format(collect($rows)->sum('hotspot'), 2) }}</strong></td>
                                <td><strong>KES {{ number_format(collect($rows)->sum('combined'), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
    $('#combinedTable').DataTable({ pageLength: 31, paging: false });

    new Chart(document.getElementById('combinedChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'PPPoE',
                    data: @json($pppoeChart),
                    backgroundColor: 'rgba(113,221,55,0.8)',
                    stack: 'stack'
                },
                {
                    label: 'Hotspot',
                    data: @json($hotspotChart),
                    backgroundColor: 'rgba(3,195,236,0.8)',
                    stack: 'stack'
                }
            ]
        },
        options: {
            responsive: true,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
            plugins: { legend: { position: 'top' } }
        }
    });
});
</script>
@endpush
