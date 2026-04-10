<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="0; url=$(link-status)">
<title>Redirecting to Status… - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:40px;max-width:420px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
p{color:#d4d4d4;margin-top:12px;font-size:0.95rem}
.footer-text{margin-top:24px;font-size:0.75rem;color:rgba(255,255,255,0.3)}
</style>
</head>
<body>
<div class="card">
  <p>📊 Redirecting to session status…</p>
  <div class="footer-text">{{ $appName ?? 'iNettotik' }} &bull; Powered by iNettotik Billing</div>
</div>
<script>window.location.replace('$(link-status)');</script>
</body>
</html>
