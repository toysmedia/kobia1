@extends('admin.layouts.app')
@section('title', 'Expense Categories')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Expense Categories</h5>
    </div>

    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">All Categories</h6>
                <a href="{{ route('admin.isp.expense_categories.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus me-1"></i> New Category</a>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>#</th><th>Name</th><th>Description</th><th>Expenses</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td>{{ $cat->id }}</td>
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->description ?? '-' }}</td>
                            <td>{{ $cat->expenses_count }}</td>
                            <td><span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.isp.expense_categories.edit', $cat) }}" class="btn btn-xs btn-outline-warning"><i class="bx bx-edit"></i></a>
                                <form action="{{ route('admin.isp.expense_categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No categories found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
