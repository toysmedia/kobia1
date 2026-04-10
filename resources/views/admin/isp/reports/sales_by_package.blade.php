@extends('admin.layouts.app')
@section('title', 'Sales by Package')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Sales by Package</h5>
    </div>

    {{-- Filter --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- PPPoE Section --}}
    <div class="col-md-8 col-sm-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bx bx-user me-2"></i>PPPoE Packages</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="pppoeTable" class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Package Name</th>
                                <th>Total Sold</th>
                                <th>Total Revenue (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pppoePackages as $pkg)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pkg->package?->name ?? 'Unknown' }}</td>
                                <td>{{ $pkg->total_sold }}</td>
                                <td><strong>{{ number_format($pkg->total_revenue, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>{{ $pppoePackages->sum('total_sold') }}</strong></td>
                                <td><strong>KES {{ number_format($pppoePackages->sum('total_revenue'), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">PPPoE Revenue by Package</h6>
            </div>
            <div class="card-body">
                <canvas id="pppoePie"></canvas>
            </div>
        </div>
    </div>

    {{-- Hotspot Section --}}
    <div class="col-md-8 col-sm-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bx bx-wifi me-2"></i>Hotspot Packages</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="hotspotTable" class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Package Name</th>
                                <th>Total Sold</th>
                                <th>Total Revenue (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hotspotPackages as $pkg)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pkg->package?->name ?? 'Unknown' }}</td>
                                <td>{{ $pkg->total_sold }}</td>
                                <td><strong>{{ number_format($pkg->total_revenue, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>{{ $hotspotPackages->sum('total_sold') }}</strong></td>
                                <td><strong>KES {{ number_format($hotspotPackages->sum('total_revenue'), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Hotspot Revenue by Package</h6>
            </div>
            <div class="card-body">
                <canvas id="hotspotPie"></canvas>
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
    $('#pppoeTable').DataTable({ pageLength: 10, searching: false, paging: false });
    $('#hotspotTable').DataTable({ pageLength: 10, searching: false, paging: false });

    const colors = ['#71dd37','#696cff','#03c3ec','#ffab00','#ff3e1d','#20c997','#fd7e14'];

    const pppoeLabels   = @json($pppoePackages->map(fn($p) => $p->package?->name ?? 'Unknown'));
    const pppoeRevenue  = @json($pppoePackages->pluck('total_revenue'));
    const hotspotLabels = @json($hotspotPackages->map(fn($p) => $p->package?->name ?? 'Unknown'));
    const hotspotRev    = @json($hotspotPackages->pluck('total_revenue'));

    if (pppoeLabels.length) {
        new Chart(document.getElementById('pppoePie'), {
            type: 'doughnut',
            data: { labels: pppoeLabels, datasets: [{ data: pppoeRevenue, backgroundColor: colors }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    if (hotspotLabels.length) {
        new Chart(document.getElementById('hotspotPie'), {
            type: 'doughnut',
            data: { labels: hotspotLabels, datasets: [{ data: hotspotRev, backgroundColor: colors }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
@endpush
