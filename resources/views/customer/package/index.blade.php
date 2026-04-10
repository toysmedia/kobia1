@extends('customer.layouts.app')

@section('title', 'My Package')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-box text-primary me-2"></i>My Package</h4>
</div>

{{-- Current package --}}
@if($currentPackage)
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold mb-1">{{ $currentPackage->name }}
                    <span class="badge bg-primary ms-2">Current</span>
                </h5>
                <div class="row g-3 mt-1">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Download</div>
                        <div class="fw-semibold"><i class="fa-solid fa-arrow-down text-info me-1"></i>{{ $currentPackage->speed_download }}Mbps</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Upload</div>
                        <div class="fw-semibold"><i class="fa-solid fa-arrow-up text-success me-1"></i>{{ $currentPackage->speed_upload }}Mbps</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Validity</div>
                        <div class="fw-semibold">
                            @if($currentPackage->validity_days) {{ $currentPackage->validity_days }}d @endif
                            @if($currentPackage->validity_hours) {{ $currentPackage->validity_hours }}h @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Price</div>
                        <div class="fw-semibold text-success">KES {{ number_format($currentPackage->price, 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('customer.payments.renew') }}" class="btn btn-primary">
                    <i class="fa-solid fa-rotate me-1"></i>Renew Now
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Available packages --}}
<h5 class="fw-bold mb-3">Available Packages</h5>
<div class="row g-3">
    @forelse($availablePackages as $package)
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 {{ (int)$subscriber->isp_package_id === (int)$package->id ? 'border border-primary' : '' }}">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold">{{ $package->name }}</span>
                @if((int)$subscriber->isp_package_id === (int)$package->id)
                <span class="badge bg-primary">Current</span>
                @endif
            </div>
            <div class="card-body">
                <div class="fs-4 fw-bold text-success mb-3">KES {{ number_format($package->price, 0) }}</div>
                <ul class="list-unstyled small mb-3">
                    <li class="mb-1"><i class="fa-solid fa-arrow-down text-info me-2"></i>Download: <strong>{{ $package->speed_download }}Mbps</strong></li>
                    <li class="mb-1"><i class="fa-solid fa-arrow-up text-success me-2"></i>Upload: <strong>{{ $package->speed_upload }}Mbps</strong></li>
                    <li class="mb-1"><i class="fa-solid fa-calendar-days text-warning me-2"></i>Valid for:
                        <strong>
                            @if($package->validity_days) {{ $package->validity_days }} day(s) @endif
                            @if($package->validity_hours) {{ $package->validity_hours }} hour(s) @endif
                        </strong>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-transparent">
                @if((int)$subscriber->isp_package_id === (int)$package->id)
                    <a href="{{ route('customer.payments.renew') }}?package_id={{ $package->id }}"
                       class="btn btn-sm btn-primary w-100">
                        <i class="fa-solid fa-rotate me-1"></i>Renew
                    </a>
                @else
                    <form method="POST" action="{{ route('customer.package.change') }}">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fa-solid fa-arrow-right-arrow-left me-1"></i>
                            {{ $package->price > ($currentPackage?->price ?? 0) ? 'Upgrade' : 'Change' }} to this
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">No packages available at this time.</div>
    </div>
    @endforelse
</div>
@endsection
