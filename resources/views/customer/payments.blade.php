@extends('layouts.app')
@section('title', 'Payment History')
@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h4 class="fw-bold">💰 Payment History</h4>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Receipt</th><th>Amount</th><th>Package</th><th>Type</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    <tr>
                        <td><code>{{ $p->mpesa_receipt_number ?? '-' }}</code></td>
                        <td>KES {{ number_format($p->amount, 0) }}</td>
                        <td>{{ $p->package?->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-secondary text-capitalize">{{ $p->connection_type }}</span></td>
                        <td><span class="badge {{ $p->status === 'completed' ? 'bg-success' : ($p->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($p->status) }}</span></td>
                        <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
