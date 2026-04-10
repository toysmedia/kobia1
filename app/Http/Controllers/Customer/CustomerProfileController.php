<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\Customer\DataUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function __construct(private readonly DataUsageService $usageService) {}

    public function index(): View
    {
        /** @var Subscriber $subscriber */
        $subscriber     = auth('customer')->user();
        $usage          = $this->usageService->getUsageSummary($subscriber->username ?? '');
        $recentSessions = $this->usageService->getRecentSessions($subscriber->username ?? '', 10);

        return view('customer.profile.index', compact('subscriber', 'usage', 'recentSessions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();
        $subscriber->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        if (!Hash::check($request->current_password, $subscriber->password_hash)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $subscriber->update(['password_hash' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function sessionHistory(): View
    {
        /** @var Subscriber $subscriber */
        $subscriber     = auth('customer')->user();
        $recentSessions = $this->usageService->getRecentSessions($subscriber->username ?? '', 50);

        return view('customer.profile.sessions', compact('subscriber', 'recentSessions'));
    }

    public function apiUsage(): JsonResponse
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();
        $usage      = $this->usageService->getUsageSummary($subscriber->username ?? '');

        return response()->json($usage);
    }
}
