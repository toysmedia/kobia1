<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | {{ config('settings.system_general.title', 'OxNet') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eqjn9gIos1qrSA8oXBFjO5A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }</style>
</head>
<body class="bg-body-secondary">

<div class="container" style="max-width:480px">
    <div class="text-center mb-4">
        <i class="fa-solid fa-network-wired fa-2x text-primary"></i>
        <h4 class="mt-2 fw-bold">Create Account</h4>
        <p class="text-muted small">{{ config('settings.system_general.title', 'OxNet') }} Customer Portal</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('customer.register.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="John Doe" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="john@example.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="form-control @error('phone') is-invalid @enderror"
                           placeholder="0712345678" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimum 8 characters" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                           class="form-control" placeholder="Repeat password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-user-plus me-1"></i>Create Account
                </button>
            </form>
        </div>
    </div>

    <div class="text-center mt-3 small">
        Already have an account? <a href="{{ route('customer.login') }}">Login here</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFnpSPPQEJ6W5vMz6Mi4ZKDyJR"
        crossorigin="anonymous"></script>
</body>
</html>
