<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuperAdminEmailGatewayController extends Controller
{
    public function __construct(private CmsService $cms) {}

    public function index()
    {
        $config = $this->cms->getSection('email_gateway');

        return view('super-admin.email-gateway.index', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'driver'       => 'required|in:smtp,mailgun,ses,postmark',
            'host'         => 'nullable|string',
            'port'         => 'nullable|integer',
            'username'     => 'nullable|string',
            'password'     => 'nullable|string',
            'encryption'   => 'nullable|in:tls,ssl,null',
            'from_address' => 'nullable|email',
            'from_name'    => 'nullable|string',
        ]);

        $this->cms->setSection('email_gateway', $data);

        return back()->with('success', 'Email gateway settings saved.');
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
        ]);

        try {
            Mail::raw('This is a test email from OxNet Super Admin.', function ($m) use ($request) {
                $m->to($request->to)->subject('Test Email - OxNet Platform');
            });

            return back()->with('success', "Test email sent to {$request->to}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
