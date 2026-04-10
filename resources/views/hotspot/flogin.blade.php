<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $appName ?? 'WiFi Login' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;padding:20px}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:40px 36px;width:100%;max-width:420px;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.logo{text-align:center;margin-bottom:24px}
.logo img{height:60px;object-fit:contain}
.logo-text{font-size:1.6rem;font-weight:700;color:#a78bfa;letter-spacing:1px}
h2{font-size:1.2rem;text-align:center;color:#d4d4d4;margin-bottom:28px;font-weight:400}
.error-box{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#fca5a5;font-size:0.9rem;text-align:center}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:0.85rem;color:#a78bfa;font-weight:600;margin-bottom:8px;letter-spacing:0.5px}
.form-group input{width:100%;padding:14px 18px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);border-radius:10px;color:#fff;font-size:1.1rem;letter-spacing:2px;text-transform:uppercase;outline:none;transition:border-color .2s,background .2s}
.form-group input:focus{border-color:#a78bfa;background:rgba(167,139,250,0.1)}
.form-group input::placeholder{color:rgba(255,255,255,0.35);letter-spacing:1px;font-size:0.95rem;text-transform:none}
.btn-login{width:100%;padding:15px;background:linear-gradient(135deg,#6d28d9,#a21caf);border:none;border-radius:10px;color:#fff;font-size:1.05rem;font-weight:700;cursor:pointer;transition:opacity .2s,transform .1s;letter-spacing:0.5px;margin-bottom:12px}
.btn-login:hover{opacity:0.9;transform:translateY(-1px)}
.btn-buy{width:100%;padding:14px;background:transparent;border:1px solid rgba(167,139,250,0.5);border-radius:10px;color:#a78bfa;font-size:0.95rem;cursor:pointer;transition:background .2s,color .2s;text-decoration:none;display:block;text-align:center}
.btn-buy:hover{background:rgba(167,139,250,0.15);color:#fff}
.divider{display:flex;align-items:center;gap:12px;margin:16px 0;color:rgba(255,255,255,0.3);font-size:0.8rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,0.15)}
.footer-text{text-align:center;margin-top:24px;font-size:0.75rem;color:rgba(255,255,255,0.3)}
.paybill-box{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:14px;margin-top:16px;font-size:0.82rem;color:#d4d4d4;text-align:center}
.paybill-box strong{color:#a78bfa;font-size:1rem}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    @if(isset($appLogo))
    <img src="{{ $appLogo }}" alt="{{ $appName ?? 'Logo' }}">
    @else
    <div class="logo-text">{{ $appName ?? '📡 WiFi' }}</div>
    @endif
  </div>
  <h2>Enter Your M-Pesa Reference</h2>

  <!-- Login failed error — always visible on flogin -->
  <div class="error-box" id="error-box">
    $(error)
  </div>

  <!-- MikroTik Login Form -->
  <form name="sendin" action="$(link-login-only)" method="post">
    <input type="hidden" name="dst" value="$(link-orig)">
    <input type="hidden" name="popup" value="true">
    <div class="form-group">
      <label>M-Pesa Reference Code</label>
      <input type="text" name="username" id="ref-code"
             placeholder="e.g. QKX123456" autocomplete="off"
             autocorrect="off" autocapitalize="characters" spellcheck="false"
             required maxlength="20">
    </div>
    <input type="hidden" name="password" id="password-field">
    <button type="submit" class="btn-login" onclick="setPassword()">
      🔓 Try Again
    </button>
  </form>

  <div class="divider">or</div>

  <a href="https://{{ $billingDomain ?? parse_url(config('app.url'), PHP_URL_HOST) }}/buy" class="btn-buy">
    💳 Buy New Package
  </a>

  <div class="paybill-box">
    <div>Pay via M-Pesa Paybill:</div>
    <strong>{{ config('mpesa.shortcode', 'XXXXXX') }}</strong>
    <div style="margin-top:4px;font-size:0.78rem;opacity:0.7">Account: Your Phone Number</div>
  </div>

  <div class="footer-text">{{ $appName ?? 'iNettotik' }} &bull; Powered by iNettotik Billing</div>
</div>

<script>
function setPassword() {
  var code = document.getElementById('ref-code').value.trim().toUpperCase();
  document.getElementById('ref-code').value = code;
  document.getElementById('password-field').value = code;
}
document.getElementById('ref-code').addEventListener('input', function() {
  this.value = this.value.toUpperCase();
  document.getElementById('password-field').value = this.value;
});
</script>
</body>
</html>
