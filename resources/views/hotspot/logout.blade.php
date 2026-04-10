<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logged Out - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff;padding:20px}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:36px;max-width:460px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.header{text-align:center;margin-bottom:28px}
.header h1{font-size:1.4rem;color:#a78bfa;margin-bottom:4px}
.header p{color:rgba(255,255,255,0.5);font-size:0.85rem}
.username-box{background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.3);border-radius:10px;padding:14px;text-align:center;margin-bottom:20px}
.username-box .label{font-size:0.75rem;color:#a78bfa;margin-bottom:4px}
.username-box .uname{font-size:1rem;font-weight:600;letter-spacing:2px}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px}
.stat-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:16px;text-align:center}
.stat-card .label{font-size:0.75rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.stat-card .value{font-size:1.1rem;font-weight:700;color:#fff}
.stat-card .value.green{color:#4ade80}
.stat-card .value.blue{color:#60a5fa}
.btn-login{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#6d28d9,#a21caf);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;text-decoration:none;text-align:center;transition:opacity .2s}
.btn-login:hover{opacity:0.85}
.footer-text{text-align:center;margin-top:20px;font-size:0.75rem;color:rgba(255,255,255,0.3)}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>👋 Session Ended</h1>
    <p>You have been logged out of {{ $appName ?? 'iNettotik' }} WiFi</p>
  </div>

  <div class="username-box">
    <div class="label">User</div>
    <div class="uname">$(username)</div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="label">Session Time</div>
      <div class="value green">$(uptime)</div>
    </div>
    <div class="stat-card">
      <div class="label">Downloaded</div>
      <div class="value blue">$(bytes-in-nice)</div>
    </div>
    <div class="stat-card">
      <div class="label">Uploaded</div>
      <div class="value blue">$(bytes-out-nice)</div>
    </div>
    <div class="stat-card">
      <div class="label">Packets In/Out</div>
      <div class="value">$(packets-in) / $(packets-out)</div>
    </div>
  </div>

  <a href="$(link-login-only)" class="btn-login">🔓 Login Again</a>

  <div class="footer-text">{{ $appName ?? 'iNettotik' }} &bull; Powered by iNettotik Billing</div>
</div>
</body>
</html>
