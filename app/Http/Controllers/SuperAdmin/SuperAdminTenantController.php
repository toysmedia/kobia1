<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\PricingPlan;
use App\Models\System\SystemAuditLog;
use App\Models\System\SystemRecycleBin;
use App\Models\System\Tenant;
use App\Services\TenantImpersonationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminTenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('plan')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->paginate(15)->withQueryString();
        $plans   = PricingPlan::all();

        return view('super-admin.tenants.index', compact('tenants', 'plans'));
    }

    public function create()
    {
        $plans = PricingPlan::all();

        return view('super-admin.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:tenants,email',
            'subdomain' => 'required|string|max:100|unique:tenants,subdomain|alpha_dash',
            'plan_id'   => 'nullable|exists:pricing_plans,id',
            'status'    => 'required|in:active,suspended,expired,trial',
        ]);

        $tenant = Tenant::create($data);

        $this->auditLog('tenant_created', "Tenant {$tenant->name} created", $tenant->id);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['plan', 'subscriptionPayments' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $plans = PricingPlan::all();

        return view('super-admin.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => "required|email|unique:tenants,email,{$tenant->id}",
            'subdomain' => "required|string|max:100|unique:tenants,subdomain,{$tenant->id}|alpha_dash",
            'plan_id'   => 'nullable|exists:pricing_plans,id',
            'status'    => 'required|in:active,suspended,expired,trial',
        ]);

        $tenant->update($data);

        $this->auditLog('tenant_updated', "Tenant {$tenant->name} updated", $tenant->id);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        SystemRecycleBin::create([
            'tenant_id'      => $tenant->id,
            'model_type'     => Tenant::class,
            'model_id'       => $tenant->id,
            'data'           => $tenant->toArray(),
            'deleted_by_type' => 'super_admin',
            'deleted_by_id'  => Auth::guard('super_admin')->id(),
            'deleted_at'     => now(),
        ]);

        $this->auditLog('tenant_deleted', "Tenant {$tenant->name} deleted", $tenant->id);

        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant moved to recycle bin.');
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        $this->auditLog('tenant_suspended', "Tenant {$tenant->name} suspended", $tenant->id);

        return back()->with('success', 'Tenant suspended.');
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        $this->auditLog('tenant_activated', "Tenant {$tenant->name} activated", $tenant->id);

        return back()->with('success', 'Tenant activated.');
    }

    public function impersonate(Tenant $tenant, TenantImpersonationService $service)
    {
        $admin = Auth::guard('super_admin')->user();
        $url   = $service->impersonate($tenant, $admin);

        $this->auditLog('tenant_impersonated', "Impersonating tenant {$tenant->name}", $tenant->id);

        return redirect($url)->with('info', "Now impersonating {$tenant->name}");
    }

    public function toggleMaintenance(Tenant $tenant)
    {
        $tenant->update(['maintenance_mode' => !$tenant->maintenance_mode]);
        $status = $tenant->maintenance_mode ? 'enabled' : 'disabled';
        $this->auditLog('tenant_maintenance', "Maintenance {$status} for tenant {$tenant->name}", $tenant->id);

        return back()->with('success', "Maintenance mode {$status}.");
    }

    private function auditLog(string $action, string $description, int $tenantId = null): void
    {
        SystemAuditLog::create([
            'action'     => $action,
            'user_type'  => 'super_admin',
            'user_id'    => Auth::guard('super_admin')->id(),
            'tenant_id'  => $tenantId,
            'new_values' => ['description' => $description],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
