@extends('layouts.super-admin')
@section('title', 'Community - Categories')
@section('page-title', 'Community Categories')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-grid me-2 text-primary"></i>Manage Categories</h5>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg me-1"></i>Add Category</button>
        <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($cat->color)<span class="rounded" style="width:14px;height:14px;background:{{ $cat->color }};display:inline-block;"></span>@endif
                            @if($cat->icon)<i class="bi bi-{{ $cat->icon }} text-muted"></i>@endif
                            <span class="fw-semibold">{{ $cat->name }}</span>
                        </div>
                        @if($cat->description)<small class="text-muted">{{ Str::limit($cat->description, 60) }}</small>@endif
                    </td>
                    <td><code>{{ $cat->slug }}</code></td>
                    <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $cat->posts_count }}</span></td>
                    <td>{{ $cat->order }}</td>
                    <td>
                        <span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}-subtle text-{{ $cat->is_active ? 'success' : 'secondary' }}-emphasis">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCatModal{{ $cat->id }}" style="font-size:.75rem;"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('super-admin.community.categories.toggle', $cat->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $cat->is_active ? 'warning' : 'success' }}" style="font-size:.75rem;"><i class="bi bi-{{ $cat->is_active ? 'eye-slash' : 'eye' }}"></i></button>
                            </form>
                        </div>
                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editCatModal{{ $cat->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('super-admin.community.categories.update', $cat->id) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header"><h6 class="modal-title">Edit Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="row g-2">
                                                <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required></div>
                                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control form-control-sm" rows="2">{{ $cat->description }}</textarea></div>
                                                <div class="col-6"><label class="form-label">Icon (Bootstrap Icons)</label><input type="text" name="icon" class="form-control form-control-sm" value="{{ $cat->icon }}" placeholder="e.g. chat-dots"></div>
                                                <div class="col-6"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-sm form-control-color" value="{{ $cat->color ?? '#6c757d' }}"></div>
                                                <div class="col-6"><label class="form-label">Order</label><input type="number" name="order" class="form-control form-control-sm" value="{{ $cat->order }}" min="0"></div>
                                                <div class="col-6 d-flex align-items-end">
                                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No categories yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('super-admin.community.categories.store') }}">
                @csrf
                <div class="modal-header"><h6 class="modal-title fw-bold">Add Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-sm" required placeholder="Category name"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Optional description..."></textarea></div>
                        <div class="col-6"><label class="form-label">Icon</label><input type="text" name="icon" class="form-control form-control-sm" placeholder="e.g. chat-dots"></div>
                        <div class="col-6"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-sm form-control-color" value="#0d6efd"></div>
                        <div class="col-6"><label class="form-label">Parent</label>
                            <select name="parent_id" class="form-select form-select-sm">
                                <option value="">None (top-level)</option>
                                @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Order</label><input type="number" name="order" class="form-control form-control-sm" value="0" min="0"></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="isActiveNew"><label class="form-check-label" for="isActiveNew">Active</label></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
