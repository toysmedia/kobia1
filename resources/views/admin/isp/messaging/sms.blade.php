@extends('admin.layouts.app')
@section('title', 'SMS')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">SMS</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    {{-- Single SMS --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-message me-1"></i> Send Single SMS</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.sms.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+2547XXXXXXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <small class="text-muted">(max 160 chars)</small></label>
                        <textarea name="message" class="form-control" rows="4" maxlength="160" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i> Send SMS</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk SMS --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-broadcast me-1"></i> Bulk SMS</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.sms.bulk') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <select name="recipients" class="form-select" id="recipientSelect" onchange="toggleCustom(this.value)">
                            <option value="all">All Subscribers</option>
                            <option value="all_pppoe">All PPPoE</option>
                            <option value="all_hotspot">All Hotspot</option>
                            <option value="active">Active Only</option>
                            <option value="expired">Expired Only</option>
                            <option value="custom">Custom List</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customPhonesDiv" style="display:none">
                        <label class="form-label">Phone Numbers (one per line or comma-separated)</label>
                        <textarea name="custom_phones" class="form-control" rows="3" placeholder="+2547XXXXXXXX"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <small class="text-muted">(max 160 chars)</small></label>
                        <textarea name="message" class="form-control" rows="4" maxlength="160" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-broadcast me-1"></i> Send Bulk SMS</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Recent SMS Logs --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Recent SMS</h6>
                <a href="{{ route('admin.isp.messaging.logs') }}?type=sms" class="btn btn-sm btn-outline-primary">View All Logs</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Recipient</th><th>Message</th><th>Status</th><th>Sent At</th></tr></thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->recipient }}</td>
                                <td>{{ Str::limit($log->message, 60) }}</td>
                                <td><span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ $log->status }}</span></td>
                                <td>{{ $log->sent_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">No SMS sent yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function toggleCustom(val) {
    document.getElementById('customPhonesDiv').style.display = val === 'custom' ? '' : 'none';
}
</script>
@endpush
