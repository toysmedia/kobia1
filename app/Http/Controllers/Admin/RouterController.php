<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Nas;
use App\Models\AuditLog;
use App\Models\IspSetting;
use App\Services\MikrotikScriptService;
use App\Services\RouterOSApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class RouterController extends Controller
{
    public function __construct(protected MikrotikScriptService $scriptService) {}

    public function index()
    {
        $routers = Router::orderBy('name')->get();
        $nasIps  = \App\Models\Nas::pluck('nasname')->flip()->toArray();
        return view('admin.isp.routers.index', compact('routers', 'nasIps'));
    }

    public function create()
    {
        return view('admin.isp.routers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'model'            => 'nullable|string|max:100',
            'routeros_version' => 'nullable|string|max:50',
            'connection_type'  => 'nullable|string|in:direct,vpn,hotspot',
            'notes'            => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        $validated['connection_type']    = $request->input('connection_type', 'direct');
        $validated['radius_secret']      = Str::random(16);
        $validated['wan_interface']      = 'ether1';
        $validated['customer_interface'] = 'bridge1';
        $validated['pppoe_pool_range']   = '10.10.1.1-10.10.1.254';
        $validated['hotspot_pool_range'] = '10.20.1.1-10.20.1.254';
        $validated['billing_domain']     = IspSetting::getValue('billing_domain', '');
        $validated['wan_ip']             = null;
        $validated['is_active']          = $request->boolean('is_active', true);
        $validated['service_mode']       = 'pppoe_hotspot';
        $validated['openvpn_port']       = config('openvpn.port', 443);
        $validated['pppoe_bridge_name']  = 'pppoe_bridge';
        $validated['hotspot_bridge_name'] = 'hotspot_bridge';
        $validated['hotspot_gateway_ip'] = '11.220.0.1';
        $validated['hotspot_prefix']     = 16;
        $validated['pppoe_gateway_ip']   = '19.225.0.1';
        $validated['timezone']           = 'Africa/Nairobi';

        $router = Router::create($validated);

        $poolOctet = (($router->id - 1) % 254) + 1;
        $router->update([
            'ref_code'           => 'RTR-' . str_pad($router->id, 3, '0', STR_PAD_LEFT),
            // vpn_ip is left null; it will be populated by the OpenVPN server
            // via the router callback endpoint once the tunnel is established.
            'vpn_ip'             => null,
            'pppoe_pool_range'   => "10.10.{$poolOctet}.1-10.10.{$poolOctet}.254",
            'hotspot_pool_range' => "10.20.{$poolOctet}.1-10.20.{$poolOctet}.254",
        ]);

        AuditLog::record('router.created', Router::class, $router->id, [], $router->fresh()->toArray());

        return redirect()->route('admin.isp.routers.index')
            ->with('success', "Router '{$router->name}' created successfully.");
    }

    public function edit(Router $router)
    {
        return view('admin.isp.routers.edit', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $old  = $router->toArray();
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'model'              => 'nullable|string|max:100',
            'routeros_version'   => 'nullable|string|max:50',
            'radius_secret'      => 'required|string|max:100',
            'wan_interface'      => 'required|string|max:50',
            'customer_interface' => 'required|string|max:50',
            'pppoe_pool_range'   => 'required|string',
            'hotspot_pool_range' => 'required|string',
            'billing_domain'     => 'nullable|string|max:255',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string',
            'wan_ip'             => 'nullable|ip',
            'vpn_ip'             => 'nullable|ip',
            'connection_type'    => 'nullable|string|in:direct,vpn,hotspot',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $router->update($data);
        $router->refresh();

        if ($router->vpn_ip || $router->wan_ip) {
            $this->syncNas($router);
        }

        AuditLog::record('router.updated', Router::class, $router->id, $old, $router->fresh()->toArray());

        return redirect()->route('admin.isp.routers.index')
            ->with('success', "Router '{$router->name}' updated.");
    }

    public function destroy(Router $router)
    {
        AuditLog::record('router.deleted', Router::class, $router->id, $router->toArray(), []);
        $nasIp = $router->vpn_ip ?: $router->wan_ip;
        if ($nasIp) {
            Nas::where('nasname', $nasIp)->delete();
        }
        $router->delete();
        return redirect()->route('admin.isp.routers.index')->with('success', 'Router deleted.');
    }

    public function show(Router $router)
    {
        return view('admin.isp.routers.show', compact('router'));
    }

    public function script(Router $router)
    {
        $foundationScript = $this->scriptService->generateFoundation($router);
        $servicesScript   = $this->scriptService->generateServices($router);

        return view('admin.isp.routers.script', compact('router', 'foundationScript', 'servicesScript'));
    }

    public function downloadScript(Router $router, Request $request)
    {
        $section = $request->query('section', 'full');

        switch ($section) {
            case 'foundation':
                $script = $this->scriptService->generateFoundation($router);
                $suffix = '-foundation';
                break;
            case 'services':
                $script = $this->scriptService->generateServices($router);
                $suffix = '-services';
                break;
            default:
                $script = $this->scriptService->generate($router);
                $suffix = '-full';
        }

        $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $router->name)) . $suffix . '.rsc';
        return response($script, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function provisionStatus(Router $router)
    {
        $router->refresh();

        $ip     = $router->vpn_ip ?: $router->wan_ip;
        $online = false;

        if ($ip) {
            try {
                $apiPort = $router->api_port ?? 80;
                $apiUser = $router->api_username ?? 'admin';
                $apiPass = $router->api_password ?? '';

                $response = Http::withBasicAuth($apiUser, $apiPass)
                    ->timeout(3)
                    ->withoutVerifying()
                    ->get("http://{$ip}:{$apiPort}/rest/system/identity");
                $online = $response->successful();
            } catch (\Exception $e) {
                $online = false;
            }
        }

        return response()->json([
            'provision_phase' => (int) ($router->provision_phase ?? 0),
            'vpn_ip'          => $router->vpn_ip,
            'wan_ip'          => $router->wan_ip,
            'online'          => $online,
            'last_heartbeat'  => $router->last_heartbeat_at?->diffForHumans(),
        ]);
    }

    public function provision(string $token)
    {
        $router = Router::where('ref_code', $token)
                        ->where('is_active', true)
                        ->firstOrFail();

        $script   = app(MikrotikScriptService::class)->generate($router);
        $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $router->name)) . '-mikrotik.rsc';

        return response($script, 200, [
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function serveHotspotFile(Router $router, string $file)
    {
        $allowedFiles = [
            'login.html'    => ['view' => 'hotspot.login',    'type' => 'text/html; charset=utf-8'],
            'alogin.html'   => ['view' => 'hotspot.alogin',   'type' => 'text/html; charset=utf-8'],
            'status.html'   => ['view' => 'hotspot.status',   'type' => 'text/html; charset=utf-8'],
            'logout.html'   => ['view' => 'hotspot.logout',   'type' => 'text/html; charset=utf-8'],
            'redirect.html' => ['view' => 'hotspot.redirect', 'type' => 'text/html; charset=utf-8'],
            'error.html'    => ['view' => 'hotspot.error',    'type' => 'text/html; charset=utf-8'],
            'flogin.html'   => ['view' => 'hotspot.flogin',   'type' => 'text/html; charset=utf-8'],
            'fstatus.html'  => ['view' => 'hotspot.fstatus',  'type' => 'text/html; charset=utf-8'],
            'flogout.html'  => ['view' => 'hotspot.flogout',  'type' => 'text/html; charset=utf-8'],
            'rlogin.html'   => ['view' => 'hotspot.rlogin',   'type' => 'text/html; charset=utf-8'],
            'rstatus.html'  => ['view' => 'hotspot.rstatus',  'type' => 'text/html; charset=utf-8'],
            'radvert.html'  => ['view' => 'hotspot.radvert',  'type' => 'text/html; charset=utf-8'],
            'md5.js'        => ['view' => 'hotspot.md5',      'type' => 'application/javascript'],
            'errors.txt'    => ['view' => 'hotspot.errors',   'type' => 'text/plain; charset=utf-8'],
            'api.json'      => ['view' => 'hotspot.api_json', 'type' => 'application/json'],
        ];

        if (!array_key_exists($file, $allowedFiles)) {
            abort(404);
        }

        $entry    = $allowedFiles[$file];
        $viewName = $entry['view'];

        if (!view()->exists($viewName)) {
            abort(404);
        }

        $parsedHost    = parse_url(config('app.url'), PHP_URL_HOST);
        $billingDomain = $router->billing_domain ?: ($parsedHost ?: 'localhost');
        $appName       = config('app.name', 'iNettotik');

        return response(
            view($viewName, compact('router', 'billingDomain', 'appName'))->render(),
            200,
            ['Content-Type' => $entry['type']]
        );
    }

    public function serveHotspotFileByRefCode(string $refCode, string $file)
    {
        $router = Router::where('ref_code', $refCode)
                        ->where('is_active', true)
                        ->firstOrFail();

        return $this->serveHotspotFile($router, $file);
    }

    public function serveCertFile(string $refCode, string $file)
    {
        $allowedFiles = ['ca.crt', 'router.crt', 'router.key'];

        if (!in_array($file, $allowedFiles, true)) {
            abort(404);
        }

        $router = Router::where('ref_code', $refCode)
                        ->where('is_active', true)
                        ->firstOrFail();

        if ($file === 'ca.crt') {
            $filename = basename($router->ca_cert_filename ?? 'ca.crt');
        } elseif ($file === 'router.crt') {
            $filename = basename($router->router_cert_filename ?? 'router.crt');
        } else {
            // router.key — derive from the cert filename
            $certFilename = $router->router_cert_filename ?? 'router.crt';
            $filename = basename(pathinfo($certFilename, PATHINFO_FILENAME) . '.key');
        }

        $path = storage_path('app/certs/' . $filename);

        if (!is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function downloadHotspotFiles(Router $router)
    {
        $tmpDir  = sys_get_temp_dir();
        $zipPath = $tmpDir . '/hotspot-' . $router->id . '-' . time() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create ZIP file.');
        }

        $billingDomain = $router->billing_domain ?: parse_url(config('app.url'), PHP_URL_HOST);
        $appName       = config('app.name', 'iNettotik');

        $zip->addFromString('login.html',    view('hotspot.login',    compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('alogin.html',   view('hotspot.alogin',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('status.html',   view('hotspot.status',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('logout.html',   view('hotspot.logout',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('redirect.html', view('hotspot.redirect', compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('error.html',    view('hotspot.error',    compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('flogin.html',   view('hotspot.flogin',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('fstatus.html',  view('hotspot.fstatus',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('flogout.html',  view('hotspot.flogout',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('rlogin.html',   view('hotspot.rlogin',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('rstatus.html',  view('hotspot.rstatus',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('radvert.html',  view('hotspot.radvert',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('md5.js',        view('hotspot.md5',      compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('errors.txt',    view('hotspot.errors',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('api.json',      view('hotspot.api_json', compact('router', 'billingDomain', 'appName'))->render());
        $zip->close();

        return response()->download($zipPath, "hotspot-files-{$router->id}.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Test connection to a router via its VPN IP using RouterOS REST API.
     *
     * Uses port 8728 (RouterOS API) not 80.
     * Also fetches MAC address from /interface/ethernet for WinBox display.
     * Returns online/offline status based on whether the API responds.
     */
    public function testConnection(Router $router)
    {
        $result = [
            'api_reachable'     => false,
            'radius_configured' => false,
            'online'            => false,
            'router_identity'   => null,
            'uptime'            => null,
            'version'           => null,
            'mac_address'       => null,
            'board_name'        => null,
            'cpu_load'          => null,
            'free_memory'       => null,
            'error'             => null,
        ];

        // Check RADIUS/NAS table — use vpn_ip (with fallback to wan_ip) to match syncNas
        $nasIp = $router->vpn_ip ?: $router->wan_ip;
        $result['radius_configured'] = $nasIp ? Nas::where('nasname', $nasIp)->exists() : false;

        // Prefer VPN IP (OpenVPN tunnel) over WAN IP
        $ip      = $router->vpn_ip ?: $router->wan_ip;
        // RouterOS REST API runs on port 80 by default but we enable it on 8728
        // The script sets: /ip service set api port=8728
        // REST API on RouterOS 7.x runs on /rest/* via www service (port 80)
        // or dedicated api-ssl (443). We use the plain API port 8728 with the
        // RouterOS REST API which is available on the www port (80) in ROS 7.x.
        // Use port 80 for REST, 8728 for raw API. REST API is on www (80/443).
        $apiPort = $router->api_port ?? 80;
        $apiUser = $router->api_username ?? 'admin';
        $apiPass = $router->api_password ?? '';

        if (!$ip) {
            $result['error'] = 'No IP address configured for this router. Run the provision script first.';
            return response()->json($result);
        }

        try {
            // RouterOS 7.x REST API is available on the www service port (default 80)
            // at /rest/* endpoints. We try port 80 first, then 443.
            $baseUrl  = "http://{$ip}:{$apiPort}";
            $http     = Http::withBasicAuth($apiUser, $apiPass)
                            ->timeout(5)
                            ->withoutVerifying(); // skip SSL for VPN connections

            // Fetch system resource
            $response = $http->get("{$baseUrl}/rest/system/resource");

            if ($response->successful()) {
                $data = $response->json();
                $result['api_reachable']   = true;
                $result['online']          = true;
                $result['board_name']      = $data['board-name']   ?? null;
                $result['uptime']          = $data['uptime']        ?? null;
                $result['version']         = $data['version']       ?? null;
                $result['cpu_load']        = isset($data['cpu-load']) ? $data['cpu-load'] . '%' : null;
                $result['free_memory']     = isset($data['free-memory'])
                                             ? round($data['free-memory'] / 1048576, 1) . ' MiB free'
                                             : null;

                // Fetch router identity
                $identResp = $http->get("{$baseUrl}/rest/system/identity");
                if ($identResp->successful()) {
                    $result['router_identity'] = $identResp->json()['name'] ?? null;
                }

                // Fetch MAC address of the WAN interface for WinBox column
                $wanIface  = $router->wan_interface ?: 'ether1';
                $ifResp    = $http->get("{$baseUrl}/rest/interface", [
                    'name' => $wanIface,
                ]);
                if ($ifResp->successful()) {
                    $interfaces = $ifResp->json();
                    if (!empty($interfaces)) {
                        $iface = is_array($interfaces[0] ?? null) ? $interfaces[0] : $interfaces;
                        $result['mac_address'] = $iface['mac-address'] ?? null;
                    }
                }

                // If MAC found, save it to the router record for persistent display
                if ($result['mac_address'] && $router->mac_address !== $result['mac_address']) {
                    $router->update(['mac_address' => $result['mac_address']]);
                }

                // Save board name and version back to DB if changed
                $updates = [];
                if ($result['board_name'] && $router->model !== $result['board_name']) {
                    $updates['model'] = $result['board_name'];
                }
                if ($result['version'] && $router->routeros_version !== $result['version']) {
                    $updates['routeros_version'] = $result['version'];
                }
                if (!empty($updates)) {
                    $router->update($updates);
                }

            } else {
                $result['error'] = 'Router API responded with HTTP ' . $response->status()
                                 . '. Check credentials or ensure the www service is enabled on the router.';
            }
        } catch (\Exception $e) {
            $result['online'] = false;
            $result['error']  = 'Could not connect: ' . $e->getMessage();
        }

        return response()->json($result);
    }

    public function configureRouter(Request $request, Router $router)
    {
        $step = (int) $request->input('step');
        if (!in_array($step, [1, 2, 3, 4, 5], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid configure step.',
            ], 422);
        }

        Log::info('Router configure attempt started', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'step' => $step,
        ]);

        try {
            switch ($step) {
                case 1:
                    if ($router->isHotspot()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Hotspot mode — API steps skipped',
                            'data' => ['skipped' => true],
                        ]);
                    }

                    if ($router->isVpn() && !$router->vpn_ip) {
                        return response()->json([
                            'success' => false,
                            'message' => 'VPN tunnel not established yet',
                        ]);
                    }

                    $api = RouterOSApiService::fromRouter($router);
                    $api->connect();
                    $systemInfo = $api->getSystemInfo();

                    $updates = [];
                    if (!empty($systemInfo['board-name']) && $router->model !== $systemInfo['board-name']) {
                        $updates['model'] = $systemInfo['board-name'];
                    }
                    if (!empty($systemInfo['version']) && $router->routeros_version !== $systemInfo['version']) {
                        $updates['routeros_version'] = $systemInfo['version'];
                    }
                    if (!empty($updates)) {
                        $router->update($updates);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Connected to MikroTik API successfully',
                        'data' => ['system_info' => $systemInfo],
                    ]);

                case 2:
                    $nasIp = $router->getNasIp();
                    if (!$nasIp) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No NAS IP available for this router',
                        ]);
                    }

                    Nas::updateOrCreate(
                        ['nasname' => $nasIp],
                        [
                            'shortname' => $router->name,
                            'secret' => $router->radius_secret,
                            'type' => 'other',
                        ]
                    );

                    return response()->json([
                        'success' => true,
                        'message' => 'NAS entry registered in FreeRADIUS',
                        'data' => ['nas_ip' => $nasIp],
                    ]);

                case 3:
                    if ($router->isHotspot()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Skipped — Hotspot mode',
                            'data' => ['skipped' => true],
                        ]);
                    }

                    $api = RouterOSApiService::fromRouter($router);
                    $api->connect();

                    $billingServerIp = (string) ($router->billing_server_vpn_ip ?: config('app.billing_server_ip', '127.0.0.1'));
                    $radiusRows = $api->sendCommand('/radius/print');
                    $radiusId = null;

                    foreach ($radiusRows as $row) {
                        if (($row['address'] ?? null) === $billingServerIp) {
                            $radiusId = $row['.id'] ?? $row['id'] ?? null;
                            break;
                        }
                    }

                    $radiusArgs = [
                        'service' => 'ppp,hotspot',
                        'address' => $billingServerIp,
                        'secret' => $router->radius_secret,
                    ];

                    if ($radiusId) {
                        $api->sendCommand('/radius/set', array_merge(['numbers' => (string) $radiusId], $radiusArgs));
                    } else {
                        $api->sendCommand('/radius/add', $radiusArgs);
                    }

                    $api->sendCommand('/radius/incoming/set', [
                        'accept' => 'yes',
                        'port' => '3799',
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'RADIUS configuration pushed to MikroTik',
                    ]);

                case 4:
                    if ($router->isHotspot()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Skipped — Hotspot mode',
                            'data' => ['skipped' => true],
                        ]);
                    }

                    $api = RouterOSApiService::fromRouter($router);
                    $api->connect();

                    $profileRows = $api->sendCommand('/ppp/profile/print');
                    $profileId = null;
                    foreach ($profileRows as $row) {
                        if (($row['name'] ?? null) === 'pppoe-profile') {
                            $profileId = $row['.id'] ?? $row['id'] ?? null;
                            break;
                        }
                    }

                    $profileArgs = [
                        'name' => 'pppoe-profile',
                        'local-address' => $router->pppoe_gateway_ip ?: '19.225.0.1',
                        'use-radius' => 'yes',
                        'only-one' => 'yes',
                    ];

                    if ($profileId) {
                        $api->sendCommand('/ppp/profile/set', array_merge(['numbers' => (string) $profileId], $profileArgs));
                    } else {
                        $api->sendCommand('/ppp/profile/add', $profileArgs);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'PPPoE server profile configured',
                    ]);

                case 5:
                    $router->update([
                        'provision_phase' => 3,
                        'last_heartbeat_at' => now(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Provisioning completed successfully',
                    ]);
            }
        } catch (\Throwable $e) {
            Log::error('Router configure step failed', [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'step' => $step,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Quick online check for the index table status column.
     * Lighter than full testConnection — just pings the REST API.
     */
    public function pingStatus(Router $router)
    {
        $ip      = $router->vpn_ip ?: $router->wan_ip;
        $apiPort = $router->api_port ?? 80;
        $apiUser = $router->api_username ?? 'admin';
        $apiPass = $router->api_password ?? '';

        if (!$ip) {
            return response()->json(['online' => false]);
        }

        try {
            $response = Http::withBasicAuth($apiUser, $apiPass)
                            ->timeout(3)
                            ->withoutVerifying()
                            ->get("http://{$ip}:{$apiPort}/rest/system/identity");

            return response()->json([
                'online'   => $response->successful(),
                'identity' => $response->successful() ? ($response->json()['name'] ?? null) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['online' => false]);
        }
    }

    protected function syncNas(Router $router): void
    {
        $nasIp = $router->vpn_ip ?: $router->wan_ip;
        if (!$nasIp) {
            return;
        }
        Nas::updateOrCreate(
            ['nasname' => $nasIp],
            [
                'shortname'   => $router->name,
                'type'        => 'mikrotik',
                'secret'      => $router->radius_secret,
                'description' => $router->name . ' - MikroTik',
            ]
        );
    }
}
