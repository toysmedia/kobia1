<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\Tenant;

class SuperAdminTenantMapController extends Controller
{
    public function index()
    {
        $tenants = Tenant::select(['id', 'name', 'subdomain', 'status', 'lat', 'lng'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        return view('super-admin.tenant-map.index', compact('tenants'));
    }
}
