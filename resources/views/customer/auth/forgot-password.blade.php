<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | {{ config('settings.system_general.title', 'OxNet') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eqjn9gIos1qrSA8oXBFjO5A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }</style>
</head>
<body class="bg-body-secondary">
<div class="container" style="max-width:420px">
    <div class="text-center mb-4">
        <i class="fa-solid fa-key fa-2x text-primary"></i>
        <h4 class="mt-2 fw-bold">Forgot Password</h4>
        <p class="text-muted small">Enter your email to receive a reset link.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('customer.password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="your@email.com" autofocus required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-paper-plane me-1"></i>Send Reset Link
                </button>
            </form>
        </div>
    </div>
    <div class="text-center mt-3 small">
        <a href="{{ route('customer.login') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to login</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFnpSPPQEJ6W5vMz6Mi4ZKDyJR"
        crossorigin="anonymous"></script>
</body>
</html>
