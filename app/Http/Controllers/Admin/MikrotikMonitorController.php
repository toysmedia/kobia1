<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterSyncLog;
use App\Services\MikrotikApiService;
use Illuminate\Http\Request;

class MikrotikMonitorController extends Controller
{
    public function __construct(protected MikrotikApiService $api) {}

    public function index()
    {
        $routers = Router::where('is_active', true)->get()->map(function ($router) {
            $online = false;
            $resource = [];
            try {
                $this->api->init($router);
                $online   = $this->api->isOnline();
                $resource = $online ? $this->api->getSystemResource() : [];
            } catch (\Exception $e) {
                // Router unreachable
            }

            return [
                'id'       => $router->id,
                'name'     => $router->name,
                'wan_ip'   => $router->wan_ip,
                'online'   => $online,
                'cpu'      => $resource['cpu-load'] ?? 'N/A',
                'memory'   => isset($resource['free-memory'], $resource['total-memory'])
                    ? round((1 - $resource['free-memory'] / $resource['total-memory']) * 100) . '%'
                    : 'N/A',
                'uptime'   => $resource['uptime'] ?? 'N/A',
                'version'  => $resource['version'] ?? 'N/A',
                'board'    => $resource['board-name'] ?? $router->name,
            ];
        });

        return view('admin.isp.mikrotik_monitor.index', compact('routers'));
    }

    public function show(Router $router)
    {
        return view('admin.isp.mikrotik_monitor.show', compact('router'));
    }

    public function getData(Router $router)
    {
        // If the router has synced within the last 10 seconds, serve cached data
        // to reduce load on both the server and the router.
        // NOTE: Cached response includes ppp_sessions/hs_sessions/cpu from the sync log.
        //       Live response includes full resource/health/board/interfaces/users data.
        //       Consumers should check the 'cached' flag to determine which fields are present.
        $recentSync = RouterSyncLog::where('router_id', $router->id)
            ->where('synced_at', '>=', now()->subSeconds(10))
            ->orderBy('synced_at', 'desc')
            ->first();

        if ($recentSync) {
            return response()->json([
                'online'      => true,
                'cached'      => true,
                'synced_at'   => $recentSync->synced_at->toIso8601String(),
                'ppp_sessions' => $recentSync->ppp_sessions ?? [],
                'hs_sessions'  => $recentSync->hs_sessions  ?? [],
                'cpu'          => $recentSync->cpu_load,
                'timestamp'    => now()->toIso8601String(),
            ]);
        }

        try {
            $this->api->init($router);
            $online = $this->api->isOnline();

            if (!$online) {
                return response()->json(['online' => false]);
            }

            $resource   = $this->api->getSystemResource();
            $health     = $this->api->getSystemHealth();
            $board      = $this->api->getRouterBoard();
            $interfaces = $this->api->getInterfaces();
            $users      = $this->api->getActiveUsers();

            return response()->json([
                'online'     => true,
                'cached'     => false,
                'resource'   => $resource,
                'health'     => $health,
                'board'      => $board,
                'interfaces' => $interfaces,
                'users'      => $users,
                'timestamp'  => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['online' => false, 'error' => $e->getMessage()]);
        }
    }

    public function routerStatuses()
    {
        $statuses = Router::where('is_active', true)->get()->map(function ($router) {
            $online = false;
            try {
                $this->api->init($router);
                $online = $this->api->isOnline();
            } catch (\Exception $e) {
                // unreachable
            }

            return [
                'id'     => $router->id,
                'name'   => $router->name,
                'wan_ip' => $router->wan_ip,
                'online' => $online,
            ];
        });

        return response()->json($statuses);
    }
}