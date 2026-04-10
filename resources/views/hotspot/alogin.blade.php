<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Successful - {{ $appName ?? 'WiFi' }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;color:#fff}
.card{background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:40px;max-width:420px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.success-icon{font-size:4rem;margin-bottom:16px}
h1{font-size:1.5rem;color:#4ade80;margin-bottom:8px}
p{color:#d4d4d4;margin-bottom:24px}
.btn{display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#6d28d9,#a21caf);border-radius:10px;color:#fff;text-decoration:none;font-weight:600;margin:6px}
.btn-outline{background:transparent;border:1px solid rgba(167,139,250,0.5);color:#a78bfa}
.redirect-text{font-size:0.8rem;color:rgba(255,255,255,0.4);margin-top:20px}
</style>
</head>
<body>
<div class="card">
  <div class="success-icon">✅</div>
  <h1>Connected!</h1>
  <p>You are now connected to the internet.<br>Welcome to <strong>{{ $appName ?? 'our WiFi network' }}</strong>!</p>

  <a href="$(link-status)" class="btn">📊 View Status</a>
  <a href="$(link-orig)" class="btn btn-outline">🌐 Continue Browsing</a>

  <div class="redirect-text" id="redirect-msg">Redirecting in <span id="countdown">5</span>s...</div>
</div>
<script>
var c = 5;
var timer = setInterval(function(){
  c--;
  document.getElementById('countdown').textContent = c;
  if(c <= 0){
    clearInterval(timer);
    window.location.href = '$(link-orig)' || '/';
  }
}, 1000);
</script>
</body>
</html>
