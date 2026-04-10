<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Error - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff;padding:20px}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:40px 36px;width:100%;max-width:420px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.error-icon{font-size:3.5rem;margin-bottom:16px}
h1{font-size:1.3rem;color:#f87171;margin-bottom:8px}
.error-msg{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);border-radius:10px;padding:14px 16px;margin:20px 0;color:#fca5a5;font-size:0.95rem}
.btn-back{display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#6d28d9,#a21caf);border-radius:10px;color:#fff;text-decoration:none;font-weight:600;margin-top:4px}
.btn-back:hover{opacity:0.9}
.footer-text{margin-top:24px;font-size:0.75rem;color:rgba(255,255,255,0.3)}
</style>
</head>
<body>
<div class="card">
  <div class="error-icon">⚠️</div>
  <h1>HotSpot Error</h1>
  <div class="error-msg">$(error)</div>
  <a href="$(link-login-only)" class="btn-back">← Back to Login</a>
  <div class="footer-text">{{ $appName ?? 'iNettotik' }} &bull; Powered by iNettotik Billing</div>
</div>
</body>
</html>
