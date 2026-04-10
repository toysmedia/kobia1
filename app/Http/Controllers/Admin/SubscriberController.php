<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\IspPackage;
use App\Models\Router;
use App\Models\AuditLog;
use App\Services\RadiusService;
use App\Services\RouterCommandService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function __construct(protected RadiusService $radius, protected RouterCommandService $routerCmd) {}

    public function index(Request $request)
    {
        $query = Subscriber::with(['package', 'router']);
        // Allow filtering by 'type' (alias for connection_type) on the general index view
        if ($request->filled('type')) {
            $query->where('connection_type', $request->type);
        }
        $this->applyFilters($query, $request);
        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $packages = IspPackage::where('is_active', true)->orderBy('name')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.index', compact('subscribers', 'packages', 'routers'));
    }

    public function create()
    {
        $packages = IspPackage::where('is_active', true)->orderBy('price')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.create', compact('packages', 'routers'));
    }

    /** Display PPPoE subscribers only. */
    public function pppoe(Request $request)
    {
        $query = Subscriber::with(['package', 'router'])
            ->where('connection_type', 'pppoe');
        $this->applyFilters($query, $request);
        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $packages = IspPackage::where('is_active', true)->orderBy('name')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.pppoe', compact('subscribers', 'packages', 'routers'));
    }

    /** Display Hotspot subscribers only. */
    public function hotspot(Request $request)
    {
        $query = Subscriber::with(['package', 'router'])
            ->where('connection_type', 'hotspot');
        $this->applyFilters($query, $request);
        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $packages = IspPackage::where('is_active', true)->orderBy('name')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.hotspot', compact('subscribers', 'packages', 'routers'));
    }

    /** Shared filter logic for subscriber queries. */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('username', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('package_id')) $query->where('isp_package_id', $request->package_id);
        if ($request->filled('router_id')) $query->where('router_id', $request->router_id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'required|string|max:20',
            'username'        => 'required|string|max:64|unique:subscribers,username',
            'password'        => 'required|string|min:6|max:64',
            'isp_package_id'  => 'nullable|exists:isp_packages,id',
            'router_id'       => 'nullable|exists:routers,id',
            'connection_type' => 'required|in:pppoe,hotspot',
            'status'          => 'required|in:active,suspended,expired',
            'expires_at'      => 'nullable|date',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
        ]);

        $plainPassword = $data['password'];
        $data['password_hash']    = bcrypt($plainPassword);
        $data['radius_password']  = encrypt($plainPassword);
        $data['created_by']       = 'admin';
        unset($data['password']);

        $subscriber = Subscriber::create($data);
        $subscriber->load('router', 'package');

        // Provision RADIUS
        if ($subscriber->isp_package_id) {
            $this->radius->provisionUser($subscriber->username, $plainPassword, $subscriber->package);
        }

        // Push credentials to the MikroTik router (PPPoE secret or Hotspot user)
        if ($subscriber->router_id) {
            if ($subscriber->connection_type === 'hotspot') {
                $this->routerCmd->provisionHotspotUser($subscriber, $plainPassword);
            } else {
                $this->routerCmd->provisionPppSecret($subscriber, $plainPassword);
            }
        }

        AuditLog::record('subscriber.created', Subscriber::class, $subscriber->id, [], $subscriber->only(['name','username','phone','status']));

        return redirect()->route('admin.isp.subscribers.index')->with('success', "Subscriber '{$subscriber->name}' created.");
    }

    public function show(Subscriber $subscriber)
    {
        $subscriber->load(['package', 'router', 'payments']);

        $sessions = \App\Models\Radacct::where('username', $subscriber->username)
            ->orderBy('acctstarttime', 'desc')
            ->limit(20)
            ->get();

        $activeSession = \App\Models\Radacct::where('username', $subscriber->username)
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->first();

        $radacctStats = \App\Models\Radacct::where('username', $subscriber->username)
            ->selectRaw('
                COALESCE(SUM(acctoutputoctets), 0) as total_download,
                COALESCE(SUM(acctinputoctets), 0)  as total_upload,
                COUNT(*)                            as total_sessions,
                COALESCE(SUM(acctsessiontime), 0)   as total_time
            ')
            ->first();

        $payments = $subscriber->payments()->orderBy('created_at', 'desc')->limit(20)->get();

        $chartData = $this->buildUsageChart($subscriber->username);

        return view('admin.isp.subscribers.show', compact(
            'subscriber', 'sessions', 'activeSession', 'radacctStats', 'payments', 'chartData'
        ));
    }

    public function usageData(Subscriber $subscriber)
    {
        return response()->json($this->buildUsageChart($subscriber->username));
    }

    private function buildUsageChart(string $username): array
    {
        $days   = 14;
        $labels = [];
        $dl     = [];
        $ul     = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i);
            $labels[] = $date->format('d M');
            $start    = $date->copy()->startOfDay();
            $end      = $date->copy()->endOfDay();

            $stats = \App\Models\Radacct::where('username', $username)
                ->whereBetween('acctstarttime', [$start, $end])
                ->selectRaw('COALESCE(SUM(acctoutputoctets),0) as dl, COALESCE(SUM(acctinputoctets),0) as ul')
                ->first();

            $dl[] = round(($stats->dl ?? 0) / 1048576, 2);
            $ul[] = round(($stats->ul ?? 0) / 1048576, 2);
        }

        return ['labels' => $labels, 'download' => $dl, 'upload' => $ul];
    }

    public function edit(Subscriber $subscriber)
    {
        $packages = IspPackage::where('is_active', true)->orderBy('price')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.edit', compact('subscriber', 'packages', 'routers'));
    }

    public function update(Request $request, Subscriber $subscriber)
    {
        $old = $subscriber->only(['name','username','phone','status','isp_package_id']);
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'required|string|max:20',
            'username'        => "required|string|max:64|unique:subscribers,username,{$subscriber->id}",
            'password'        => 'nullable|string|min:6|max:64',
            'isp_package_id'  => 'nullable|exists:isp_packages,id',
            'router_id'       => 'nullable|exists:routers,id',
            'connection_type' => 'required|in:pppoe,hotspot',
            'status'          => 'required|in:active,suspended,expired',
            'expires_at'      => 'nullable|date',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
        ]);

        $plainPassword = null;
        if (!empty($data['password'])) {
            $plainPassword = $data['password'];
            $data['password_hash']   = bcrypt($plainPassword);
            $data['radius_password'] = encrypt($plainPassword);
        }
        unset($data['password']);

        $subscriber->update($data);
        $subscriber->load('router', 'package');

        // Re-provision RADIUS if package or password changed
        if ($subscriber->isp_package_id) {
            $radPass = $plainPassword ?? decrypt($subscriber->radius_password);
            if ($data['status'] === 'suspended') {
                $this->radius->suspendUser($subscriber->username);
            } else {
                $this->radius->provisionUser($subscriber->username, $radPass, $subscriber->package);
            }
        }

        // Push updated credentials to the MikroTik router when password changed
        if ($plainPassword && $subscriber->router_id) {
            if ($subscriber->connection_type === 'hotspot') {
                $this->routerCmd->provisionHotspotUser($subscriber, $plainPassword);
            } else {
                $this->routerCmd->updatePppSecret($subscriber, $plainPassword);
            }
        }

        AuditLog::record('subscriber.updated', Subscriber::class, $subscriber->id, $old, $subscriber->fresh()->only(['name','username','phone','status']));
        return redirect()->route('admin.isp.subscribers.index')->with('success', 'Subscriber updated.');
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->load('router', 'package');

        // Remove from MikroTik router before deleting from DB
        if ($subscriber->router_id) {
            if ($subscriber->connection_type === 'hotspot') {
                $this->routerCmd->removeHotspotUser($subscriber);
            } else {
                $this->routerCmd->removePppSecret($subscriber);
            }
        }

        $this->radius->removeUser($subscriber->username);
        AuditLog::record('subscriber.deleted', Subscriber::class, $subscriber->id, $subscriber->toArray(), []);
        $subscriber->delete();
        return redirect()->route('admin.isp.subscribers.index')->with('success', 'Subscriber deleted.');
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:suspend,activate,delete',
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
        ]);

        $subscribers = Subscriber::whereIn('id', $data['ids'])->get();

        foreach ($subscribers as $subscriber) {
            match ($data['action']) {
                'suspend'  => $this->radius->suspendUser($subscriber->username) && $subscriber->update(['status' => 'suspended']),
                'activate' => $subscriber->update(['status' => 'active']),
                'delete'   => $this->radius->removeUser($subscriber->username) ?: $subscriber->delete(),
                default    => null,
            };
        }

        return back()->with('success', ucfirst($data['action']) . ' applied to ' . count($data['ids']) . ' subscribers.');
    }
}