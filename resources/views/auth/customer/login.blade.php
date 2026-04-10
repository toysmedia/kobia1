<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Login | {{ config('settings.system_general.title', 'OxNet') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eqjn9gIos1qrSA8oXBFjO5A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="bg-body-secondary">

<div class="container" style="max-width:420px">
    <div class="text-center mb-4">
        <i class="fa-solid fa-network-wired fa-2x text-primary"></i>
        <h4 class="mt-2 fw-bold">{{ config('settings.system_general.title', 'OxNet') }}</h4>
        <p class="text-muted small">Customer Portal Login</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('customer.login.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" value="{{ old('username') }}"
                               class="form-control @error('username') is-invalid @enderror"
                               placeholder="Your PPPoE username" autofocus required>
                    </div>
                    @error('username')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Password" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePwd">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('customer.password.request') }}" class="small">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-right-to-bracket me-1"></i>Login
                </button>
            </form>
        </div>
    </div>

    <div class="text-center mt-3 small">
        Don't have an account?
        <a href="{{ route('customer.register') }}">Register here</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFnpSPPQEJ6W5vMz6Mi4ZKDyJR"
        crossorigin="anonymous"></script>
<script>
    document.getElementById('togglePwd').addEventListener('click', function () {
        const pwd  = document.querySelector('input[name=password]');
        const icon = document.getElementById('eyeIcon');
        if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
        else { pwd.type = 'password'; icon.className = 'fa-solid fa-eye'; }
    });
</script>
</body>
</html>
