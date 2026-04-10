<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Session Status - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff;padding:20px}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:36px;max-width:460px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.header{text-align:center;margin-bottom:28px}
.header h1{font-size:1.4rem;color:#a78bfa;margin-bottom:4px}
.header p{color:rgba(255,255,255,0.5);font-size:0.85rem}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px}
.stat-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:16px;text-align:center}
.stat-card .label{font-size:0.75rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
.stat-card .value{font-size:1.2rem;font-weight:700;color:#fff}
.stat-card .value.green{color:#4ade80}
.stat-card .value.blue{color:#60a5fa}
.stat-card .value.yellow{color:#fbbf24}
.username-box{background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.3);border-radius:10px;padding:14px;text-align:center;margin-bottom:20px}
.username-box .label{font-size:0.75rem;color:#a78bfa;margin-bottom:4px}
.username-box .uname{font-size:1rem;font-weight:600;letter-spacing:2px}
.btn-logout{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#dc2626,#9b2c2c);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;text-decoration:none;text-align:center;transition:opacity .2s}
.btn-logout:hover{opacity:0.85}
.refresh-info{text-align:center;font-size:0.75rem;color:rgba(255,255,255,0.3);margin-top:14px}
.isp-name{text-align:center;font-size:0.72rem;color:rgba(255,255,255,0.25);margin-top:16px}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>📡 Session Status</h1>
    <p>{{ $appName ?? 'iNettotik' }} WiFi Network</p>
  </div>

  <div class="username-box">
    <div class="label">Connected As</div>
    <div class="uname">$(username)</div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="label">Session Time</div>
      <div class="value green">$(uptime)</div>
    </div>
    <div class="stat-card">
      <div class="label">Time Remaining</div>
      <div class="value yellow">$(session-time-left)</div>
    </div>
    <div class="stat-card">
      <div class="label">Downloaded</div>
      <div class="value blue">$(bytes-in)</div>
    </div>
    <div class="stat-card">
      <div class="label">Uploaded</div>
      <div class="value blue">$(bytes-out)</div>
    </div>
  </div>

  <a href="$(link-logout)" class="btn-logout">🔌 Logout / Disconnect</a>

  <div class="refresh-info">Auto-refreshes every 30 seconds</div>
  <div class="isp-name">{{ $appName ?? 'iNettotik Billing System' }}</div>
</div>

<script>
// Auto-refresh every 30 seconds
setTimeout(function(){ window.location.reload(); }, 30000);
</script>
</body>
</html>
