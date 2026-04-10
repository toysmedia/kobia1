<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Nas;
use App\Models\AuditLog;
use App\Models\PendingRouterCommand;
use App\Services\MikrotikApiService;
use App\Services\RouterCommandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RouterCallbackController extends Controller
{
    public function callback(Request $request)
    {
        $data = $request->validate([
            'router_name'  => 'required|string|max:100',
            'wan_ip'       => 'nullable|ip',
            'vpn_ip'       => 'nullable|ip',
            'radius_secret' => 'nullable|string',
            'phase'        => 'nullable|integer|min:0|max:3',
        ]);

        // Find the router by name (sanitized the same way as in MikrotikScriptService)
        $sanitizedName = preg_replace('/[^a-zA-Z0-9\-]/', '-', $data['router_name']);
        $router = Router::where('name', $data['router_name'])
            ->orWhereRaw("REPLACE(REPLACE(name, ' ', '-'), '_', '-') = ?", [$sanitizedName])
            ->first();

        if (!$router) {
            Log::warning('Router callback: no matching router found', ['router_name' => $data['router_name']]);
            return response()->json(['status' => 'error', 'message' => 'Router not found'], 404);
        }

        $old = $router->toArray();

        // Update router with detected IPs
        $updates = ['last_heartbeat_at' => now()];
        if (!empty($data['wan_ip'])) {
            $updates['wan_ip'] = $data['wan_ip'];
        }
        if (!empty($data['vpn_ip'])) {
            $updates['vpn_ip'] = $data['vpn_ip'];
        }
        // Advance provision_phase only forward, never backward
        if (!empty($data['phase']) && (int)$data['phase'] > (int)$router->provision_phase) {
            $updates['provision_phase'] = (int)$data['phase'];
        }

        $router->update($updates);
        $router->refresh();

        // Sync NAS table for FreeRADIUS — use vpn_ip (packets arrive from VPN), fallback to wan_ip
        $nasIp = $router->vpn_ip ?: $router->wan_ip;
        if ($nasIp) {
            Nas::updateOrCreate(
                ['nasname' => $nasIp],
                [
                    'shortname'   => $router->name,
                    'type'        => 'other',
                    'secret'      => $router->radius_secret,
                    'description' => $router->name . ' - MikroTik',
                ]
            );
        }

        AuditLog::record('router.callback', Router::class, $router->id, $old, $router->fresh()->toArray());

        Log::info('Router callback: updated successfully', [
            'router_id'       => $router->id,
            'name'            => $router->name,
            'wan_ip'          => $router->wan_ip,
            'vpn_ip'          => $router->vpn_ip,
            'provision_phase' => $router->provision_phase,
        ]);

        // If a VPN IP just arrived, push any pending commands to the router
        $pendingCommands = [];
        if (!empty($data['vpn_ip']) && $router->vpn_ip) {
            $pendingCommands = $this->pushPendingCommands($router);
        }

        return response()->json([
            'status'              => 'success',
            'message'             => 'Router registered successfully',
            'router_id'           => $router->id,
            'provision_phase'     => $router->provision_phase,
            'pending_commands'    => $pendingCommands,
        ]);
    }

    /**
     * Push any queued commands to the router via the API service.
     * Returns the count of commands executed.
     */
    private function pushPendingCommands(Router $router): int
    {
        if (!class_exists(PendingRouterCommand::class)) {
            return 0;
        }

        $commands = PendingRouterCommand::where('router_id', $router->id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        if ($commands->isEmpty()) {
            return 0;
        }

        try {
            $api = (new MikrotikApiService())->init($router);
        } catch (\Exception $e) {
            Log::warning('pushPendingCommands: cannot init API', ['router' => $router->id, 'error' => $e->getMessage()]);
            return 0;
        }

        $executed = 0;
        foreach ($commands as $cmd) {
            try {
                $payload = is_array($cmd->payload) ? $cmd->payload : json_decode($cmd->payload ?? '{}', true);
                $result  = null;
                RouterCommandService::dispatch($api, $cmd->command, $payload);
                $cmd->update(['status' => 'executed', 'executed_at' => now(), 'result' => 'ok']);
                $executed++;
            } catch (\Exception $e) {
                $cmd->update(['status' => 'failed', 'result' => $e->getMessage()]);
                Log::warning('pushPendingCommands: command failed', ['cmd' => $cmd->id, 'error' => $e->getMessage()]);
            }
        }

        return $executed;
    }

    /**
     * Heartbeat endpoint — called by MikroTik scheduler every second.
     */
    public function heartbeat(Request $request)
    {
        $data = $request->validate([
            'router_name' => 'required|string|max:100',
            'vpn_ip'      => 'nullable|ip',
        ]);

        $sanitizedName = preg_replace('/[^a-zA-Z0-9\-]/', '-', $data['router_name']);
        $router = Router::where('name', $data['router_name'])
            ->orWhereRaw("REPLACE(REPLACE(name, ' ', '-'), '_', '-') = ?", [$sanitizedName])
            ->first();

        if (!$router) {
            return response()->json(['status' => 'error', 'message' => 'Router not found'], 404);
        }

        $updates = ['last_heartbeat_at' => now()];
        if (!empty($data['vpn_ip'])) {
            $updates['vpn_ip'] = $data['vpn_ip'];
        }
        $router->update($updates);

        return response()->json([
            'status' => 'ok',
            'phase'  => $router->provision_phase,
        ]);
    }

    /**
     * Phase-complete endpoint — called when each phase finishes.
     */
    public function phaseComplete(Request $request)
    {
        $data = $request->validate([
            'router_name' => 'required|string|max:100',
            'phase'       => 'required|integer|min:1|max:3',
        ]);

        $sanitizedName = preg_replace('/[^a-zA-Z0-9\-]/', '-', $data['router_name']);
        $router = Router::where('name', $data['router_name'])
            ->orWhereRaw("REPLACE(REPLACE(name, ' ', '-'), '_', '-') = ?", [$sanitizedName])
            ->first();

        if (!$router) {
            return response()->json(['status' => 'error', 'message' => 'Router not found'], 404);
        }

        if ((int)$data['phase'] > (int)$router->provision_phase) {
            $router->update([
                'provision_phase'  => (int)$data['phase'],
                'last_heartbeat_at' => now(),
            ]);
        }

        Log::info('Router phase complete', [
            'router_id' => $router->id,
            'phase'     => $data['phase'],
        ]);

        return response()->json([
            'status' => 'ok',
            'phase'  => $router->provision_phase,
        ]);
    }
}