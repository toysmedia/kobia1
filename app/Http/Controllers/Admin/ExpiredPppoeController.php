<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class ExpiredPppoeController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        return view('admin.isp.expired_pppoe', compact('filter'));
    }

    public function getData(Request $request)
    {
        $filter    = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = Subscriber::where('connection_type', 'pppoe')
            ->where('expires_at', '<', now())
            ->with(['package', 'router']);

        switch ($filter) {
            case 'today':
                $query->whereDate('expires_at', today());
                break;
            case 'yesterday':
                $query->whereDate('expires_at', today()->subDay());
                break;
            case '7days':
                $query->where('expires_at', '>=', now()->subDays(7));
                break;
            case '30days':
                $query->where('expires_at', '>=', now()->subDays(30));
                break;
            case 'custom':
                if ($startDate) {
                    $query->whereDate('expires_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('expires_at', '<=', $endDate);
                }
                break;
        }

        $subscribers = $query->orderBy('expires_at', 'desc')->get();

        $data = $subscribers->map(function ($sub) {
            return [
                'id'               => $sub->id,
                'username'         => $sub->username,
                'full_name'        => $sub->full_name ?? '-',
                'phone'            => $sub->phone ?? '-',
                'package'          => $sub->package->name ?? '-',
                'expires_at'       => $sub->expires_at ? $sub->expires_at->format('Y-m-d H:i:s') : '-',
                'router'           => $sub->router->name ?? '-',
                'days_since_expiry'=> $sub->expires_at ? (int) $sub->expires_at->diffInDays(now()) : 0,
                'edit_url'         => route('admin.isp.subscribers.edit', $sub->id),
                'destroy_url'      => route('admin.isp.subscribers.destroy', $sub->id),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
