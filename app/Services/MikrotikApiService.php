<?php
namespace App\Services;

use App\Models\Router;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MikrotikApiService
{
    protected Router $router;
    protected string $baseUrl;
    protected array $auth;

    public function init(Router $router): self
    {
        $this->router  = $router;
        $ip            = $router->vpn_ip ?? throw new \RuntimeException('No VPN IP configured — refusing insecure WAN connection');
        $port          = $router->api_port ?? 80;
        $apiPass = '';
        if ($router->api_password) {
            try {
                $apiPass = decrypt($router->api_password);
            } catch (\Exception $e) {
                $apiPass = $router->api_password; // fallback if not encrypted
            }
        }
        $this->baseUrl = "http://{$ip}:{$port}/rest";
        $this->auth    = [
            $router->api_username ?? 'admin',
            $apiPass,
        ];
        return $this;
    }

    /**
     * Get system resource info (CPU, RAM, uptime, version).
     */
    public function getSystemResource(): array
    {
        return $this->request('GET', '/system/resource');
    }

    /**
     * Get system health (temperature, voltage).
     */
    public function getSystemHealth(): array
    {
        return $this->request('GET', '/system/health');
    }

    /**
     * Get router board info (model, serial).
     */
    public function getRouterBoard(): array
    {
        return $this->request('GET', '/system/routerboard');
    }

    /**
     * Get list of interfaces with stats.
     */
    public function getInterfaces(): array
    {
        return $this->request('GET', '/interface');
    }

    /**
     * Get traffic stats for a specific interface.
     */
    public function getInterfaceTraffic(string $interface): array
    {
        return $this->request('GET', '/interface/monitor-traffic', [
            'interface' => $interface,
            'once'      => '',
        ]);
    }

    /**
     * Get count of active PPPoE and Hotspot users.
     */
    public function getActiveUsers(): array
    {
        $pppoe   = $this->request('GET', '/ppp/active');
        $hotspot = $this->request('GET', '/ip/hotspot/active');

        return [
            'pppoe'   => is_array($pppoe)   ? count($pppoe)   : 0,
            'hotspot' => is_array($hotspot) ? count($hotspot) : 0,
        ];
    }

    /**
     * Get detailed active PPP sessions.
     */
    public function getActivePppSessions(): array
    {
        return $this->request('GET', '/ppp/active');
    }

    /**
     * Get detailed active Hotspot sessions.
     */
    public function getActiveHotspotSessions(): array
    {
        return $this->request('GET', '/ip/hotspot/active');
    }

    // ── PPPoE Secret Management ───────────────────────────────────────────────

    /**
     * Add a PPPoE secret (subscriber account) to the router.
     */
    public function addPppSecret(string $username, string $password, string $profile = 'pppoe-profile', string $remoteAddress = ''): array
    {
        $body = ['name' => $username, 'password' => $password, 'profile' => $profile, 'service' => 'pppoe'];
        if ($remoteAddress !== '') {
            $body['remote-address'] = $remoteAddress;
        }
        return $this->request('PUT', '/ppp/secret', [], $body);
    }

    /**
     * Remove a PPPoE secret by username.
     */
    public function removePppSecret(string $username): array
    {
        $existing = $this->request('GET', '/ppp/secret', ['name' => $username]);
        if (empty($existing) || empty($existing[0]['.id'])) {
            return [];
        }
        $id = $existing[0]['.id'];
        return $this->request('DELETE', "/ppp/secret/{$id}");
    }

    /**
     * Disconnect an active PPPoE session by username.
     */
    public function disconnectPppSession(string $username): array
    {
        $sessions = $this->request('GET', '/ppp/active', ['name' => $username]);
        if (empty($sessions) || empty($sessions[0]['.id'])) {
            return [];
        }
        $id = $sessions[0]['.id'];
        return $this->request('POST', '/ppp/active/remove', [], ['.id' => $id]);
    }

    // ── Hotspot User Management ───────────────────────────────────────────────

    /**
     * Add a Hotspot user.
     */
    public function addHotspotUser(string $username, string $password, string $profile = 'default', string $server = 'all'): array
    {
        return $this->request('PUT', '/ip/hotspot/user', [], [
            'name'     => $username,
            'password' => $password,
            'profile'  => $profile,
            'server'   => $server,
        ]);
    }

    /**
     * Remove a Hotspot user by username.
     */
    public function removeHotspotUser(string $username): array
    {
        $existing = $this->request('GET', '/ip/hotspot/user', ['name' => $username]);
        if (empty($existing) || empty($existing[0]['.id'])) {
            return [];
        }
        $id = $existing[0]['.id'];
        return $this->request('DELETE', "/ip/hotspot/user/{$id}");
    }

    /**
     * Disconnect an active Hotspot session by username.
     */
    public function disconnectHotspotSession(string $username): array
    {
        $sessions = $this->request('GET', '/ip/hotspot/active', ['user' => $username]);
        if (empty($sessions) || empty($sessions[0]['.id'])) {
            return [];
        }
        $id = $sessions[0]['.id'];
        return $this->request('POST', '/ip/hotspot/active/remove', [], ['.id' => $id]);
    }

    // ── Address List Management ───────────────────────────────────────────────

    /**
     * Add or update an address-list entry (e.g. mark expired subscribers).
     */
    public function updateAddressList(string $list, string $address, string $comment = '', string $timeout = ''): array
    {
        $body = ['list' => $list, 'address' => $address];
        if ($comment !== '') {
            $body['comment'] = $comment;
        }
        if ($timeout !== '') {
            $body['timeout'] = $timeout;
        }
        return $this->request('PUT', '/ip/firewall/address-list', [], $body);
    }

    /**
     * Remove an address-list entry.
     */
    public function removeAddressListEntry(string $list, string $address): array
    {
        $existing = $this->request('GET', '/ip/firewall/address-list', ['list' => $list, 'address' => $address]);
        if (empty($existing) || empty($existing[0]['.id'])) {
            return [];
        }
        $id = $existing[0]['.id'];
        return $this->request('DELETE', "/ip/firewall/address-list/{$id}");
    }

    // ── Firewall Rule Management ──────────────────────────────────────────────

    /**
     * Add a firewall filter rule.
     */
    public function addFirewallRule(string $chain, string $action, string $srcAddress = '', string $comment = ''): array
    {
        $body = ['chain' => $chain, 'action' => $action];
        if ($srcAddress !== '') {
            $body['src-address'] = $srcAddress;
        }
        if ($comment !== '') {
            $body['comment'] = $comment;
        }
        return $this->request('PUT', '/ip/firewall/filter', [], $body);
    }

    /**
     * Remove a firewall filter rule by its .id.
     */
    public function removeFirewallRule(string $id): array
    {
        return $this->request('DELETE', "/ip/firewall/filter/{$id}");
    }

    // ── Queue / Bandwidth Management ─────────────────────────────────────────

    /**
     * Create or update a Simple Queue for bandwidth management.
     */
    public function setQueueSimple(string $name, string $target, string $maxLimit, string $burstLimit = '', string $burstThreshold = '', string $burstTime = ''): array
    {
        $existing = $this->request('GET', '/queue/simple', ['name' => $name]);

        $body = ['name' => $name, 'target' => $target, 'max-limit' => $maxLimit];
        if ($burstLimit !== '')     { $body['burst-limit']     = $burstLimit; }
        if ($burstThreshold !== '') { $body['burst-threshold']  = $burstThreshold; }
        if ($burstTime !== '')      { $body['burst-time']       = $burstTime; }

        if (!empty($existing) && !empty($existing[0]['.id'])) {
            $id = $existing[0]['.id'];
            return $this->request('PATCH', "/queue/simple/{$id}", [], $body);
        }

        return $this->request('PUT', '/queue/simple', [], $body);
    }

    /**
     * Remove a Simple Queue by name.
     */
    public function removeQueueSimple(string $name): array
    {
        $existing = $this->request('GET', '/queue/simple', ['name' => $name]);
        if (empty($existing) || empty($existing[0]['.id'])) {
            return [];
        }
        $id = $existing[0]['.id'];
        return $this->request('DELETE', "/queue/simple/{$id}");
    }

    /**
     * List all Simple Queues.
     */
    public function getQueueSimple(): array
    {
        return $this->request('GET', '/queue/simple');
    }

    // ── Internal HTTP request ─────────────────────────────────────────────────

    /**
     * Make an HTTP request to the MikroTik REST API with retry/backoff.
     * Falls back to PEAR2 API library on connection failure if available.
     *
     * @param  string  $method  GET | PUT | POST | PATCH | DELETE
     * @param  string  $path    REST path, e.g. '/ppp/secret'
     * @param  array   $query   Query-string parameters (GET only)
     * @param  array   $body    JSON body for PUT/POST/PATCH
     * @param  int     $retries Max retry attempts
     */
    protected function request(string $method, string $path, array $query = [], array $body = [], int $retries = 2): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt <= $retries) {
            try {
                $http = Http::timeout(5)->withBasicAuth(...$this->auth);

                $url = $this->baseUrl . $path;

                switch (strtoupper($method)) {
                    case 'GET':
                        $response = $http->get($url, $query);
                        break;
                    case 'PUT':
                        $response = $http->withHeaders(['Content-Type' => 'application/json'])->put($url, $body);
                        break;
                    case 'POST':
                        $response = $http->withHeaders(['Content-Type' => 'application/json'])->post($url, $body);
                        break;
                    case 'PATCH':
                        $response = $http->withHeaders(['Content-Type' => 'application/json'])->patch($url, $body);
                        break;
                    case 'DELETE':
                        $response = $http->delete($url);
                        break;
                    default:
                        $response = $http->get($url, $query);
                }

                if ($response->successful()) {
                    $json = $response->json();
                    return is_array($json) ? $json : [];
                }

                // 404 on DELETE is acceptable (already gone)
                if (strtoupper($method) === 'DELETE' && $response->status() === 404) {
                    return [];
                }

                Log::warning('MikroTik API non-200', [
                    'router' => $this->router->name ?? 'unknown',
                    'method' => $method,
                    'path'   => $path,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];

            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;
                if ($attempt <= $retries) {
                    // Exponential backoff: 200 ms, 400 ms, …
                    usleep(200000 * (2 ** ($attempt - 1)));
                    continue;
                }
            }
        }

        Log::error('MikroTik API error (all retries exhausted)', [
            'router' => $this->router->name ?? 'unknown',
            'method' => $method,
            'path'   => $path,
            'error'  => $lastError ? $lastError->getMessage() : 'unknown',
        ]);

        // Attempt PEAR2 fallback for read operations only
        if (strtoupper($method) === 'GET') {
            return $this->pear2Fallback($path, $query);
        }

        return [];
    }

    /**
     * PEAR2 RouterOS API fallback (port 8728 by default) for GET operations when the
     * REST API is unreachable (e.g. HTTP service disabled on the router).
     */
    protected function pear2Fallback(string $path, array $query = []): array
    {
        if (!class_exists(\PEAR2\Net\RouterOS\Client::class)) {
            Log::warning('MikroTik PEAR2 fallback skipped: pear2/net_routeros library not installed.', [
                'router' => $this->router->name ?? 'unknown',
                'path'   => $path,
                'hint'   => 'Run: composer require pear2/net_routeros',
            ]);
            return [];
        }

        try {
            $ip   = $this->router->vpn_ip ?? null;
            $user = $this->router->api_username ?? 'admin';
            $pass = '';
            if ($this->router->api_password) {
                try {
                    $pass = decrypt($this->router->api_password);
                } catch (\Exception $e) {
                    $pass = $this->router->api_password;
                }
            }

            if (!$ip) {
                return [];
            }

            // Convert REST path to RouterOS API command (e.g. /ppp/active → /ppp/active/print)
            $command = '/' . ltrim($path, '/') . '/print';

            $binaryApiPort = (int) ($this->router->api_port ?? 8728);
            $client   = new \PEAR2\Net\RouterOS\Client($ip, $user, $pass, $binaryApiPort);
            $request  = new \PEAR2\Net\RouterOS\Request($command);

            foreach ($query as $key => $value) {
                $request->setArgument($key, (string) $value);
            }

            $responses = $client->sendSync($request);
            $result    = [];
            foreach ($responses as $response) {
                if ($response->getType() === \PEAR2\Net\RouterOS\Response::TYPE_DATA) {
                    $item = [];
                    foreach ($response as $key => $value) {
                        $item[$key] = $value;
                    }
                    $result[] = $item;
                }
            }
            return $result;

        } catch (\Exception $e) {
            Log::error('MikroTik PEAR2 fallback error', [
                'router' => $this->router->name ?? 'unknown',
                'path'   => $path,
                'error'  => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Check if router is reachable.
     */
    public function isOnline(): bool
    {
        $res = $this->getSystemResource();
        return !empty($res);
    }
}