<?php

namespace App\Services;

use App\Models\Router;
use Illuminate\Support\Facades\Log;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Request;
use PEAR2\Net\RouterOS\Response;

class RouterOSApiService
{
    protected ?Client $client = null;
    protected bool $connected = false;

    public function __construct(
        protected string $host,
        protected int $port = 8728,
        protected string $username = 'admin',
        protected string $password = ''
    ) {}

    public function connect(): self
    {
        try {
            $this->client = new Client($this->host, $this->username, $this->password, $this->port);
            $this->connected = true;

            return $this;
        } catch (\Throwable $e) {
            Log::error('RouterOSApiService: connect failed', [
                'host' => $this->host,
                'port' => $this->port,
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                sprintf('Failed to connect to RouterOS API at %s:%d. %s', $this->host, $this->port, $e->getMessage()),
                0,
                $e
            );
        }
    }

    public function disconnect(): void
    {
        $this->client = null;
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->client !== null;
    }

    public function sendCommand(string $command, array $args = []): array
    {
        if (!$this->isConnected()) {
            Log::error('RouterOSApiService: sendCommand called while disconnected', [
                'host' => $this->host,
                'command' => $command,
            ]);

            throw new \RuntimeException('RouterOS API client is not connected. Call connect() first.');
        }

        try {
            $request = new Request($command);
            foreach ($args as $key => $value) {
                $request->setArgument((string) $key, $value);
            }

            $responses = $this->client->sendSync($request);
            $result = [];

            foreach ($responses as $response) {
                if ($response->getType() !== Response::TYPE_DATA) {
                    continue;
                }

                $row = [];
                foreach ($response->getIterator() as $key => $value) {
                    $row[$key] = $value;
                }

                $result[] = $row;
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('RouterOSApiService: sendCommand failed', [
                'host' => $this->host,
                'port' => $this->port,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                sprintf('Failed to execute RouterOS command %s. %s', $command, $e->getMessage()),
                0,
                $e
            );
        }
    }

    public function getSystemInfo(): array
    {
        try {
            $resource = $this->sendCommand('/system/resource/print');
        } catch (\Throwable $e) {
            Log::error('RouterOSApiService: getSystemInfo resource query failed', [
                'host' => $this->host,
                'port' => $this->port,
                'command' => '/system/resource/print',
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        try {
            $board = $this->sendCommand('/system/routerboard/print');
        } catch (\Throwable $e) {
            Log::error('RouterOSApiService: getSystemInfo routerboard query failed', [
                'host' => $this->host,
                'port' => $this->port,
                'command' => '/system/routerboard/print',
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        try {
            $resourceRow = $resource[0] ?? [];
            $boardRow = $board[0] ?? [];

            return [
                'board-name' => $resourceRow['board-name'] ?? null,
                'version' => $resourceRow['version'] ?? null,
                'cpu' => $resourceRow['cpu'] ?? null,
                'architecture-name' => $resourceRow['architecture-name'] ?? null,
                'serial-number' => $boardRow['serial-number'] ?? null,
                'uptime' => $resourceRow['uptime'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('RouterOSApiService: getSystemInfo failed', [
                'host' => $this->host,
                'port' => $this->port,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function sendCommandMulti(string $command, array $args = []): array
    {
        return $this->sendCommand($command, $args);
    }

    public function getIdentity(): string
    {
        $identity = $this->sendCommand('/system/identity/print');

        return (string) ($identity[0]['name'] ?? '');
    }

    public function getIpPools(): array
    {
        return $this->sendCommand('/ip/pool/print');
    }

    public function addIpPool(string $name, string $ranges): array
    {
        return $this->sendCommand('/ip/pool/add', [
            'name' => $name,
            'ranges' => $ranges,
        ]);
    }

    public function removeIpPool(string $name): array
    {
        $pools = $this->getIpPools();

        foreach ($pools as $pool) {
            if (($pool['name'] ?? null) !== $name) {
                continue;
            }

            return $this->sendCommand('/ip/pool/remove', [
                'numbers' => $pool['.id'] ?? $pool['id'] ?? $name,
            ]);
        }

        return [];
    }

    public static function fromRouter(Router $router): self
    {
        $password = '';

        if (!empty($router->api_password)) {
            try {
                $password = decrypt($router->api_password);
            } catch (\Throwable) {
                $password = (string) $router->api_password;
            }
        }

        $host = (string) ($router->vpn_ip ?: $router->wan_ip ?: '');

        if ($host === '') {
            Log::error('RouterOSApiService: fromRouter failed, missing host', [
                'router_id' => $router->id,
                'router_name' => $router->name,
            ]);

            throw new \RuntimeException('Router does not have a valid host (vpn_ip or wan_ip).');
        }

        return new self(
            host: $host,
            port: (int) ($router->api_port ?? 8728),
            username: (string) ($router->api_username ?? 'admin'),
            password: $password
        );
    }
}
