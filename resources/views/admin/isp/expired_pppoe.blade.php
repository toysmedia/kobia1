@extends('admin.layouts.app')
@section('title', 'Expired PPPoE Subscribers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .filter-btn.active { font-weight: 600; }
    #customDateRange { display: none; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Expired PPPoE Subscribers</h5>
            </div>
            <button id="exportCsv" class="btn btn-success">
                <i class="bx bx-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <div class="col-sm-12 mb-3">
        <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex flex-wrap gap-2 mb-3" id="filterBtns">
                    <button class="btn btn-outline-primary filter-btn {{ $filter === 'today' ? 'active' : '' }}" data-filter="today">Today</button>
                    <button class="btn btn-outline-primary filter-btn {{ $filter === 'yesterday' ? 'active' : '' }}" data-filter="yesterday">Yesterday</button>
                    <button class="btn btn-outline-primary filter-btn {{ $filter === '7days' ? 'active' : '' }}" data-filter="7days">Last 7 Days</button>
                    <button class="btn btn-outline-primary filter-btn {{ $filter === '30days' ? 'active' : '' }}" data-filter="30days">Last 30 Days</button>
                    <button class="btn btn-outline-primary filter-btn {{ $filter === 'custom' ? 'active' : '' }}" data-filter="custom">Custom Date Range</button>
                    <button class="btn btn-outline-secondary filter-btn {{ $filter === 'all' ? 'active' : '' }}" data-filter="all">All Expired</button>
                </div>
                <div id="customDateRange" class="d-flex gap-2 mb-3 align-items-end">
                    <div>
                        <label class="form-label small mb-1">From</label>
                        <input type="date" id="startDate" class="form-control form-control-sm">
                    </div>
                    <div>
                        <label class="form-label small mb-1">To</label>
                        <input type="date" id="endDate" class="form-control form-control-sm">
                    </div>
                    <button id="applyCustom" class="btn btn-primary btn-sm">Apply</button>
                </div>
                <div id="countBadge" class="mb-2"></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="expiredTable" class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Package</th>
                                <th>Expired At</th>
                                <th>Router</th>
                                <th>Days Since Expiry</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
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
<script>
let currentFilter = '{{ $filter }}';
let currentStart  = '';
let currentEnd    = '';
let dt;
let allData = [];

$(function () {
    dt = $('#expiredTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [8] }]
    });

    // Show custom date range inputs if custom filter active
    if (currentFilter === 'custom') {
        $('#customDateRange').show();
    }

    loadData(currentFilter);

    // Filter button clicks
    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');

        if (currentFilter === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
            loadData(currentFilter);
        }
    });

    $('#applyCustom').on('click', function () {
        currentStart = $('#startDate').val();
        currentEnd   = $('#endDate').val();
        loadData('custom', currentStart, currentEnd);
    });

    $('#exportCsv').on('click', function () {
        if (!allData.length) return;
        const headers = ['#','Username','Full Name','Phone','Package','Expired At','Router','Days Since Expiry'];
        const rows = allData.map((r, i) => [
            i+1, r.username, r.full_name, r.phone, r.package, r.expires_at, r.router, r.days_since_expiry
        ]);
        let csv = [headers, ...rows].map(r => r.map(v => '"' + String(v).replace(/"/g,'""') + '"').join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'expired_pppoe_' + currentFilter + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    });
});

function loadData(filter, start, end) {
    let url = '{{ route("admin.isp.expired_pppoe.data") }}?filter=' + filter;
    if (start) url += '&start_date=' + start;
    if (end)   url += '&end_date='   + end;

    $.getJSON(url, function (res) {
        allData = res.data;
        dt.clear();

        if (!allData.length) {
            $('#countBadge').html('<span class="badge bg-secondary">0 records</span>');
            dt.draw();
            return;
        }

        $('#countBadge').html('<span class="badge bg-danger">' + allData.length + ' expired subscriber' + (allData.length !== 1 ? 's' : '') + '</span>');

        allData.forEach(function (r, i) {
            dt.row.add([
                i + 1,
                '<strong>' + escHtml(r.username) + '</strong>',
                escHtml(r.full_name),
                escHtml(r.phone),
                escHtml(r.package),
                '<span class="text-danger">' + escHtml(r.expires_at) + '</span>',
                escHtml(r.router),
                '<span class="badge bg-label-warning">' + r.days_since_expiry + ' day' + (r.days_since_expiry !== 1 ? 's' : '') + '</span>',
                '<div class="d-flex gap-1 justify-content-center">' +
                    '<a href="' + escHtml(r.edit_url) + '" class="btn btn-sm btn-success" title="Reactivate"><i class="bx bx-refresh"></i></a>' +
                    '<form method="POST" action="' + escHtml(r.destroy_url) + '" onsubmit="return confirm(\'Delete subscriber ' + escHtml(r.username) + '?\')">' +
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bx bx-trash"></i></button>' +
                    '</form>' +
                '</div>'
            ]);
        });

        dt.draw();
    });
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
