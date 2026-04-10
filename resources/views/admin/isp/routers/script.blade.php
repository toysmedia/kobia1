@extends('admin.layouts.app')
@section('title', 'Provision Wizard — ' . $router->name)

@push('styles')
<style>
    .provision-code-wrap {
        position: relative;
    }
    .provision-code-header {
        background: #1e1e2e;
        color: #cdd6f4;
        padding: 8px 14px;
        border-radius: 6px 6px 0 0;
        font-family: sans-serif;
        font-size: 0.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .provision-code-block {
        background: #1e1e2e;
        color: #cdd6f4;
        font-size: 0.8rem;
        line-height: 1.5;
        max-height: 420px;
        overflow-y: auto;
        white-space: pre;
        font-family: 'Courier New', monospace;
        border-radius: 0 0 6px 6px;
        padding: 14px;
        margin: 0;
    }
    .step-card-locked {
        pointer-events: none;
    }
    #step2-lock-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.55);
        border-radius: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .dark-mode #step2-lock-overlay {
        background: rgba(0,0,0,0.45);
    }
</style>
@endpush

@section('content')
@php $phase = (int)($router->provision_phase ?? 0); @endphp

{{-- Page header --}}
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bx bx-terminal me-1"></i> Provision Wizard &mdash; {{ $router->name }}</h5>
            <small class="text-muted">Generated {{ now()->format('d M Y H:i:s') }} &nbsp;|&nbsp; WAN: {{ $router->wan_ip ?: '—' }} &nbsp;|&nbsp; Phase: {{ $phase }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.isp.routers.download_script', [$router, 'section' => 'full']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-download me-1"></i> Download Full .rsc
            </a>
            <a href="{{ route('admin.isp.routers.show', $router) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Router
            </a>
        </div>
    </div>
</div>

{{-- Completion banner (shown when fully provisioned) --}}
<div id="completion-alert" class="alert alert-success d-flex align-items-center gap-3 mb-4{{ $phase >= 3 ? '' : ' d-none' }}" role="alert">
    <i class="bx bx-check-circle fs-3"></i>
    <div class="flex-grow-1">
        <strong>🎉 Router fully provisioned!</strong> VPN tunnel active, services configured.
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('admin.isp.routers.show', $router) }}" class="btn btn-sm btn-success">View Router Details</a>
        <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-sm btn-outline-success">Back to Routers List</a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- STEP 1 — Foundation & VPN Tunnel                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill">Step 1</span>
            <h6 class="mb-0"><i class="bx bx-shield-quarter me-1"></i> Foundation &amp; VPN Tunnel</h6>
        </div>
        <span id="step1-status">
            @if($phase >= 1)
                <span class="badge bg-success"><i class="bx bx-check me-1"></i>VPN Connected — {{ $router->vpn_ip ?: 'IP pending' }}</span>
            @else
                <span class="badge bg-secondary"><i class="bx bx-loader-alt bx-spin me-1"></i>Waiting…</span>
            @endif
        </span>
    </div>
    <div class="card-body">

        @if($phase >= 1)
        {{-- Already complete — show collapsed notice --}}
        <div class="alert alert-success py-2 mb-3">
            <i class="bx bx-check-circle me-1"></i> Foundation script has been applied. VPN tunnel is established.
        </div>
        @endif

        <p class="text-muted small mb-3">
            Paste this script into your MikroTik terminal (<strong>WinBox → New Terminal</strong>).
            It will configure certificates, OpenVPN tunnel, RADIUS, and firewall.
            The router will automatically call back when the VPN tunnel is established.
        </p>

        <div class="provision-code-wrap mb-3">
            <div class="provision-code-header">
                <span><i class="bx bx-terminal me-1"></i> RouterOS Script — Step 1: Foundation</span>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('admin.isp.routers.download_script', [$router, 'section' => 'foundation']) }}"
                       class="btn btn-sm btn-outline-light">
                        <i class="bx bx-download me-1"></i> Download
                    </a>
                    <button class="btn btn-sm btn-outline-light" onclick="copyScript('foundation-code', event)">
                        <i class="bx bx-copy me-1"></i> Copy
                    </button>
                </div>
            </div>
            <pre id="foundation-code" class="provision-code-block">{{ $foundationScript }}</pre>
        </div>

        {{-- Live status polling — Step 1 --}}
        <div id="step1-waiting"{{ $phase >= 1 ? ' style="display:none"' : '' }}>
            <div class="d-flex align-items-center gap-2 text-muted small">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                ⏳ Waiting for router to connect… (polling every 3 s)
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- STEP 2 — Services Configuration                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="card mb-4 position-relative{{ $phase < 1 ? ' step-card-locked' : '' }}" id="step2-card">

    {{-- Lock overlay (hidden once Step 1 completes) --}}
    @if($phase < 1)
    <div id="step2-lock-overlay">
        <div class="text-center text-muted">
            <i class="bx bx-lock-alt fs-1 d-block mb-1"></i>
            <small>Locked — complete Step 1 first</small>
        </div>
    </div>
    @endif

    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary rounded-pill">Step 2</span>
            <h6 class="mb-0"><i class="bx bx-server me-1"></i> Services Configuration</h6>
        </div>
        <span id="step2-status">
            @if($phase >= 3)
                <span class="badge bg-success"><i class="bx bx-check me-1"></i>Fully Provisioned</span>
            @elseif($phase >= 2)
                <span class="badge bg-info"><i class="bx bx-loader-alt bx-spin me-1"></i>Services Configuring…</span>
            @elseif($phase >= 1)
                <span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Ready — paste script</span>
            @else
                <span class="badge bg-secondary"><i class="bx bx-lock me-1"></i>Locked</span>
            @endif
        </span>
    </div>

    <div id="step2-content" class="card-body"{{ $phase < 1 ? ' style="display:none"' : '' }}>

        @if($phase >= 3)
        <div class="alert alert-success py-2 mb-3">
            <i class="bx bx-check-circle me-1"></i> Services script applied. Router is fully provisioned.
        </div>
        @endif

        <p class="text-muted small mb-3">
            Paste this script to configure PPPoE/Hotspot services. The router will call back when complete.
        </p>

        <div class="provision-code-wrap mb-3">
            <div class="provision-code-header">
                <span><i class="bx bx-terminal me-1"></i> RouterOS Script — Step 2: Services</span>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('admin.isp.routers.download_script', [$router, 'section' => 'services']) }}"
                       class="btn btn-sm btn-outline-light">
                        <i class="bx bx-download me-1"></i> Download
                    </a>
                    <button class="btn btn-sm btn-outline-light" onclick="copyScript('services-code', event)">
                        <i class="bx bx-copy me-1"></i> Copy
                    </button>
                </div>
            </div>
            <pre id="services-code" class="provision-code-block">{{ $servicesScript }}</pre>
        </div>

        {{-- Live status polling — Step 2 --}}
        <div id="step2-waiting"{{ $phase >= 3 ? ' style="display:none"' : '' }}>
            <div class="d-flex align-items-center gap-2 text-muted small">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                ⏳ Waiting for services to configure… (polling every 3 s)
            </div>
        </div>

    </div>

    {{-- Placeholder shown when step 2 is locked --}}
    <div id="step2-locked-msg" class="card-body text-center text-muted py-5"{{ $phase >= 1 ? ' style="display:none"' : '' }}>
        <i class="bx bx-lock fs-1 d-block mb-2"></i>
        Complete Step 1 to unlock the services script.
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    let pollInterval = null;
    let currentPhase = {{ (int)($router->provision_phase ?? 0) }};
    const statusUrl  = '{{ route('admin.isp.routers.provision_status', $router) }}';
    const routerUrl  = '{{ route('admin.isp.routers.show', $router) }}';

    function updateUI(data) {
        const phase = data.provision_phase;

        // ── Phase ≥ 1 — Step 1 complete, unlock Step 2 ─────────────────────
        if (phase >= 1) {
            const vpnLabel = data.vpn_ip ? 'VPN Connected — ' + data.vpn_ip : 'VPN Connected';
            document.getElementById('step1-status').innerHTML =
                '<span class="badge bg-success"><i class="bx bx-check me-1"></i>' + vpnLabel + '</span>';

            const w1 = document.getElementById('step1-waiting');
            if (w1) w1.style.display = 'none';

            const card2 = document.getElementById('step2-card');
            card2.classList.remove('step-card-locked');

            const overlay = document.getElementById('step2-lock-overlay');
            if (overlay) overlay.style.display = 'none';

            const lockedMsg = document.getElementById('step2-locked-msg');
            if (lockedMsg) lockedMsg.style.display = 'none';

            const content2 = document.getElementById('step2-content');
            if (content2) content2.style.display = 'block';

            if (phase === 1) {
                document.getElementById('step2-status').innerHTML =
                    '<span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Ready — paste script</span>';
            }
        }

        // ── Phase ≥ 2 — Services configuring ───────────────────────────────
        if (phase >= 2) {
            document.getElementById('step2-status').innerHTML =
                '<span class="badge bg-info"><i class="bx bx-loader-alt bx-spin me-1"></i>Services Configuring…</span>';
        }

        // ── Phase ≥ 3 — Fully provisioned ──────────────────────────────────
        if (phase >= 3) {
            document.getElementById('step2-status').innerHTML =
                '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Fully Provisioned</span>';

            const w2 = document.getElementById('step2-waiting');
            if (w2) w2.style.display = 'none';

            const banner = document.getElementById('completion-alert');
            if (banner) banner.classList.remove('d-none');

            clearInterval(pollInterval);
        }
    }

    function startPolling() {
        pollInterval = setInterval(async () => {
            try {
                const res  = await fetch(statusUrl);
                const data = await res.json();
                if (data.provision_phase > currentPhase) {
                    currentPhase = data.provision_phase;
                    updateUI(data);
                }
            } catch (e) {
                console.error('Poll error:', e);
            }
        }, 3000);
    }

    window.copyScript = function (elementId, event) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn      = event.target.closest('button');
            const original = btn.innerHTML;
            btn.innerHTML  = '<i class="bx bx-check me-1"></i>Copied!';
            btn.classList.replace('btn-outline-light', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.replace('btn-success', 'btn-outline-light');
            }, 2000);
        }).catch(() => {
            // Fallback for legacy browsers without clipboard API support
            const ta      = document.createElement('textarea');
            ta.value      = text;
            ta.style.position = 'fixed';
            ta.style.opacity  = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        // If already at a phase, update UI immediately
        if (currentPhase >= 1) {
            updateUI({ provision_phase: currentPhase, vpn_ip: {!! json_encode($router->vpn_ip) !!} });
        }
        // Only start polling if not fully complete
        if (currentPhase < 3) {
            startPolling();
        }
    });
})();
</script>
@endpush