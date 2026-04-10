<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\SubscriptionPayment;
use App\Models\System\Tenant;
use App\Services\SuperAdminMpesaService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SuperAdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPayment::with(['tenant', 'plan'])->latest();

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $payments = $query->paginate(15)->withQueryString();
        $tenants  = Tenant::orderBy('name')->get(['id', 'name']);

        return view('super-admin.subscriptions.index', compact('payments', 'tenants'));
    }

    public function show(SubscriptionPayment $subscription)
    {
        $subscription->load(['tenant', 'plan']);

        return view('super-admin.subscriptions.show', compact('subscription'));
    }

    public function stkPush(Request $request, SuperAdminMpesaService $mpesa)
    {
        $data = $request->validate([
            'phone'     => 'required|string|min:10|max:15',
            'amount'    => 'required|numeric|min:1',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::findOrFail($data['tenant_id']);

        $result = $mpesa->stkPush(
            $data['phone'],
            $data['amount'],
            $data['tenant_id'],
            "Subscription for {$tenant->name}"
        );

        if ($result['success'] ?? false) {
            return back()->with('success', 'STK Push sent successfully.');
        }

        return back()->with('error', $result['message'] ?? 'STK Push failed.');
    }

    public function extend(SubscriptionPayment $subscription, SubscriptionService $service)
    {
        $service->extend($subscription->tenant, 30);

        return back()->with('success', 'Subscription extended by 30 days.');
    }
}
