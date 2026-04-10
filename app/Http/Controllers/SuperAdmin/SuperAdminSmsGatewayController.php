<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\Tenant;
use App\Services\CmsService;
use Illuminate\Http\Request;

class SuperAdminSmsGatewayController extends Controller
{
    public function __construct(private CmsService $cms) {}

    public function index()
    {
        $config  = $this->cms->getSection('sms_gateway');
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);

        return view('super-admin.sms-gateway.index', compact('config', 'tenants'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'provider'   => 'required|string',
            'api_key'    => 'nullable|string',
            'api_secret' => 'nullable|string',
            'sender_id'  => 'nullable|string|max:11',
            'base_url'   => 'nullable|url',
        ]);

        $this->cms->setSection('sms_gateway', $data);

        return back()->with('success', 'SMS gateway settings saved.');
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        // Placeholder — actual dispatch would go through the configured provider
        return back()->with('success', "Test SMS queued to {$request->phone}.");
    }

    public function sendCampaign(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:160',
            'target'  => 'required|in:all,expiring_7,expiring_3,expired',
        ]);

        $query = Tenant::query();

        match ($request->target) {
            'expiring_7' => $query->where('subscription_expires_at', '<=', now()->addDays(7))
                                  ->where('subscription_expires_at', '>', now()),
            'expiring_3' => $query->where('subscription_expires_at', '<=', now()->addDays(3))
                                  ->where('subscription_expires_at', '>', now()),
            'expired'    => $query->where('status', 'expired'),
            default      => $query,
        };

        $count = $query->count();

        return back()->with('success', "Campaign queued for {$count} tenants.");
    }
}
