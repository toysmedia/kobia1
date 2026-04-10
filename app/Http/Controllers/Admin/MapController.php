<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\Router;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        return view('admin.isp.maps.index');
    }

    public function data()
    {
        $locations = MapLocation::all();

        // Include routers with coordinates
        $routers = Router::whereNotNull('latitude')->whereNotNull('longitude')->get()->map(function ($r) {
            return [
                'id'          => 'router_' . $r->id,
                'name'        => $r->name,
                'type'        => 'router',
                'latitude'    => $r->latitude,
                'longitude'   => $r->longitude,
                'description' => $r->wan_ip ?? '',
                'extra'       => ['model' => $r->model ?? '', 'wan_ip' => $r->wan_ip ?? ''],
            ];
        });

        // Include subscribers with coordinates
        $subscribers = Subscriber::whereNotNull('latitude')->whereNotNull('longitude')->get()->map(function ($s) {
            return [
                'id'          => 'sub_' . $s->id,
                'name'        => $s->name,
                'type'        => $s->connection_type === 'pppoe' ? 'subscriber_pppoe' : 'subscriber_hotspot',
                'latitude'    => $s->latitude,
                'longitude'   => $s->longitude,
                'description' => $s->phone ?? '',
                'extra'       => [
                    'package' => $s->package->name ?? '',
                    'status'  => $s->status,
                    'phone'   => $s->phone,
                ],
            ];
        });

        // Manual locations
        $manual = $locations->map(function ($loc) {
            return [
                'id'          => 'loc_' . $loc->id,
                'name'        => $loc->name,
                'type'        => $loc->type,
                'latitude'    => $loc->latitude,
                'longitude'   => $loc->longitude,
                'description' => $loc->description ?? '',
                'extra'       => $loc->metadata ?? [],
            ];
        });

        return response()->json($routers->concat($subscribers)->concat($manual)->values());
    }

    public function storeLocation(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:router,subscriber,tower,cabinet,other',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
        ]);

        $location = MapLocation::create($data);
        return response()->json(['success' => true, 'location' => $location]);
    }

    public function updateLocation(Request $request, MapLocation $location)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'latitude'    => 'sometimes|numeric|between:-90,90',
            'longitude'   => 'sometimes|numeric|between:-180,180',
            'description' => 'nullable|string',
        ]);

        $location->update($data);
        return response()->json(['success' => true]);
    }

    public function destroyLocation(MapLocation $location)
    {
        $location->delete();
        return response()->json(['success' => true]);
    }
}
