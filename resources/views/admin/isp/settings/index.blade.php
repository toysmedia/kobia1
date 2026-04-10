@extends('admin.layouts.app')
@section('title', 'ISP Settings')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">ISP Settings</h5>
    </div>

    @if(session('success'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="col-sm-12 mb-3">
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                {{-- Nav Tabs --}}
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab','company') === 'company' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-company" type="button">
                            <i class="bx bx-building-house me-1"></i> Company
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'radius' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-radius" type="button">
                            <i class="bx bx-server me-1"></i> RADIUS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'mpesa' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-mpesa" type="button">
                            <i class="bx bx-mobile-alt me-1"></i> M-Pesa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'payment_gateways' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-payment-gateways" type="button">
                            <i class="bx bx-credit-card me-1"></i> Payment Gateways
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'sms' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-sms" type="button">
                            <i class="bx bx-message-rounded me-1"></i> SMS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'whatsapp' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-whatsapp" type="button">
                            <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'email' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-email" type="button">
                            <i class="bx bx-envelope me-1"></i> Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'billing' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-billing" type="button">
                            <i class="bx bx-dollar-circle me-1"></i> Billing
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- ===== COMPANY TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab','company') === 'company' ? 'show active' : '' }}" id="tab-company">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab" value="company">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}" placeholder="My ISP Ltd">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Billing Domain</label>
                                    <input type="text" name="billing_domain" class="form-control" value="{{ $settings['billing_domain'] ?? '' }}" placeholder="billing.myisp.co.ke">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Company Logo</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <div class="form-text">Upload a new logo (optional)</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '' }}" placeholder="+254700000000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? '' }}" placeholder="info@myisp.co.ke">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Physical address">{{ $settings['address'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Company Settings</button>
                        </form>
                    </div>

                    {{-- ===== RADIUS TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'radius' ? 'show active' : '' }}" id="tab-radius">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="radius">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">RADIUS Server IP</label>
                                    <input type="text" name="radius_server_ip" class="form-control" value="{{ $settings['radius_server_ip'] ?? '' }}" placeholder="127.0.0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Global RADIUS Secret</label>
                                    <input type="text" name="radius_secret" class="form-control" value="{{ $settings['radius_secret'] ?? '' }}" placeholder="Default shared secret">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Auth Port</label>
                                    <input type="number" name="radius_port" class="form-control" value="{{ $settings['radius_port'] ?? '1812' }}" min="1" max="65535">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Accounting Port</label>
                                    <input type="number" name="radius_acct_port" class="form-control" value="{{ $settings['radius_acct_port'] ?? '1813' }}" min="1" max="65535">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Interim Update Interval (s)</label>
                                    <input type="number" name="interim_update_interval" class="form-control" value="{{ $settings['interim_update_interval'] ?? '300' }}" min="60">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save RADIUS Settings</button>
                        </form>
                    </div>

                    {{-- ===== M-PESA TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'mpesa' ? 'show active' : '' }}" id="tab-mpesa">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="mpesa">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Environment</label>
                                    <select name="mpesa_environment" class="form-select">
                                        <option value="sandbox"    {{ ($settings['mpesa_environment'] ?? 'sandbox') === 'sandbox'    ? 'selected' : '' }}>Sandbox</option>
                                        <option value="production" {{ ($settings['mpesa_environment'] ?? '') === 'production' ? 'selected' : '' }}>Production</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">STK Shortcode</label>
                                    <input type="text" name="mpesa_shortcode" class="form-control" value="{{ $settings['mpesa_shortcode'] ?? '' }}" placeholder="174379">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Consumer Key</label>
                                    <input type="text" name="mpesa_consumer_key" class="form-control" value="{{ $settings['mpesa_consumer_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Consumer Secret</label>
                                    <input type="password" name="mpesa_consumer_secret" class="form-control" value="{{ $settings['mpesa_consumer_secret'] ?? '' }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Passkey</label>
                                    <input type="password" name="mpesa_passkey" class="form-control" value="{{ $settings['mpesa_passkey'] ?? '' }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">STK Push Callback URL</label>
                                    <input type="url" name="mpesa_stk_callback_url" class="form-control" value="{{ $settings['mpesa_stk_callback_url'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Shortcode</label>
                                    <input type="text" name="mpesa_c2b_shortcode" class="form-control" value="{{ $settings['mpesa_c2b_shortcode'] ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Validation URL</label>
                                    <input type="url" name="mpesa_c2b_validation_url" class="form-control" value="{{ $settings['mpesa_c2b_validation_url'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Confirmation URL</label>
                                    <input type="url" name="mpesa_c2b_confirmation_url" class="form-control" value="{{ $settings['mpesa_c2b_confirmation_url'] ?? '' }}" placeholder="https://...">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save M-Pesa Settings</button>
                        </form>
                    </div>

                    {{-- ===== PAYMENT GATEWAYS TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'payment_gateways' ? 'show active' : '' }}" id="tab-payment-gateways">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="payment_gateways">

                            {{-- M-Pesa Paybill --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-mobile me-1"></i>M-Pesa Paybill</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_mpesa_paybill_enabled" value="1"
                                               id="pg_mpesa_paybill_enabled"
                                               {{ ($settings['pg_mpesa_paybill_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_mpesa_paybill_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Paybill Number</label>
                                            <input type="text" name="pg_mpesa_paybill_number" class="form-control"
                                                   value="{{ $settings['pg_mpesa_paybill_number'] ?? '' }}" placeholder="174379">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Account Number</label>
                                            <input type="text" name="pg_mpesa_paybill_account" class="form-control"
                                                   value="{{ $settings['pg_mpesa_paybill_account'] ?? '' }}" placeholder="Account number">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Display Name</label>
                                            <input type="text" name="pg_mpesa_paybill_display" class="form-control"
                                                   value="{{ $settings['pg_mpesa_paybill_display'] ?? '' }}" placeholder="Pay via M-Pesa Paybill">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- M-Pesa Till --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-store me-1"></i>M-Pesa Till</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_mpesa_till_enabled" value="1"
                                               id="pg_mpesa_till_enabled"
                                               {{ ($settings['pg_mpesa_till_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_mpesa_till_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Till Number</label>
                                            <input type="text" name="pg_mpesa_till_number" class="form-control"
                                                   value="{{ $settings['pg_mpesa_till_number'] ?? '' }}" placeholder="5XXXXXX">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Display Name</label>
                                            <input type="text" name="pg_mpesa_till_display" class="form-control"
                                                   value="{{ $settings['pg_mpesa_till_display'] ?? '' }}" placeholder="Pay via M-Pesa Till">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kopokopo --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-transfer me-1"></i>Kopokopo</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_kopokopo_enabled" value="1"
                                               id="pg_kopokopo_enabled"
                                               {{ ($settings['pg_kopokopo_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_kopokopo_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Client ID</label>
                                            <input type="text" name="pg_kopokopo_client_id" class="form-control"
                                                   value="{{ $settings['pg_kopokopo_client_id'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Client Secret</label>
                                            <input type="password" name="pg_kopokopo_client_secret" class="form-control"
                                                   value="{{ $settings['pg_kopokopo_client_secret'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Till Number</label>
                                            <input type="text" name="pg_kopokopo_till" class="form-control"
                                                   value="{{ $settings['pg_kopokopo_till'] ?? '' }}" placeholder="5XXXXXX">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Webhook URL</label>
                                            <input type="url" name="pg_kopokopo_webhook_url" class="form-control"
                                                   value="{{ $settings['pg_kopokopo_webhook_url'] ?? '' }}" placeholder="https://...">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Environment</label>
                                            <select name="pg_kopokopo_env" class="form-select">
                                                <option value="sandbox"    {{ ($settings['pg_kopokopo_env'] ?? 'sandbox') === 'sandbox'    ? 'selected' : '' }}>Sandbox</option>
                                                <option value="production" {{ ($settings['pg_kopokopo_env'] ?? '') === 'production' ? 'selected' : '' }}>Production</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Equity Bank --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-bank me-1"></i>Equity Bank</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_equity_enabled" value="1"
                                               id="pg_equity_enabled"
                                               {{ ($settings['pg_equity_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_equity_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Merchant ID</label>
                                            <input type="text" name="pg_equity_merchant_id" class="form-control"
                                                   value="{{ $settings['pg_equity_merchant_id'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">API Key</label>
                                            <input type="password" name="pg_equity_api_key" class="form-control"
                                                   value="{{ $settings['pg_equity_api_key'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Paybill / Account</label>
                                            <input type="text" name="pg_equity_account" class="form-control"
                                                   value="{{ $settings['pg_equity_account'] ?? '' }}" placeholder="Account number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Callback URL</label>
                                            <input type="url" name="pg_equity_callback_url" class="form-control"
                                                   value="{{ $settings['pg_equity_callback_url'] ?? '' }}" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KCB Bank --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-building me-1"></i>KCB Bank</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_kcb_enabled" value="1"
                                               id="pg_kcb_enabled"
                                               {{ ($settings['pg_kcb_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_kcb_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Merchant Code</label>
                                            <input type="text" name="pg_kcb_merchant_code" class="form-control"
                                                   value="{{ $settings['pg_kcb_merchant_code'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">API Key</label>
                                            <input type="password" name="pg_kcb_api_key" class="form-control"
                                                   value="{{ $settings['pg_kcb_api_key'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Paybill / Account</label>
                                            <input type="text" name="pg_kcb_account" class="form-control"
                                                   value="{{ $settings['pg_kcb_account'] ?? '' }}" placeholder="Account number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Callback URL</label>
                                            <input type="url" name="pg_kcb_callback_url" class="form-control"
                                                   value="{{ $settings['pg_kcb_callback_url'] ?? '' }}" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Co-operative Bank --}}
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="bx bx-buildings me-1"></i>Co-operative Bank</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="pg_coop_enabled" value="1"
                                               id="pg_coop_enabled"
                                               {{ ($settings['pg_coop_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pg_coop_enabled">Enable</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Consumer Key</label>
                                            <input type="text" name="pg_coop_consumer_key" class="form-control"
                                                   value="{{ $settings['pg_coop_consumer_key'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Consumer Secret</label>
                                            <input type="password" name="pg_coop_consumer_secret" class="form-control"
                                                   value="{{ $settings['pg_coop_consumer_secret'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Account Number</label>
                                            <input type="text" name="pg_coop_account" class="form-control"
                                                   value="{{ $settings['pg_coop_account'] ?? '' }}" placeholder="Account number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Callback URL</label>
                                            <input type="url" name="pg_coop_callback_url" class="form-control"
                                                   value="{{ $settings['pg_coop_callback_url'] ?? '' }}" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Payment Gateway Settings</button>
                        </form>
                    </div>

                    {{-- ===== SMS TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'sms' ? 'show active' : '' }}" id="tab-sms">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="sms">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">SMS Gateway</label>
                                    <select name="sms_gateway" id="sms_gateway" class="form-select">
                                        <option value="africastalking" {{ ($settings['sms_gateway'] ?? 'africastalking') === 'africastalking' ? 'selected' : '' }}>Africa's Talking</option>
                                        <option value="blessedafrica"  {{ ($settings['sms_gateway'] ?? '') === 'blessedafrica'  ? 'selected' : '' }}>Blessed Africa</option>
                                        <option value="advanta"        {{ ($settings['sms_gateway'] ?? '') === 'advanta'        ? 'selected' : '' }}>Advanta SMS</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Sender ID / Name</label>
                                    <input type="text" name="sms_sender_id" class="form-control"
                                           value="{{ $settings['sms_sender_id'] ?? ($settings['at_sender_id'] ?? '') }}"
                                           placeholder="MyISP">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="sms_enabled"
                                               {{ ($settings['sms_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="sms_enabled">Enable SMS Notifications</label>
                                    </div>
                                </div>

                                {{-- Africa's Talking fields --}}
                                <div id="fields-africastalking" class="col-12">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">AT Username</label>
                                            <input type="text" name="at_username" class="form-control"
                                                   value="{{ $settings['at_username'] ?? '' }}" placeholder="sandbox">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">AT API Key</label>
                                            <input type="password" name="at_api_key" class="form-control"
                                                   value="{{ $settings['at_api_key'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Blessed Africa fields --}}
                                <div id="fields-blessedafrica" class="col-12 d-none">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Blessed Africa API Key</label>
                                            <input type="password" name="blessedafrica_api_key" class="form-control"
                                                   value="{{ $settings['blessedafrica_api_key'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Blessed Africa API URL</label>
                                            <input type="url" name="blessedafrica_api_url" class="form-control"
                                                   value="{{ $settings['blessedafrica_api_url'] ?? '' }}"
                                                   placeholder="https://api.blessedafrica.co.ke/sms">
                                        </div>
                                    </div>
                                </div>

                                {{-- Advanta SMS fields --}}
                                <div id="fields-advanta" class="col-12 d-none">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Advanta API Key</label>
                                            <input type="password" name="advanta_api_key" class="form-control"
                                                   value="{{ $settings['advanta_api_key'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Advanta Partner ID</label>
                                            <input type="text" name="advanta_partner_id" class="form-control"
                                                   value="{{ $settings['advanta_partner_id'] ?? '' }}"
                                                   placeholder="Your partner ID">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save SMS Settings</button>
                        </form>
                    </div>

                    {{-- ===== WHATSAPP TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'whatsapp' ? 'show active' : '' }}" id="tab-whatsapp">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="whatsapp">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">API URL</label>
                                    <input type="url" name="whatsapp_api_url" class="form-control"
                                           value="{{ $settings['whatsapp_api_url'] ?? '' }}"
                                           placeholder="https://api.whatsapp.example.com/send">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Instance ID</label>
                                    <input type="text" name="whatsapp_instance_id" class="form-control"
                                           value="{{ $settings['whatsapp_instance_id'] ?? '' }}"
                                           placeholder="Instance ID">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">API Key</label>
                                    <input type="password" name="whatsapp_api_key" class="form-control"
                                           value="{{ $settings['whatsapp_api_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Sender Number</label>
                                    <input type="text" name="whatsapp_sender_number" class="form-control"
                                           value="{{ $settings['whatsapp_sender_number'] ?? '' }}"
                                           placeholder="+254700000000">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="whatsapp_enabled" value="1" id="whatsapp_enabled"
                                               {{ ($settings['whatsapp_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="whatsapp_enabled">Enable WhatsApp Notifications</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save WhatsApp Settings</button>
                        </form>
                    </div>

                    {{-- ===== EMAIL TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'email' ? 'show active' : '' }}" id="tab-email">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="email">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">SMTP Host</label>
                                    <input type="text" name="mail_host" class="form-control"
                                           value="{{ $settings['mail_host'] ?? '' }}"
                                           placeholder="smtp.mailtrap.io">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold">Port</label>
                                    <input type="number" name="mail_port" class="form-control"
                                           value="{{ $settings['mail_port'] ?? '587' }}"
                                           min="1" max="65535">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold">Encryption</label>
                                    <select name="mail_encryption" class="form-select">
                                        <option value="tls"  {{ ($settings['mail_encryption'] ?? 'tls') === 'tls'  ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl"  {{ ($settings['mail_encryption'] ?? '') === 'ssl'  ? 'selected' : '' }}>SSL</option>
                                        <option value=""     {{ ($settings['mail_encryption'] ?? '') === ''     ? 'selected' : '' }}>None / Unencrypted</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Username</label>
                                    <input type="text" name="mail_username" class="form-control"
                                           value="{{ $settings['mail_username'] ?? '' }}"
                                           placeholder="smtp@example.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Password</label>
                                    <input type="password" name="mail_password" class="form-control"
                                           value="{{ $settings['mail_password'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">From Name</label>
                                    <input type="text" name="mail_from_name" class="form-control"
                                           value="{{ $settings['mail_from_name'] ?? '' }}"
                                           placeholder="My ISP">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">From Address</label>
                                    <input type="email" name="mail_from_address" class="form-control"
                                           value="{{ $settings['mail_from_address'] ?? '' }}"
                                           placeholder="noreply@myisp.co.ke">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Email Settings</button>
                        </form>
                    </div>

                    {{-- ===== BILLING TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'billing' ? 'show active' : '' }}" id="tab-billing">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="billing">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Default PPPoE Expiry (days)</label>
                                    <input type="number" name="default_pppoe_expiry_days" class="form-control" value="{{ $settings['default_pppoe_expiry_days'] ?? '30' }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Default Hotspot Expiry (hours)</label>
                                    <input type="number" name="default_hotspot_expiry_hours" class="form-control" value="{{ $settings['default_hotspot_expiry_hours'] ?? '24' }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Grace Period (hours)</label>
                                    <input type="number" name="grace_period_hours" class="form-control" value="{{ $settings['grace_period_hours'] ?? '0' }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Auto Disconnect</label>
                                    <select name="auto_disconnect" class="form-select">
                                        <option value="yes" {{ ($settings['auto_disconnect'] ?? 'yes') === 'yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="no"  {{ ($settings['auto_disconnect'] ?? '') === 'no'  ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Currency</label>
                                    <input type="text" name="currency" class="form-control" value="{{ $settings['currency'] ?? 'KES' }}" placeholder="KES">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Paybill Display Text</label>
                                    <input type="text" name="paybill_display_text" class="form-control" value="{{ $settings['paybill_display_text'] ?? '' }}" placeholder="Pay to Account...">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Billing Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show/hide SMS gateway-specific fields
function showSmsFields() {
    const gateway = document.getElementById('sms_gateway').value;
    const sections = ['africastalking', 'blessedafrica', 'advanta'];
    sections.forEach(function(s) {
        const el = document.getElementById('fields-' + s);
        if (el) el.classList.toggle('d-none', s !== gateway);
    });
}
// Run on page load to show correct fields and attach change listener
document.addEventListener('DOMContentLoaded', function() {
    const gatewaySelect = document.getElementById('sms_gateway');
    if (gatewaySelect) {
        gatewaySelect.addEventListener('change', showSmsFields);
        showSmsFields();
    }
});
</script>
@endpush