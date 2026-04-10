@extends('layouts.super-admin')
@section('title', 'SMS Gateway')
@section('page-title', 'SMS Gateway')

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
    {{-- Gateway Configuration --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-gear me-2 text-primary"></i>SMS Provider Configuration</h6>
            </div>
            <div class="card-body">
                @php $config = $config ?? []; @endphp
                <form method="POST" action="{{ route('super-admin.sms-gateway.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Active Provider <span class="text-danger">*</span></label>
                            <select name="provider" class="form-select" id="providerSelect">
                                <option value="advanta" @selected(($config['provider'] ?? '') === 'advanta')>Advanta SMS</option>
                                <option value="blessed" @selected(($config['provider'] ?? '') === 'blessed')>Blessed Africa</option>
                                <option value="africastalking" @selected(($config['provider'] ?? '') === 'africastalking')>Africa's Talking</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sender ID</label>
                            <input type="text" name="sender_id" value="{{ $config['sender_id'] ?? '' }}" class="form-control" placeholder="OxNet" maxlength="11">
                            <div class="form-text">Max 11 characters</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">API Key / Username</label>
                            <input type="text" name="api_key" value="{{ $config['api_key'] ?? '' }}" class="form-control" placeholder="Your API key or username">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">API Secret / Password</label>
                            <input type="password" name="api_secret" value="{{ $config['api_secret'] ?? '' }}" class="form-control" placeholder="Your API secret">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Base URL (optional)</label>
                            <input type="url" name="base_url" value="{{ $config['base_url'] ?? '' }}" class="form-control" placeholder="https://api.example.com">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Test SMS --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-send me-2 text-success"></i>Send Test SMS</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.sms-gateway.test') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+254712345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="3" class="form-control" maxlength="160" placeholder="Test message from OxNet…" required></textarea>
                        <div class="form-text">Max 160 characters</div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-send me-1"></i>Send Test</button>
                </form>
            </div>
        </div>

        {{-- Current Provider Badge --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary"><i class="bi bi-chat-dots fs-5"></i></div>
                    <div>
                        <div class="small text-muted">Active Provider</div>
                        <div class="fw-semibold">{{ ucfirst($config['provider'] ?? 'Not configured') }}</div>
                    </div>
                    @if(!empty($config['provider']))
                        <span class="badge bg-success ms-auto">Configured</span>
                    @else
                        <span class="badge bg-secondary ms-auto">Not Set</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SMS Campaign --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-megaphone me-2 text-warning"></i>SMS Campaign</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.sms-gateway.campaign') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Target Audience</label>
                            <select name="target" class="form-select" required>
                                <option value="all">All Active Tenants ({{ $tenants->count() }})</option>
                                <option value="expiring_7">Expiring in 7 Days</option>
                                <option value="expiring_3">Expiring in 3 Days</option>
                                <option value="expired">Expired Tenants</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Campaign Message</label>
                            <textarea name="message" rows="2" class="form-control" maxlength="160" placeholder="Your OxNet subscription expires soon. Renew now to avoid service interruption." required></textarea>
                            <div class="form-text">Max 160 characters per SMS</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Send this SMS campaign? This will send real SMS messages.')">
                            <i class="bi bi-megaphone me-1"></i>Send Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
