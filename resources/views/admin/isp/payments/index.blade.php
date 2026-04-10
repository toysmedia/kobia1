@extends('admin.layouts.app')
@section('title', 'Payments')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Payments</h5>
            </div>
            <a href="{{ route('admin.isp.payments.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success">
                <i class="bx bx-download me-1"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="col-sm-12 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Filter Payments</h6>
                <div>
                    <button type="submit" form="filterForm" class="btn btn-sm btn-primary">Search</button>
                    <a href="{{ route('admin.isp.payments.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Clear</a>
                </div>
            </div>
            <div class="card-body pb-2">
                <form id="filterForm" method="GET" action="{{ route('admin.isp.payments.index') }}">
                    <div class="row">
                        <div class="col-sm-3 mb-3">
                            <div class="input-group">
                                <span class="input-group-text">From</span>
                                <input type="date" name="from_date" class="form-control"
                                       value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-sm-3 mb-3">
                            <div class="input-group">
                                <span class="input-group-text">To</span>
                                <input type="date" name="to_date" class="form-control"
                                       value="{{ request('to_date', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                                <option value="failed"    {{ request('status') == 'failed'    ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <select name="payment_method" class="form-select">
                                <option value="">All Methods</option>
                                <option value="mpesa"  {{ request('payment_method') == 'mpesa'  ? 'selected' : '' }}>M-Pesa</option>
                                <option value="cash"   {{ request('payment_method') == 'cash'   ? 'selected' : '' }}>Cash</option>
                                <option value="bank"   {{ request('payment_method') == 'bank'   ? 'selected' : '' }}>Bank</option>
                                <option value="manual" {{ request('payment_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div class="col-sm-2 mb-3">
                            <input type="text" name="q" class="form-control" placeholder="Username / Phone / Ref"
                                   value="{{ request('q') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 mb-3">
                            <select name="package_id" class="form-select">
                                <option value="">All Packages</option>
                                @foreach($packages ?? [] as $pkg)
                                    <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 mb-3">
                            <select name="router_id" class="form-select">
                                <option value="">All Routers</option>
                                @foreach($routers ?? [] as $router)
                                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>{{ $router->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    @if(isset($summary))
    <div class="col-sm-12 mb-3">
        <div class="row g-3">
            <div class="col-sm-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <p class="text-muted mb-1 small">Total Collected</p>
                        <h5 class="mb-0 text-success">KES {{ number_format($summary['total'] ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <p class="text-muted mb-1 small">Completed</p>
                        <h5 class="mb-0 text-primary">{{ $summary['completed_count'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <p class="text-muted mb-1 small">Pending</p>
                        <h5 class="mb-0 text-warning">{{ $summary['pending_count'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <p class="text-muted mb-1 small">Failed</p>
                        <h5 class="mb-0 text-danger">{{ $summary['failed_count'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Payments Table --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Subscriber</th>
                                <th>Phone</th>
                                <th>Package</th>
                                <th>Amount (KES)</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($payment->subscriber)
                                        <a href="{{ route('admin.isp.subscribers.show', $payment->subscriber_id) }}">
                                            {{ $payment->subscriber->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $payment->subscriber->phone ?? '-' }}</td>
                                <td>{{ $payment->package->name ?? '-' }}</td>
                                <td><strong>{{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-label-{{ $payment->payment_method === 'mpesa' ? 'success' : 'secondary' }}">
                                        {{ strtoupper($payment->payment_method ?? 'N/A') }}
                                    </span>
                                </td>
                                <td><code>{{ $payment->transaction_id ?? '-' }}</code></td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-label-success">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-label-warning">Pending</span>
                                    @else
                                        <span class="badge bg-label-danger">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No payment records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $payments->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
