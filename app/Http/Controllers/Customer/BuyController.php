<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\IspPackage;
use App\Models\MpesaPayment;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    public function index()
    {
        $packages = IspPackage::where('is_active', true)
            ->orderBy('price')
            ->get();

        $shortcode = config('mpesa.shortcode');
        $billingDomain = config('app.url');

        return view('customer.buy', compact('packages', 'shortcode', 'billingDomain'));
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'phone'          => 'required|string|min:9|max:15',
            'package_id'     => 'required|exists:isp_packages,id',
            'connection_type'=> 'required|in:pppoe,hotspot',
        ]);

        // Redirect to API STK push via AJAX form submit
        return redirect()->route('customer.buy')->with([
            'pending_phone'      => $data['phone'],
            'pending_package_id' => $data['package_id'],
            'pending_type'       => $data['connection_type'],
        ]);
    }
}
