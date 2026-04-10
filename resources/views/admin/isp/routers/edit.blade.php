@extends('admin.layouts.app')
@section('title', 'Edit Router')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Edit Router: {{ $router->name }}</h5>
            </div>
            <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.routers.update', $router) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                {{-- Basic Info --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Basic Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Router Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $router->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">WAN IP Address</label>
                                <input type="text" name="wan_ip" class="form-control @error('wan_ip') is-invalid @enderror"
                                       value="{{ old('wan_ip', $router->wan_ip) }}" placeholder="e.g. 41.215.10.5 (optional)">
                                <div class="form-text">Public/WAN IP used for FreeRADIUS NAS registration and Winbox/Web links.</div>
                                @error('wan_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">VPN IP / Management IP</label>
                                <input type="text" name="vpn_ip" class="form-control @error('vpn_ip') is-invalid @enderror"
                                       value="{{ old('vpn_ip', $router->vpn_ip) }}" placeholder="e.g. 10.0.0.1 (optional)">
                                <div class="form-text">IP tunnel/VPN address for API connectivity. Takes priority over WAN IP for API calls.</div>
                                @error('vpn_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">RADIUS Secret <span class="text-danger">*</span></label>
                                <input type="text" name="radius_secret" class="form-control @error('radius_secret') is-invalid @enderror"
                                       value="{{ old('radius_secret', $router->radius_secret) }}" required>
                                @error('radius_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Billing Domain</label>
                                <input type="text" name="billing_domain" class="form-control @error('billing_domain') is-invalid @enderror"
                                       value="{{ old('billing_domain', $router->billing_domain) }}">
                                @error('billing_domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', $router->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Network Config --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Network Configuration</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">WAN Interface</label>
                                <input type="text" name="wan_interface" class="form-control @error('wan_interface') is-invalid @enderror"
                                       value="{{ old('wan_interface', $router->wan_interface) }}">
                                @error('wan_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Interface</label>
                                <input type="text" name="customer_interface" class="form-control @error('customer_interface') is-invalid @enderror"
                                       value="{{ old('customer_interface', $router->customer_interface) }}">
                                @error('customer_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PPPoE Pool Range</label>
                                <input type="text" name="pppoe_pool_range" class="form-control @error('pppoe_pool_range') is-invalid @enderror"
                                       value="{{ old('pppoe_pool_range', $router->pppoe_pool_range) }}">
                                @error('pppoe_pool_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hotspot Pool Range</label>
                                <input type="text" name="hotspot_pool_range" class="form-control @error('hotspot_pool_range') is-invalid @enderror"
                                       value="{{ old('hotspot_pool_range', $router->hotspot_pool_range) }}">
                                @error('hotspot_pool_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="col-sm-12 mb-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Notes</h6></div>
                        <div class="card-body">
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $router->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Update Router
                    </button>
                    <a href="{{ route('admin.isp.routers.show', $router) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection