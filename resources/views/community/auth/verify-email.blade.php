@extends('community.layouts.app')
@section('title', 'Verify Email')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-envelope-check text-primary fs-1 mb-3"></i>
                <h4 class="fw-bold">Verify your email</h4>
                <p class="text-muted">We've sent a verification link to your email address. Please check your inbox and click the link to activate your account.</p>
                <a href="{{ route('community.index') }}" class="btn btn-outline-primary mt-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Community
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
