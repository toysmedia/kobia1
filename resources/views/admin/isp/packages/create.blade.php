@extends('admin.layouts.app')
@section('title', 'Create Package')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Create ISP Package</h5>
            </div>
            <a href="{{ route('admin.isp.packages.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.packages.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Package Details</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. Home 10Mbps Monthly" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="pppoe"   {{ old('type') == 'pppoe'   ? 'selected' : '' }}>PPPoE</option>
                                    <option value="hotspot" {{ old('type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                                    <option value="both"    {{ old('type') == 'both'    ? 'selected' : '' }}>Both (PPPoE & Hotspot)</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Price (KES) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" min="0" name="price"
                                           class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price') }}" placeholder="0.00" required>
                                </div>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Optional description">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Speed & Validity</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Upload Speed (Mbps) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.1" min="0" name="speed_upload"
                                           class="form-control @error('speed_upload') is-invalid @enderror"
                                           value="{{ old('speed_upload') }}" placeholder="e.g. 5" required>
                                    @error('speed_upload')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Download Speed (Mbps) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.1" min="0" name="speed_download"
                                           class="form-control @error('speed_download') is-invalid @enderror"
                                           value="{{ old('speed_download') }}" placeholder="e.g. 10" required>
                                    @error('speed_download')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Validity Days</label>
                                    <input type="number" min="0" name="validity_days"
                                           class="form-control @error('validity_days') is-invalid @enderror"
                                           value="{{ old('validity_days', 30) }}" placeholder="30">
                                    @error('validity_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Validity Hours</label>
                                    <input type="number" min="0" max="23" name="validity_hours"
                                           class="form-control @error('validity_hours') is-invalid @enderror"
                                           value="{{ old('validity_hours', 0) }}" placeholder="0">
                                    @error('validity_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Data Limit (MB)</label>
                                <input type="number" min="0" name="data_limit_mb"
                                       class="form-control @error('data_limit_mb') is-invalid @enderror"
                                       value="{{ old('data_limit_mb') }}" placeholder="Leave blank for unlimited">
                                <div class="form-text">1 GB = 1024 MB. Leave blank for unlimited data.</div>
                                @error('data_limit_mb')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Save Package
                    </button>
                    <a href="{{ route('admin.isp.packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
