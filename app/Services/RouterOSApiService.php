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

                foreach ($response->getIterator() as $key => $value) {
                    $result[$key] = $value;
                }
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
            $board = $this->sendCommand('/system/routerboard/print');

            return [
                'board-name' => $resource['board-name'] ?? null,
                'version' => $resource['version'] ?? null,
                'cpu' => $resource['cpu'] ?? null,
                'architecture-name' => $resource['architecture-name'] ?? null,
                'serial-number' => $board['serial-number'] ?? null,
                'uptime' => $resource['uptime'] ?? null,
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

        return new self(
            host: (string) ($router->vpn_ip ?: $router->wan_ip ?: ''),
            port: (int) ($router->api_port ?? 8728),
            username: (string) ($router->api_username ?? 'admin'),
            password: $password
        );
    }
}
