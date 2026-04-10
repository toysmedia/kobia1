<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MikroTik Script – {{ $router->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #1e1e2e; color: #cdd6f4; font-family: 'Courier New', Courier, monospace; min-height: 100vh; }
        .toolbar {
            background: #181825;
            border-bottom: 1px solid #313244;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .toolbar h1 { font-size: 1rem; color: #cba6f7; font-family: sans-serif; }
        .toolbar .meta { font-size: 0.8rem; color: #6c7086; font-family: sans-serif; }
        .btn-group { display: flex; gap: 8px; }
        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-family: sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-copy { background: #313244; color: #cdd6f4; }
        .btn-copy:hover { background: #45475a; }
        .btn-download { background: #cba6f7; color: #1e1e2e; font-weight: 600; }
        .btn-download:hover { background: #b4befe; }
        .btn-back { background: transparent; color: #89b4fa; border: 1px solid #45475a; }
        .btn-back:hover { background: #313244; }
        .script-container { padding: 20px; }
        pre {
            background: #181825;
            border: 1px solid #313244;
            border-radius: 8px;
            padding: 20px;
            font-size: 0.85rem;
            line-height: 1.6;
            overflow-x: auto;
            white-space: pre;
            color: #cdd6f4;
            tab-size: 4;
        }
        .line-numbers { color: #6c7086; user-select: none; display: inline-block; min-width: 3ch; margin-right: 1ch; text-align: right; }
        .comment { color: #6c7086; }
        .keyword { color: #cba6f7; }
        .string { color: #a6e3a1; }
        .copy-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #a6e3a1;
            color: #1e1e2e;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        .copy-toast.show { opacity: 1; }
    </style>
</head>
<body>

<div class="toolbar">
    <div>
        <h1>&#128194; MikroTik Script &mdash; {{ $router->name }}</h1>
        <div class="meta">Generated: {{ now()->format('d M Y H:i:s') }} &nbsp;|&nbsp; WAN: {{ $router->wan_ip }} &nbsp;|&nbsp; Phase: {{ $router->provision_phase ?? 0 }}</div>
    </div>
    <div class="btn-group">
        <a href="{{ url()->previous() }}" class="btn btn-back">&#8592; Back</a>
        <button class="btn btn-copy" onclick="copyScript()">&#128203; Copy</button>
        <a href="{{ route('admin.isp.routers.download_script', [$router, 'download' => 1]) }}" class="btn btn-download">&#8615; Download .rsc</a>
    </div>
</div>

<div class="script-container">
    <pre id="scriptContent">{{ $script }}</pre>
</div>

<div class="copy-toast" id="copyToast">&#10003; Copied to clipboard!</div>

<script>
function copyScript() {
    const text = document.getElementById('scriptContent').innerText;
    navigator.clipboard.writeText(text).then(function() {
        const toast = document.getElementById('copyToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }).catch(function() {
        // Fallback for older browsers
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        const toast = document.getElementById('copyToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    });
}
</script>
</body>
</html>