@extends('admin.layouts.app')
@section('title', 'Email')
@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Email</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-envelope me-1"></i> Send Email</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.email.send') }}" method="POST" id="emailForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">To (Email Address)</label>
                        <input type="email" name="to" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body</label>
                        <div id="emailEditor" style="height:200px"></div>
                        <input type="hidden" name="body" id="emailBody">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i> Send Email</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-broadcast me-1"></i> Bulk Email</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.email.bulk') }}" method="POST" id="bulkEmailForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <select name="recipients" class="form-select" onchange="this.value==='custom'?document.getElementById('customEmailDiv').style.display='':document.getElementById('customEmailDiv').style.display='none'">
                            <option value="all">All Subscribers</option>
                            <option value="all_pppoe">All PPPoE</option>
                            <option value="all_hotspot">All Hotspot</option>
                            <option value="active">Active Only</option>
                            <option value="expired">Expired Only</option>
                            <option value="custom">Custom List</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customEmailDiv" style="display:none">
                        <label class="form-label">Email Addresses (one per line)</label>
                        <textarea name="custom_emails" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body</label>
                        <div id="bulkEmailEditor" style="height:150px"></div>
                        <input type="hidden" name="body" id="bulkEmailBody">
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-broadcast me-1"></i> Send Bulk Email</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Recent Emails</h6>
                <a href="{{ route('admin.isp.messaging.logs') }}?type=email" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>To</th><th>Subject</th><th>Status</th><th>Sent At</th></tr></thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ Str::limit($log->subject ?? '-', 40) }}</td>
                            <td><span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
                            <td>{{ $log->sent_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No emails sent yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
var quill1 = new Quill('#emailEditor', {theme: 'snow'});
var quill2 = new Quill('#bulkEmailEditor', {theme: 'snow'});
document.getElementById('emailForm').addEventListener('submit', function() {
    document.getElementById('emailBody').value = quill1.root.innerHTML;
});
document.getElementById('bulkEmailForm').addEventListener('submit', function() {
    document.getElementById('bulkEmailBody').value = quill2.root.innerHTML;
});
</script>
@endpush
