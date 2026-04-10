<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterSyncLog;
use App\Models\PendingRouterCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Real-time 1-second sync endpoint.
 *
 * The MikroTik router posts active PPPoE/hotspot sessions, interface stats,
 * and system resources every second.  This controller:
 *  1. Persists the sync snapshot (RouterSyncLog).
 *  2. Returns pending commands for the router to execute immediately.
 */
class RouterSyncController extends Controller
{
    /**
     * POST /api/router-sync
     *
     * Accepts bulk session/stats data from the router and returns any pending
     * commands that the router should execute in this sync cycle.
     */
    public function sync(Request $request)
    {
        $data = $request->validate([
            'router_name'  => 'required|string|max:100',
            'ppp_sessions' => 'nullable|string',
            'hs_sessions'  => 'nullable|string',
            'cpu'          => 'nullable|string|max:10',
        ]);

        $sanitizedName = preg_replace('/[^a-zA-Z0-9\-]/', '-', $data['router_name']);
        $router = Router::where('name', $data['router_name'])
            ->orWhereRaw("REPLACE(REPLACE(name, ' ', '-'), '_', '-') = ?", [$sanitizedName])
            ->first();

        if (!$router) {
            return response()->json(['status' => 'error', 'message' => 'Router not found'], 404);
        }

        // Parse the compact session strings sent by the RouterOS script.
        // Format: "username,ip,uptime,caller-id;username2,ip2,uptime2,caller-id2;"
        $pppSessions  = $this->parseSessionString($data['ppp_sessions'] ?? '', ['username', 'ip', 'uptime', 'caller_id']);
        $hsSessions   = $this->parseSessionString($data['hs_sessions']  ?? '', ['username', 'ip', 'uptime', 'mac_address']);

        // Update last heartbeat timestamp
        $router->update(['last_heartbeat_at' => now()]);

        // Persist sync snapshot if model exists
        if (class_exists(RouterSyncLog::class)) {
            RouterSyncLog::create([
                'router_id'    => $router->id,
                'ppp_sessions' => $pppSessions,
                'hs_sessions'  => $hsSessions,
                'cpu_load'     => $data['cpu'] ?? null,
                'synced_at'    => now(),
            ]);
        }

        // Fetch pending commands for this router
        $commands = [];
        if (class_exists(PendingRouterCommand::class)) {
            $pending = PendingRouterCommand::where('router_id', $router->id)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->limit(20)
                ->get();

            foreach ($pending as $cmd) {
                $commands[] = [
                    'id'      => $cmd->id,
                    'command' => $cmd->command,
                    'payload' => is_array($cmd->payload) ? $cmd->payload : json_decode($cmd->payload ?? '{}', true),
                ];
                // Mark as dispatched so it isn't sent again
                $cmd->update(['status' => 'dispatched', 'executed_at' => now()]);
            }
        }

        return response()->json([
            'status'   => 'ok',
            'commands' => $commands,
        ]);
    }

    /**
     * Parse the compact semicolon-delimited session string from RouterOS.
     *
     * @param  string   $raw    e.g. "alice,10.0.0.2,00:05:10,00:11:22:33:44:55;"
     * @param  string[] $keys   Field names to map to
     * @return array
     */
    private function parseSessionString(string $raw, array $keys): array
    {
        $sessions = [];
        foreach (explode(';', rtrim($raw, ';')) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }
            $parts   = explode(',', $entry, count($keys));
            $session = [];
            foreach ($keys as $i => $key) {
                $session[$key] = $parts[$i] ?? '';
            }
            $sessions[] = $session;
        }
        return $sessions;
    }
}