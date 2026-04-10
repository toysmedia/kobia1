@extends('customer.layouts.app')

@section('title', 'Renew Subscription')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h4 class="fw-bold mb-4"><i class="fa-solid fa-rotate text-primary me-2"></i>Renew Subscription</h4>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" id="success-banner">
            <i class="fa-solid fa-check-circle me-1"></i>
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        @if(session('checkout_request_id'))
        {{-- Payment status polling --}}
        <div class="card border-0 shadow-sm mb-4" id="payment-status-card"
             data-status-url="{{ route('api.customer.payment.status', '__id__') }}"
             data-checkout-id="{{ session('checkout_request_id') }}">
            <div class="card-body text-center py-4">
                <div class="spinner-border text-primary mb-3" id="payment-spinner"></div>
                <h6 id="payment-status-text">Waiting for payment confirmation...</h6>
                <p class="text-muted small">Please enter your M-Pesa PIN on your phone.</p>
                <div class="progress mt-2" style="height:4px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progress-bar" style="width:0%"></div>
                </div>
            </div>
        </div>
        @endif
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-circle-xmark me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('customer.payments.process') }}" id="renewal-form">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Package</label>
                        <select name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                            <option value="">-- Choose a package --</option>
                            @foreach($packages as $p)
                            <option value="{{ $p->id }}" {{ old('package_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} — KES {{ number_format($p->price, 0) }}
                                ({{ $p->speed_download }}↓/{{ $p->speed_upload }}↑ Mbps,
                                @if($p->validity_days){{ $p->validity_days }}d@endif
                                @if($p->validity_hours) {{ $p->validity_hours }}h@endif)
                            </option>
                            @endforeach
                        </select>
                        @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">M-Pesa Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-mobile-screen-button"></i></span>
                            <input type="tel" name="phone" value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="e.g. 0712345678" required>
                        </div>
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">You will receive an STK push prompt on this number.</div>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="pay-btn">
                        <i class="fa-solid fa-mobile-screen-button me-1"></i>
                        Pay with M-Pesa
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-3 small text-muted text-center">
            <i class="fa-solid fa-shield-halved text-success me-1"></i>
            Payments are secured and processed by Safaricom M-Pesa.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const card       = document.getElementById('payment-status-card');
    if (!card) return;

    const checkoutId = card.dataset.checkoutId;
    const statusUrl  = card.dataset.statusUrl;
    if (!checkoutId) return;

    const statusText = document.getElementById('payment-status-text');
    const spinner    = document.getElementById('payment-spinner');
    const progressBar = document.getElementById('progress-bar');

    let elapsed = 0;
    const maxTime = 60; // seconds

    const interval = setInterval(async function () {
        elapsed++;
        const pct = Math.min((elapsed / maxTime) * 100, 100);
        if (progressBar) progressBar.style.width = pct + '%';

        if (elapsed >= maxTime) {
            clearInterval(interval);
            if (statusText) statusText.textContent = 'Payment confirmation timed out. Please check your payment history.';
            if (spinner) spinner.remove();
            return;
        }

        try {
            const res  = await fetch(statusUrl.replace('__id__', checkoutId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.status === 'completed') {
                clearInterval(interval);
                if (spinner) spinner.remove();
                if (statusText) statusText.textContent = '✅ Payment confirmed! Receipt: ' + (data.mpesa_receipt_number || '');
                if (progressBar) { progressBar.classList.remove('progress-bar-animated'); progressBar.classList.add('bg-success'); progressBar.style.width = '100%'; }
                setTimeout(() => window.location.href = '{{ route("customer.payments.index") }}', 2000);
            } else if (data.status === 'failed') {
                clearInterval(interval);
                if (spinner) spinner.remove();
                if (statusText) statusText.textContent = '❌ Payment failed. Please try again.';
                if (progressBar) { progressBar.classList.remove('progress-bar-animated', 'bg-success'); progressBar.classList.add('bg-danger'); }
            }
        } catch (e) {
            // silently retry
        }
    }, 5000);
})();

// Disable button on submit to prevent double-click
document.getElementById('renewal-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('pay-btn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'; }
});
</script>
@endpush
