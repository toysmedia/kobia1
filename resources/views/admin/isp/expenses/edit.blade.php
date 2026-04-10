@extends('admin.layouts.app')
@section('title', 'Edit Expense')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Edit Expense</h5>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.isp.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount (KES) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                @foreach(['utilities', 'salaries', 'equipment', 'maintenance', 'internet_bandwidth', 'rent', 'transport', 'other'] as $cat)
                                <option value="{{ $cat }}" {{ (old('category', $expense->category)) === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Custom Category</label>
                            <select name="expense_category_id" class="form-select">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['cash', 'mpesa', 'bank'] as $pm)
                                <option value="{{ $pm }}" {{ old('payment_method', $expense->payment_method) === $pm ? 'selected' : '' }}>{{ ucfirst($pm) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference', $expense->reference) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $expense->date->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $expense->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Receipt Image</label>
                            @if($expense->receipt_image)
                            <div class="mb-2"><img src="{{ asset('storage/' . $expense->receipt_image) }}" style="max-height:80px" class="img-thumbnail"></div>
                            @endif
                            <input type="file" name="receipt_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
                            <a href="{{ route('admin.isp.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
