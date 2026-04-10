<?php

namespace App\Console\Commands;

use App\Models\PendingRouterCommand;
use App\Services\MikrotikApiService;
use App\Services\RouterCommandService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process pending router commands for routers that are currently online.
 *
 * Commands are normally picked up by the router's 1-second sync script.
 * This artisan command acts as a safety net: it tries to push any commands
 * that have been pending for more than 30 seconds directly via the REST API.
 */
class ProcessPendingRouterCommands extends Command
{
    protected $signature   = 'app:process-router-commands';
    protected $description = 'Push stale pending router commands via REST API for online routers';

    public function handle(MikrotikApiService $api): int
    {
        $staleThreshold = now()->subSeconds(30);

        $pending = PendingRouterCommand::where('status', 'pending')
            ->where('created_at', '<', $staleThreshold)
            ->with('router')
            ->orderBy('router_id')
            ->orderBy('created_at')
            ->get();

        if ($pending->isEmpty()) {
            return 0;
        }

        $executed = 0;
        $failed   = 0;

        foreach ($pending as $cmd) {
            $router = $cmd->router;
            if (!$router || !$router->vpn_ip) {
                continue;
            }

            try {
                $api->init($router);
                if (!$api->isOnline()) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            $payload = is_array($cmd->payload) ? $cmd->payload : json_decode($cmd->payload ?? '{}', true);

            try {
                RouterCommandService::dispatch($api, $cmd->command, $payload);
                $cmd->update(['status' => 'executed', 'executed_at' => now()]);
                $executed++;
            } catch (\Exception $e) {
                $cmd->update(['status' => 'failed', 'result' => $e->getMessage()]);
                $failed++;
                Log::warning('ProcessPendingRouterCommands: command failed', [
                    'cmd_id'  => $cmd->id,
                    'command' => $cmd->command,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        if ($executed > 0 || $failed > 0) {
            $this->info("Processed {$executed} command(s), {$failed} failed.");
        }

        return 0;
    }
}