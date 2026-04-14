<?php

namespace App\Console\Commands;

use App\Services\RouterOSApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPear2Connection extends Command
{
    protected $signature = 'pear2:test {host} {--port=8728} {--username=admin} {--password=}';

    protected $description = 'Test PEAR2 RouterOS API connection to a MikroTik router';

    public function handle(): int
    {
        if (!class_exists(\PEAR2\Net\RouterOS\Client::class)) {
            $this->error('PEAR2\\Net\\RouterOS\\Client class is not available.');

            return self::FAILURE;
        }

        $service = new RouterOSApiService(
            host: (string) $this->argument('host'),
            port: (int) $this->option('port'),
            username: (string) $this->option('username'),
            password: (string) $this->option('password')
        );

        try {
            $service->connect();
            $systemInfo = $service->getSystemInfo();

            $this->table(
                ['board-name', 'version', 'cpu', 'architecture', 'serial', 'uptime'],
                [[
                    (string) ($systemInfo['board-name'] ?? 'N/A'),
                    (string) ($systemInfo['version'] ?? 'N/A'),
                    (string) ($systemInfo['cpu'] ?? 'N/A'),
                    (string) ($systemInfo['architecture-name'] ?? 'N/A'),
                    (string) ($systemInfo['serial-number'] ?? 'N/A'),
                    (string) ($systemInfo['uptime'] ?? 'N/A'),
                ]]
            );

            $this->info('PEAR2 RouterOS connection test successful.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('PEAR2 RouterOS connection test failed', [
                'host' => (string) $this->argument('host'),
                'port' => (int) $this->option('port'),
                'username' => (string) $this->option('username'),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            $this->error('PEAR2 RouterOS connection test failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            $service->disconnect();
        }
    }
}
