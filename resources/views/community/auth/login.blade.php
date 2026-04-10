@extends('community.layouts.app')
@section('title', 'Login')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-people-fill text-primary fs-1"></i>
                    <h4 class="fw-bold mt-2">Welcome back</h4>
                    <p class="text-muted">Sign in to OxNet Community</p>
                </div>
                <form method="POST" action="{{ route('community.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="{{ route('community.password.request') }}" class="text-primary small">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Sign In</button>
                </form>
                <hr class="my-4">
                <p class="text-center text-muted mb-0">
                    Don't have an account? <a href="{{ route('community.register') }}" class="text-primary fw-semibold">Join Community</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
