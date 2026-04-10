<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\IspPackage;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CustomerPackageController extends Controller
{
    public function index(): View
    {
        /** @var Subscriber $subscriber */
        $subscriber     = auth('customer')->user();
        $currentPackage = $subscriber->package;
        $availablePackages = IspPackage::where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('customer.package.index', compact('subscriber', 'currentPackage', 'availablePackages'));
    }

    public function requestChange(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:isp_packages,id'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();
        $newPackage = IspPackage::findOrFail($data['package_id']);

        if ((int) $subscriber->isp_package_id === (int) $newPackage->id) {
            return back()->with('info', 'You are already on this package.');
        }

        Log::info('[CustomerPortal] Package change requested', [
            'subscriber_id'   => $subscriber->id,
            'from_package_id' => $subscriber->isp_package_id,
            'to_package_id'   => $newPackage->id,
        ]);

        // TODO: send admin notification via NotifyService

        return back()->with('success', "Your request to switch to '{$newPackage->name}' has been received. An admin will process it shortly.");
    }
}
