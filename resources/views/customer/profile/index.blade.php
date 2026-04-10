@extends('customer.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-user text-primary me-2"></i>My Profile</h4>
    </div>
</div>

<div class="row g-4">
    {{-- Edit profile form --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-1"></i>Personal Information</h6>
            </div>
            <div class="card-body p-4">
                @if($errors->hasBag('default') || $errors->has('name') || $errors->has('email'))
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $subscriber->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $subscriber->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $subscriber->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Your address (optional)">{{ old('address', $subscriber->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">PPPoE Username</label>
                        <input type="text" value="{{ $subscriber->username }}" class="form-control bg-body-secondary" readonly>
                        <div class="form-text">Contact support to change your username.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Change password --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-lock me-1"></i>Change Password</h6>
            </div>
            <div class="card-body p-4">
                @if($errors->has('current_password'))
                <div class="alert alert-danger py-2 small">{{ $errors->first('current_password') }}</div>
                @endif

                <form method="POST" action="{{ route('customer.profile.password') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               placeholder="Current password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimum 8 characters" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Repeat new password" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-key me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Data usage summary --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-bar me-1 text-info"></i>Data Usage</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="text-muted small">Download</div>
                        <div class="fw-bold text-info">{{ $usage['download_formatted'] }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Upload</div>
                        <div class="fw-bold text-success">{{ $usage['upload_formatted'] }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold">{{ $usage['total_formatted'] }}</div>
                    </div>
                </div>
                <div class="text-muted small text-center mt-2">
                    {{ $usage['session_count'] }} session(s) recorded
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent session history --}}
@if($recentSessions->isNotEmpty())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-history me-1"></i>Recent Session History</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr><th>Start</th><th>End</th><th>Download</th><th>Upload</th><th>IP Address</th><th>NAS</th></tr>
            </thead>
            <tbody>
                @foreach($recentSessions as $session)
                <tr>
                    <td>{{ $session->acctstarttime?->format('d M Y H:i') ?? '—' }}</td>
                    <td>
                        @if($session->acctstoptime)
                            {{ $session->acctstoptime->format('d M Y H:i') }}
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td>{{ app(\App\Services\Customer\DataUsageService::class)->formatBytes($session->acctoutputoctets ?? 0) }}</td>
                    <td>{{ app(\App\Services\Customer\DataUsageService::class)->formatBytes($session->acctinputoctets ?? 0) }}</td>
                    <td><code>{{ $session->framedipaddress ?? '—' }}</code></td>
                    <td><code>{{ $session->nasipaddress ?? '—' }}</code></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
