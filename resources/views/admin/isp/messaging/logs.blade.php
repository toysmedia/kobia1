@extends('admin.layouts.app')
@section('title', 'Message Logs')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Message Logs</h5>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form class="row g-2 align-items-center" method="GET">
                    <div class="col-auto">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="sms" {{ request('type') === 'sms' ? 'selected' : '' }}>SMS</option>
                            <option value="whatsapp" {{ request('type') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="email" {{ request('type') === 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('admin.isp.messaging.logs') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="logsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Recipient</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->type === 'sms' ? 'info' : ($log->type === 'whatsapp' ? 'success' : 'primary') }}">
                                        {{ strtoupper($log->type) }}
                                    </span>
                                </td>
                                <td>{{ $log->recipient }}</td>
                                <td>{{ Str::limit($log->subject ?? '-', 30) }}</td>
                                <td>{{ Str::limit($log->message, 50) }}</td>
                                <td>{{ $log->gateway ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'sent' || $log->status === 'delivered' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td>{{ $log->sent_at?->format('d M Y H:i') ?? $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No message logs found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    if (typeof $.fn.DataTable !== 'undefined' && $('#logsTable tbody tr').length > 0) {
        // DataTables is initialized via server-side pagination; skip client-side
    }
});
</script>
@endpush
