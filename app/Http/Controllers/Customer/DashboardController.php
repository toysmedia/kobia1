<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MpesaPayment;
use App\Models\Radacct;
use App\Models\Subscriber;
use App\Services\Customer\DataUsageService;

class DashboardController extends Controller
{
    public function __construct(private readonly DataUsageService $usageService) {}

    public function index()
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $usage = $this->usageService->getUsageSummary($subscriber->username ?? '');

        $recentPayments = MpesaPayment::where('subscriber_id', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $activeSessions = Radacct::where('username', $subscriber->username ?? '')
            ->whereNull('acctstoptime')
            ->get();

        $recentSessions = $this->usageService->getRecentSessions($subscriber->username ?? '', 5);

        return view('customer.dashboard', compact(
            'subscriber',
            'usage',
            'recentPayments',
            'activeSessions',
            'recentSessions'
        ))->with('usageService', $this->usageService);
    }
}
