@extends('layouts.app')

@section('title', 'Buy Internet Package')

@push('styles')
<style>
.package-card { border-radius: 16px; transition: transform .2s, box-shadow .2s; }
.package-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.15); }
.price-tag { font-size: 2rem; font-weight: 800; color: #6d28d9; }
.speed-badge { background: #f3f0ff; color: #6d28d9; border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="fw-bold mb-2">🌐 Buy Internet Package</h4>
        <p class="text-muted">Pay with M-Pesa and connect instantly. Hotspot users get immediate access.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- STK Push Result -->
<div id="stk-result" class="alert" style="display:none;"></div>

<div class="row g-4 mb-4">
@forelse($packages as $package)
<div class="col-md-4 col-sm-6">
    <div class="card package-card h-100 shadow-sm">
        <div class="card-body text-center p-4">
            <h5 class="fw-bold mb-1">{{ $package->name }}</h5>
            <div class="mb-2">
                <span class="speed-badge">⬇ {{ $package->speed_download }}Mbps / ⬆ {{ $package->speed_upload }}Mbps</span>
            </div>
            <div class="price-tag">KES {{ number_format($package->price, 0) }}</div>
            <div class="text-muted small mb-3">
                @if($package->validity_days > 0) {{ $package->validity_days }} day(s) @endif
                @if($package->validity_hours > 0) {{ $package->validity_hours }} hour(s) @endif
                validity
            </div>
            @if($package->description)
            <p class="small text-muted mb-3">{{ $package->description }}</p>
            @endif
            <span class="badge {{ $package->type === 'hotspot' ? 'bg-info' : ($package->type === 'pppoe' ? 'bg-primary' : 'bg-success') }} mb-3 text-capitalize">
                {{ $package->type }}
            </span>

            <!-- Buy form -->
            <form class="buy-form" data-package="{{ $package->id }}" data-amount="{{ $package->price }}" data-name="{{ $package->name }}" data-type="{{ $package->type }}">
                <div class="input-group mb-2">
                    <span class="input-group-text">🇰🇪</span>
                    <input type="tel" name="phone" class="form-control" placeholder="0712345678" required maxlength="13">
                </div>
                <select name="connection_type" class="form-select mb-2">
                    @if(in_array($package->type, ['hotspot','both']))<option value="hotspot">Hotspot (M-Pesa code as voucher)</option>@endif
                    @if(in_array($package->type, ['pppoe','both']))<option value="pppoe">PPPoE (extend subscription)</option>@endif
                </select>
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    💳 Pay KES {{ number_format($package->price, 0) }} via M-Pesa
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="alert alert-info">No active packages available at the moment.</div>
</div>
@endforelse
</div>

<!-- Paybill Info -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-2">📱 Manual M-Pesa Payment (Paybill)</h6>
        <div class="row text-center">
            <div class="col-4">
                <div class="small text-muted">Business No.</div>
                <div class="fw-bold fs-5">{{ config('mpesa.shortcode', 'N/A') }}</div>
            </div>
            <div class="col-4">
                <div class="small text-muted">Account No.</div>
                <div class="fw-bold fs-5">Your Phone</div>
            </div>
            <div class="col-4">
                <div class="small text-muted">After payment, use</div>
                <div class="fw-bold">M-Pesa receipt as WiFi code</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.buy-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var phone = form.querySelector('[name="phone"]').value;
        var type = form.querySelector('[name="connection_type"]').value;
        var packageId = form.dataset.package;

        btn.disabled = true;
        btn.textContent = '⏳ Processing...';

        fetch('{{ url("/api/mpesa/stk-push") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                phone: phone,
                package_id: packageId,
                connection_type: type
            })
        })
        .then(r => r.json())
        .then(function(data) {
            var el = document.getElementById('stk-result');
            el.style.display = 'block';
            if (data.success) {
                el.className = 'alert alert-success';
                el.innerHTML = '✅ ' + data.message + '<br><small>Check your phone for M-Pesa prompt.</small>';
                if (data.checkout_request_id) {
                    pollPayment(data.checkout_request_id, btn, form.dataset.name);
                }
            } else {
                el.className = 'alert alert-danger';
                el.textContent = '❌ ' + data.message;
                btn.disabled = false;
                btn.textContent = '💳 Pay KES ' + parseFloat(form.dataset.amount).toLocaleString() + ' via M-Pesa';
            }
        })
        .catch(function() {
            var el = document.getElementById('stk-result');
            el.style.display = 'block';
            el.className = 'alert alert-danger';
            el.textContent = '❌ Network error. Please try again.';
            btn.disabled = false;
            btn.textContent = '💳 Retry';
        });
    });
});

function pollPayment(checkoutId, btn, packageName) {
    var attempts = 0;
    var interval = setInterval(function() {
        attempts++;
        if (attempts > 24) { clearInterval(interval); btn.disabled = false; return; }
        fetch('{{ url("/api/check-payment") }}/' + checkoutId)
            .then(r => r.json())
            .then(function(data) {
                if (data.status === 'completed') {
                    clearInterval(interval);
                    var el = document.getElementById('stk-result');
                    el.className = 'alert alert-success';
                    if (data.voucher) {
                        el.innerHTML = '🎉 <strong>Payment successful!</strong><br>Your WiFi code: <code style="font-size:1.2rem;font-weight:bold;">' + data.voucher + '</code><br>Enter this at the WiFi login page.';
                    } else {
                        el.innerHTML = '🎉 <strong>Payment successful!</strong><br>' + packageName + ' activated on your account.';
                    }
                    btn.textContent = '✅ Paid!';
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    var el = document.getElementById('stk-result');
                    el.className = 'alert alert-danger';
                    el.textContent = '❌ Payment failed. Please try again.';
                    btn.disabled = false;
                    btn.textContent = '💳 Retry';
                }
            });
    }, 5000);
}
</script>
@endpush
