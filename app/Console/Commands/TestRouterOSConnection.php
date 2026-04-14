<?php

namespace App\Console\Commands;

use App\Services\RouterOSApiService;
use Illuminate\Console\Command;

class TestRouterOSConnection extends Command
{
    protected $signature = 'routeros:test {host} {--port=8728} {--username=admin} {--password=}';

    protected $description = 'Test PEAR2/Net_RouterOS connection to a MikroTik router';

    public function handle(): int
    {
        $service = new RouterOSApiService(
            host: (string) $this->argument('host'),
            port: (int) $this->option('port'),
            username: (string) $this->option('username'),
            password: (string) $this->option('password')
        );

        try {
            $service->connect();
            $systemInfo = $service->getSystemInfo();

            if (empty($systemInfo)) {
                $this->error('Connected, but failed to fetch system information.');
                return self::FAILURE;
            }

            $rows = [];
            foreach ($systemInfo as $key => $value) {
                $rows[] = [$key, (string) ($value ?? 'N/A')];
            }

            $this->table(['Field', 'Value'], $rows);
            $this->info('RouterOS connection test successful.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('RouterOS connection test failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            $service->disconnect();
        }
    }
}
