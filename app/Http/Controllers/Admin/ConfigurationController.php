<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ConfigurationController extends Controller
{
    public function index()
    {
        return view('admin.configuration.index');
    }

    public function servicesStatus(): JsonResponse
    {
        try {
            Artisan::call('services:status');
            $output = trim(Artisan::output());
            $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restartService(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service' => ['required', 'in:openvpn,freeradius,supervisor'],
        ]);

        $service = $validated['service'];
        $supervisorPrograms = [
            'openvpn' => 'openvpn',
            'freeradius' => 'freeradius',
        ];
        $systemdServices = [
            'openvpn' => 'openvpn-server',
            'freeradius' => 'freeradius',
            'supervisor' => 'supervisord',
        ];
        $supervisorResult = ['output' => '', 'exit_code' => 1];

        if (isset($supervisorPrograms[$service])) {
            $program = $supervisorPrograms[$service];
            $supervisorResult = $this->runCommand("supervisorctl restart {$program} 2>&1");
            if ($supervisorResult['exit_code'] === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Service {$service} restarted via Supervisor.",
                    'source' => 'supervisor',
                ]);
            }
        }

        $systemd = $systemdServices[$service];
        $systemdResult = $this->runCommand("sudo systemctl restart {$systemd} 2>&1");

        if ($systemdResult['exit_code'] === 0) {
            return response()->json([
                'success' => true,
                'message' => "Service {$service} restarted via systemd.",
                'source' => 'systemd',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to restart {$service}.",
            'error' => trim($supervisorResult['output'] . PHP_EOL . $systemdResult['output']),
        ], 500);
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
