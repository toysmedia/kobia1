@extends('admin.layouts.app')
@section('title', 'Edit Reseller')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Edit Reseller: {{ $reseller->name }}</h5>
            </div>
            <a href="{{ route('admin.isp.resellers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.resellers.update', $reseller) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Personal & Contact Details</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $reseller->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $reseller->phone) }}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $reseller->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Location / Area</label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location', $reseller->location) }}">
                                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $reseller->notes) }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Account & Pricing</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $reseller->username) }}" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <input type="text" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Leave blank to keep current password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="genPassword()">Generate</button>
                                </div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Discount (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_percent"
                                       class="form-control @error('discount_percent') is-invalid @enderror"
                                       value="{{ old('discount_percent', $reseller->discount_percent ?? 0) }}">
                                @error('discount_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Credit Limit (KES)</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" min="0" name="credit_limit"
                                           class="form-control @error('credit_limit') is-invalid @enderror"
                                           value="{{ old('credit_limit', $reseller->credit_limit ?? 0) }}">
                                </div>
                                @error('credit_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Assigned Routers</label>
                                <select name="router_ids[]" class="form-select @error('router_ids') is-invalid @enderror" multiple>
                                    @foreach($routers ?? [] as $router)
                                        <option value="{{ $router->id }}"
                                            {{ in_array($router->id, old('router_ids', $reseller->routers->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                            {{ $router->name }} ({{ $router->wan_ip }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple routers.</div>
                                @error('router_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', $reseller->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Update Reseller
                    </button>
                    <a href="{{ route('admin.isp.resellers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function genPassword() {
    const chars = 'abcdefghijkmnpqrstuvwxyz23456789';
    let pass = '';
    for (let i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('password').value = pass;
}
</script>
@endpush
