@extends('admin.layouts.app')
@section('title', 'Expenses')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">All Expenses</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <form class="d-flex gap-2 align-items-center flex-wrap" method="GET">
                    <select name="category" class="form-select form-select-sm" style="width:auto">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" style="width:auto">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" style="width:auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('admin.isp.expenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </form>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.isp.expenses.export', request()->all()) }}" class="btn btn-sm btn-outline-success"><i class="bx bx-export me-1"></i> Export CSV</a>
                    <a href="{{ route('admin.isp.expenses.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus me-1"></i> Add Expense</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="expenseTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Amount (KES)</th>
                                <th>Category</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->id }}</td>
                                <td>{{ $expense->title }}</td>
                                <td class="fw-bold">KES {{ number_format($expense->amount, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $expense->category)) }}</span></td>
                                <td>{{ ucfirst($expense->payment_method) }}</td>
                                <td>{{ $expense->date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.isp.expenses.edit', $expense) }}" class="btn btn-xs btn-outline-warning"><i class="bx bx-edit"></i></a>
                                    <form action="{{ route('admin.isp.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No expenses found</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="2">Total</td>
                                <td>KES {{ number_format($expenses->sum('amount'), 2) }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{ $expenses->links() }}
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
    if ($('#expenseTable tbody tr').length > 1) {
        $('#expenseTable').DataTable({ paging: false, searching: false, info: false, order: [] });
    }
});
</script>
@endpush
