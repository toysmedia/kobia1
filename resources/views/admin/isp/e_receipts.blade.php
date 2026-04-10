@extends('admin.layouts.app')
@section('title', 'Hotspot e-Receipts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .row-new { background-color: rgba(113, 221, 55, 0.12) !important; }
    .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }
    .filter-btn.active { font-weight: 600; }
    .badge-status-active  { background-color: #71dd37; color: #fff; }
    .badge-status-expired { background-color: #ff3e1d; color: #fff; }
    .badge-status-used    { background-color: #8592a3; color: #fff; }
    #autoRefreshIndicator { font-size: .8rem; color: #8592a3; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Hotspot e-Receipts</h5>
            </div>
            <span id="autoRefreshIndicator"><i class="bx bx-refresh me-1"></i>Auto-refresh every 15s</span>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div id="filterBtns" class="d-flex gap-2">
                    <button class="btn btn-outline-primary filter-btn active" data-filter="today">Today</button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="week">This Week</button>
                    <button class="btn btn-outline-secondary filter-btn" data-filter="all">All</button>
                </div>
                <span id="countBadge" class="ms-2"></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="receiptsTable" class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Receipt No (M-Pesa)</th>
                                <th>Phone</th>
                                <th>Amount (KES)</th>
                                <th>Package</th>
                                <th>Voucher Code</th>
                                <th>Created At</th>
                                <th>Expires At</th>
                                <th>Status</th>
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

{{-- Toast --}}
<div class="toast-container">
    <div id="newVoucherToast" class="toast align-items-center text-bg-success border-0" role="alert" data-bs-autohide="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bx bx-check-circle me-2"></i><strong>New voucher!</strong> A new hotspot payment was received.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
let currentFilter = 'today';
let dt;
let lastCount = 0;
let refreshTimer;

$(function () {
    dt = $('#receiptsTable').DataTable({
        pageLength: 25,
        order: [[6, 'desc']],
        columnDefs: [{ orderable: false, targets: [9] }]
    });

    loadData(currentFilter);

    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        clearInterval(refreshTimer);
        loadData(currentFilter);
        startAutoRefresh();
    });

    startAutoRefresh();
});

function startAutoRefresh() {
    refreshTimer = setInterval(function () {
        loadData(currentFilter, true);
    }, 15000);
}

function loadData(filter, isRefresh) {
    $.getJSON('{{ route("admin.isp.ereceipts.data") }}?filter=' + filter, function (res) {
        const data = res.data;

        if (isRefresh && data.length > lastCount) {
            showToast();
        }
        lastCount = data.length;

        $('#countBadge').html('<span class="badge bg-primary">' + data.length + ' receipt' + (data.length !== 1 ? 's' : '') + '</span>');

        dt.clear();
        data.forEach(function (r, i) {
            const statusClass = 'badge-status-' + r.status;
            const rowClass    = r.is_new ? 'row-new' : '';
            const row = dt.row.add([
                i + 1,
                '<code>' + escHtml(r.mpesa_receipt) + '</code>',
                escHtml(r.phone),
                '<strong>' + escHtml(r.amount) + '</strong>',
                escHtml(r.package),
                '<code class="text-primary">' + escHtml(r.voucher_code) + '</code>',
                escHtml(r.created_at),
                r.expires_at ? escHtml(r.expires_at) : '<span class="text-muted">-</span>',
                '<span class="badge ' + statusClass + '">' + r.status.charAt(0).toUpperCase() + r.status.slice(1) + '</span>',
                '<div class="d-flex gap-1 justify-content-center">' +
                    '<button class="btn btn-sm btn-outline-info" title="View" onclick="viewReceipt(' + r.id + ')"><i class="bx bx-show"></i></button>' +
                    '<button class="btn btn-sm btn-outline-success" title="Resend SMS" onclick="resendSms(' + r.id + ', \'' + escHtml(r.phone) + '\')"><i class="bx bx-message-rounded-dots"></i></button>' +
                '</div>'
            ]);
            if (rowClass) {
                $(row.node()).addClass(rowClass);
            }
        });
        dt.draw();
    });
}

function showToast() {
    const toastEl = document.getElementById('newVoucherToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}

function viewReceipt(id) {
    alert('View receipt #' + id + ' – implement modal or detail page as needed.');
}

function resendSms(id, phone) {
    if (!confirm('Resend SMS to ' + phone + '?')) return;
    alert('SMS resend for receipt #' + id + ' – wire up your SMS endpoint.');
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
