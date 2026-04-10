@extends('community.layouts.app')
@section('title', 'Forgot Password')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-lock text-primary fs-1"></i>
                    <h4 class="fw-bold mt-2">Forgot Password?</h4>
                    <p class="text-muted">Enter your email and we'll send a reset link.</p>
                </div>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('community.password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('community.login') }}" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
