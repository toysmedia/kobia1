<?php

namespace App\Services;

use App\Models\System\SuperAdmin;
use App\Models\System\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TenantImpersonationService
{
    public function impersonate(Tenant $tenant, SuperAdmin $admin): string
    {
        Session::put('impersonating_tenant_id', $tenant->id);
        Session::put('impersonating_super_admin_id', $admin->id);
        Session::put('impersonating', true);

        $domain = config('app.domain', 'oxnet.co.ke');

        return "https://{$tenant->subdomain}.{$domain}/dashboard";
    }

    public function stopImpersonation(Request $request): void
    {
        $request->session()->forget([
            'impersonating_tenant_id',
            'impersonating_super_admin_id',
            'impersonating',
        ]);
    }
}
