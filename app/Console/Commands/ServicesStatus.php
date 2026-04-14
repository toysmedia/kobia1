<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ServicesStatus extends Command
{
    protected const SUPERVISORCTL_BIN = '/usr/local/bin/supervisorctl';
    protected const SYSTEMCTL_BIN = '/bin/systemctl';

    protected $signature = 'services:status';

    protected $description = 'Get OpenVPN, FreeRADIUS, and Supervisor service status as JSON';

    public function handle(): int
    {
        $services = [
            $this->buildServiceStatus(
                key: 'openvpn',
                serviceName: 'OpenVPN',
                systemdService: 'openvpn-server',
                versionCommand: 'openvpn --version 2>&1 | head -1',
                configPath: '/etc/openvpn/server.conf',
                supervisorProgram: 'openvpn'
            ),
            $this->buildServiceStatus(
                key: 'freeradius',
                serviceName: 'FreeRADIUS',
                systemdService: 'freeradius',
                versionCommand: '/usr/local/sbin/radiusd -v 2>&1 | head -1 || freeradius -v 2>&1 | head -1',
                configPath: '/usr/local/etc/raddb/radiusd.conf',
                supervisorProgram: 'freeradius'
            ),
            $this->buildServiceStatus(
                key: 'supervisor',
                serviceName: 'Supervisor',
                systemdService: 'supervisord',
                versionCommand: 'supervisord --version 2>&1',
                configPath: '/etc/supervisor/supervisord.conf'
            ),
        ];

        $this->output->write(json_encode([
            'services' => $services,
            'timestamp' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }

    protected function buildServiceStatus(
        string $key,
        string $serviceName,
        string $systemdService,
        string $versionCommand,
        string $configPath,
        ?string $supervisorProgram = null
    ): array {
        $version = $this->runCommand($versionCommand)['output'] ?: 'Unknown';
        $status = [
            'running' => false,
            'pid' => null,
            'uptime' => null,
            'source' => 'systemd',
        ];

        if ($supervisorProgram) {
            $supervisorStatus = $this->parseSupervisorStatus($supervisorProgram);
            if ($supervisorStatus !== null) {
                $status = array_merge($status, $supervisorStatus, ['source' => 'supervisor']);
            } else {
                $status = array_merge($status, $this->getSystemdStatus($systemdService));
            }
        } else {
            $status = array_merge($status, $this->getSystemdStatus($systemdService));
        }

        return [
            'key' => $key,
            'name' => $serviceName,
            'version' => $version,
            'running' => (bool) $status['running'],
            'pid' => $status['pid'],
            'uptime' => $status['uptime'],
            'config_path' => $configPath,
            'source' => $status['source'],
        ];
    }

    protected function parseSupervisorStatus(string $program): ?array
    {
        $result = $this->runCommand(self::SUPERVISORCTL_BIN . ' status ' . escapeshellarg($program) . ' 2>&1');
        if ($result['exit_code'] !== 0 || $result['output'] === '') {
            return null;
        }

        $output = $result['output'];
        if (!preg_match('/\b(RUNNING|STOPPED|FATAL|BACKOFF|EXITED|STARTING)\b/', $output, $stateMatch)) {
            return null;
        }

        $isRunning = $stateMatch[1] === 'RUNNING';
        preg_match('/pid\s+(\d+)/i', $output, $pidMatch);
        preg_match('/uptime\s+(.+)$/i', $output, $uptimeMatch);

        return [
            'running' => $isRunning,
            'pid' => isset($pidMatch[1]) ? (int) $pidMatch[1] : null,
            'uptime' => $isRunning ? trim($uptimeMatch[1] ?? '') : null,
        ];
    }

    protected function getSystemdStatus(string $service): array
    {
        $activeResult = $this->runCommand(self::SYSTEMCTL_BIN . ' is-active ' . escapeshellarg($service) . ' 2>&1');
        $isRunning = trim($activeResult['output']) === 'active';

        $pidResult = $this->runCommand(self::SYSTEMCTL_BIN . ' show ' . escapeshellarg($service) . ' --property=MainPID --value 2>&1');
        $pid = (int) trim($pidResult['output']);
        $pid = $pid > 0 ? $pid : null;

        $activeSinceResult = $this->runCommand(self::SYSTEMCTL_BIN . ' show ' . escapeshellarg($service) . ' --property=ActiveEnterTimestamp --value 2>&1');
        $uptime = null;
        $activeSince = trim($activeSinceResult['output']);
        if ($isRunning && $activeSince !== '') {
            try {
                $uptime = Carbon::parse($activeSince)->diffForHumans(null, true);
            } catch (\Throwable) {
                $uptime = null;
            }
        }

        return [
            'running' => $isRunning,
            'pid' => $pid,
            'uptime' => $uptime,
        ];
    }

    protected function runCommand(string $command): array
    {
        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        return [
            'output' => trim(implode("\n", $output)),
            'exit_code' => $exitCode,
        ];
    }
}
