@extends('community.layouts.app')
@section('title', 'Edit Profile')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header fw-bold"><i class="bi bi-gear me-2 text-primary"></i>Profile Settings</div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('community.profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <h6 class="fw-semibold mb-3 text-muted text-uppercase small">Basic Information</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Avatar</label>
                    <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($user->avatar)<small class="text-muted">Current avatar will be replaced.</small>@endif
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="3" maxlength="500">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location', $user->location) }}" placeholder="e.g. Nairobi, Kenya">
                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                           value="{{ old('website', $user->website) }}" placeholder="https://...">
                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <h6 class="fw-semibold mb-3 text-muted text-uppercase small">Change Password <small class="text-muted fw-normal">(leave blank to keep current)</small></h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror">
                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="form-control">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
                <a href="{{ route('community.profile.show', $user->id) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
