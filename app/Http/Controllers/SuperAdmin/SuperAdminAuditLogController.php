<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\SystemAuditLog;
use App\Models\System\Tenant;
use Illuminate\Http\Request;

class SuperAdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemAuditLog::with('tenant')->latest();

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs    = $query->paginate(20)->withQueryString();
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('super-admin.audit-logs.index', compact('logs', 'tenants'));
    }
}
