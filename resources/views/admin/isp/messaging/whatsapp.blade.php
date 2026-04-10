@extends('admin.layouts.app')
@section('title', 'WhatsApp')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">WhatsApp</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bxl-whatsapp me-1 text-success"></i> Send WhatsApp Message</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.whatsapp.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+2547XXXXXXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bx bxl-whatsapp me-1"></i> Send WhatsApp</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bx bx-broadcast me-1"></i> Bulk WhatsApp</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.isp.messaging.whatsapp.bulk') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <select name="recipients" class="form-select" onchange="this.value==='custom'?document.getElementById('customWaDiv').style.display='':document.getElementById('customWaDiv').style.display='none'">
                            <option value="all">All Subscribers</option>
                            <option value="all_pppoe">All PPPoE</option>
                            <option value="all_hotspot">All Hotspot</option>
                            <option value="active">Active Only</option>
                            <option value="expired">Expired Only</option>
                            <option value="custom">Custom List</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customWaDiv" style="display:none">
                        <label class="form-label">Phone Numbers (one per line)</label>
                        <textarea name="custom_phones" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bx bx-broadcast me-1"></i> Send Bulk WhatsApp</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Recent WhatsApp Messages</h6>
                <a href="{{ route('admin.isp.messaging.logs') }}?type=whatsapp" class="btn btn-sm btn-outline-success">View All Logs</a>
            </div>
            <div class="card-body">
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
                        <tr><td colspan="4" class="text-center text-muted">No WhatsApp messages sent yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
