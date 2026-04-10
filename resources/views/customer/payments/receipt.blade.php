@extends('customer.layouts.app')

@section('title', 'Payment Receipt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Payment Receipt</h4>
            <a href="{{ route('customer.payments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white text-center py-4">
                <i class="fa-solid fa-circle-check fa-3x mb-2"></i>
                <h5 class="mb-0">Payment Successful</h5>
            </div>
            <div class="card-body p-4">
                <table class="table table-borderless">
                    <tr><td class="text-muted">Receipt No.</td><td class="fw-bold"><code>{{ $payment->mpesa_receipt_number }}</code></td></tr>
                    <tr><td class="text-muted">Amount</td><td class="fw-bold text-success">KES {{ number_format($payment->amount, 0) }}</td></tr>
                    <tr><td class="text-muted">Package</td><td>{{ $payment->package?->name ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $payment->phone }}</td></tr>
                    <tr><td class="text-muted">Date</td><td>{{ $payment->created_at->format('d M Y H:i') }}</td></tr>
                    <tr><td class="text-muted">Account</td><td>{{ $subscriber->username }}</td></tr>
                </table>
            </div>
            <div class="card-footer bg-transparent text-center">
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
