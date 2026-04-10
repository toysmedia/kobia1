@extends('admin.layouts.app')
@section('title', 'Edit Category')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5>Edit Expense Category</h5>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.isp.expense_categories.update', $expenseCategory) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $expenseCategory->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $expenseCategory->description) }}">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ $expenseCategory->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.isp.expense_categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
