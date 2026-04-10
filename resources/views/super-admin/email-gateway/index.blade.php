@extends('layouts.super-admin')
@section('title', 'Email Gateway')
@section('page-title', 'Email Gateway')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- SMTP Configuration --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-envelope-gear me-2 text-primary"></i>SMTP Configuration</h6>
            </div>
            <div class="card-body">
                @php $config = $config ?? []; @endphp
                <form method="POST" action="{{ route('super-admin.email-gateway.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mail Driver <span class="text-danger">*</span></label>
                            <select name="driver" class="form-select" required>
                                <option value="smtp" @selected(($config['driver'] ?? 'smtp') === 'smtp')>SMTP</option>
                                <option value="mailgun" @selected(($config['driver'] ?? '') === 'mailgun')>Mailgun</option>
                                <option value="ses" @selected(($config['driver'] ?? '') === 'ses')>Amazon SES</option>
                                <option value="postmark" @selected(($config['driver'] ?? '') === 'postmark')>Postmark</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="host" value="{{ $config['host'] ?? '' }}" class="form-control" placeholder="smtp.mailtrap.io">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="port" value="{{ $config['port'] ?? 587 }}" class="form-control" placeholder="587">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" value="{{ $config['username'] ?? '' }}" class="form-control" placeholder="SMTP username">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" value="{{ $config['password'] ?? '' }}" class="form-control" placeholder="SMTP password">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Encryption</label>
                            <select name="encryption" class="form-select">
                                <option value="tls" @selected(($config['encryption'] ?? 'tls') === 'tls')>TLS</option>
                                <option value="ssl" @selected(($config['encryption'] ?? '') === 'ssl')>SSL</option>
                                <option value="null" @selected(($config['encryption'] ?? '') === 'null')>None</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">From Address</label>
                            <input type="email" name="from_address" value="{{ $config['from_address'] ?? '' }}" class="form-control" placeholder="no-reply@oxnet.co.ke">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" value="{{ $config['from_name'] ?? 'OxNet Platform' }}" class="form-control" placeholder="OxNet Platform">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Test Email + Status --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-send me-2 text-success"></i>Send Test Email</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.email-gateway.test') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Send To</label>
                        <input type="email" name="to" class="form-control" placeholder="test@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-envelope-arrow-up me-1"></i>Send Test Email</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary"><i class="bi bi-envelope fs-5"></i></div>
                    <div>
                        <div class="small text-muted">Current Driver</div>
                        <div class="fw-semibold">{{ strtoupper($config['driver'] ?? 'Not configured') }}</div>
                    </div>
                    @if(!empty($config['host']) || !empty($config['driver']))
                        <span class="badge bg-success ms-auto">Configured</span>
                    @else
                        <span class="badge bg-secondary ms-auto">Not Set</span>
                    @endif
                </div>
                @if(!empty($config['from_address']))
                <hr class="my-2">
                <div class="small text-muted">From: <strong>{{ $config['from_name'] ?? '' }} &lt;{{ $config['from_address'] }}&gt;</strong></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
