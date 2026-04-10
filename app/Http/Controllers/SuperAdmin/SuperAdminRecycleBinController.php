<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\SystemRecycleBin;
use App\Models\System\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminRecycleBinController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemRecycleBin::with('tenant')->latest();

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($modelType = $request->input('model_type')) {
            $query->where('model_type', $modelType);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $items      = $query->paginate(20)->withQueryString();
        $tenants    = Tenant::orderBy('name')->get(['id', 'name']);
        $modelTypes = SystemRecycleBin::select('model_type')->distinct()->pluck('model_type');

        return view('super-admin.recycle-bin.index', compact('items', 'tenants', 'modelTypes'));
    }

    public function restore($id)
    {
        $item = SystemRecycleBin::findOrFail($id);

        try {
            $modelClass = $item->model_type;
            $data       = $item->data;

            // Remove timestamps/deleted_at so DB sets them fresh; keep id for re-insert
            unset($data['deleted_at'], $data['updated_at'], $data['created_at']);
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table((new $modelClass)->getTable())->insert($data);

            $item->delete();

            return back()->with('success', 'Record restored successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $item = SystemRecycleBin::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Record permanently deleted.');
    }
}
