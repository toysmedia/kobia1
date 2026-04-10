@extends('admin.layouts.app')
@section('title', 'Hotspot Sales Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Hotspot Sales Report</h5>
            </div>
            <button id="exportCsv" class="btn btn-success"><i class="bx bx-download me-1"></i>Export CSV</button>
        </div>
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
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i>Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="col-md-6 col-sm-12 mb-4">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bx bx-money text-info" style="font-size:2.5rem;"></i>
                <h6 class="text-muted mt-2">Total Revenue</h6>
                <h3 class="fw-bold text-info">KES {{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 mb-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <i class="bx bx-receipt text-primary" style="font-size:2.5rem;"></i>
                <h6 class="text-muted mt-2">Total Transactions</h6>
                <h3 class="fw-bold text-primary">{{ $totalTransactions }}</h3>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Daily Revenue (Hotspot)</h6></div>
            <div class="card-body">
                <canvas id="hotspotChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="salesTable" class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Phone</th>
                                <th>Voucher Code</th>
                                <th>Package</th>
                                <th>Amount (KES)</th>
                                <th>M-Pesa Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $p->created_at->format('d M Y') }}</td>
                                <td>{{ $p->phone ?? '-' }}</td>
                                <td><code>{{ $p->mpesa_code ?? $p->reference ?? $p->transaction_id ?? '-' }}</code></td>
                                <td>{{ $p->package->name ?? '-' }}</td>
                                <td>{{ number_format($p->amount, 2) }}</td>
                                <td><code>{{ $p->transaction_id ?? '-' }}</code></td>
                            </tr>
                            @endforeach
                        </tbody>
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
    const dt = $('#salesTable').DataTable({ pageLength: 25, order: [[1, 'desc']] });

    new Chart(document.getElementById('hotspotChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Revenue (KES)',
                data: @json($chartData),
                backgroundColor: 'rgba(3,195,236,0.7)',
                borderColor: '#03c3ec',
                borderWidth: 1
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    $('#exportCsv').on('click', function () {
        const headers = ['#','Date','Phone','Voucher Code','Package','Amount','M-Pesa Receipt'];
        const rows = [];
        dt.rows({ search: 'applied' }).every(function () {
            const d = this.data();
            rows.push([d[0],d[1],d[2],d[3],d[4],d[5],d[6]]);
        });
        const csv = [headers, ...rows].map(r => r.map(v => '"' + String(v ?? '').replace(/<[^>]+>/g,'').replace(/"/g,'""') + '"').join(',')).join('\n');
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
        a.download = 'hotspot_sales_{{ $dateFrom }}_{{ $dateTo }}.csv';
        a.click();
    });
});
</script>
@endpush
