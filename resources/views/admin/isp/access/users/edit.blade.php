@extends('admin.layouts.app')
@section('title', 'Edit Worker')
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5>Edit Worker: {{ $worker->name }}</h5>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.isp.access.users.update', $worker) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $worker->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $worker->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $worker->mobile) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-12">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm New Password">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Roles</label>
                            <div class="row">
                                @foreach($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $workerRoleIds)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->display_name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Update Worker</button>
                            <a href="{{ route('admin.isp.access.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
