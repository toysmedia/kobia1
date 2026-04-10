@extends('admin.layouts.app')
@section('title', 'Create Role')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5>Create Role</h5>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.isp.access.roles.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Role Name (slug) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. manager" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" value="{{ old('display_name') }}" placeholder="e.g. Manager" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        </div>
                    </div>

                    <h6 class="mb-3">Permissions</h6>
                    @foreach($permissions as $group => $perms)
                    <div class="card mb-3 border">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <strong>{{ ucfirst(str_replace('_', ' ', $group)) }}</strong>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleGroup('{{ $group }}')">Select All</button>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                @foreach($perms as $perm)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input perm-{{ $group }}" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm_{{ $perm->id }}">{{ $perm->display_name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary">Save Role</button>
                    <a href="{{ route('admin.isp.access.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function toggleGroup(group) {
    var checkboxes = document.querySelectorAll('.perm-' + group);
    var allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
}
</script>
@endpush
