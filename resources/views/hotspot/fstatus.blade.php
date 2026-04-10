<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Not Logged In - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff;padding:20px}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:40px 36px;width:100%;max-width:420px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.icon{font-size:3rem;margin-bottom:16px}
h1{font-size:1.3rem;color:#fbbf24;margin-bottom:12px}
p{color:rgba(255,255,255,0.6);margin-bottom:24px;font-size:0.95rem}
.btn{display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#6d28d9,#a21caf);border-radius:10px;color:#fff;text-decoration:none;font-weight:600}
.btn:hover{opacity:0.9}
.footer-text{margin-top:24px;font-size:0.75rem;color:rgba(255,255,255,0.3)}
</style>
</head>
<body>
<div class="card">
  <div class="icon">🔒</div>
  <h1>You are not logged in</h1>
  <p>Please log in to view your session status.</p>
  <a href="$(link-login-only)" class="btn">🔓 Go to Login</a>
  <div class="footer-text">{{ $appName ?? 'iNettotik' }} &bull; Powered by iNettotik Billing</div>
</div>
</body>
</html>
