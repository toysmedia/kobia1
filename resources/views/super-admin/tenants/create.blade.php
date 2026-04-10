@extends('layouts.super-admin')
@section('title', 'Create Tenant')
@section('page-title', 'Create Tenant')

@section('content')
{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.tenants.index') }}">Tenants</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2 text-primary"></i>New Tenant</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('super-admin.tenants.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ISP / Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Kenya Broadband Ltd" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="admin@example.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subdomain <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control @error('subdomain') is-invalid @enderror" placeholder="kenya-broadband" required>
                                <span class="input-group-text text-muted">.{{ config('app.domain', 'oxnet.co.ke') }}</span>
                            </div>
                            @error('subdomain')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Letters, numbers, hyphens only. No spaces.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pricing Plan</label>
                            <select name="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                <option value="">— No Plan —</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                        {{ $plan->name }} (KES {{ number_format($plan->price, 0) }}/{{ $plan->billing_cycle }})
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="trial" @selected(old('status','trial') === 'trial')>Trial</option>
                                <option value="active" @selected(old('status') === 'active')>Active</option>
                                <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                                <option value="expired" @selected(old('status') === 'expired')>Expired</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
