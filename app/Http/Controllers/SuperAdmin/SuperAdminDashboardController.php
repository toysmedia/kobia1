<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\SubscriptionPayment;
use App\Models\System\Tenant;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $totalTenants     = Tenant::count();
        $activeTenants    = Tenant::where('status', 'active')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $expiredTenants   = Tenant::where('status', 'expired')->count();

        $mrr = SubscriptionPayment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('amount');

        $recentTenants = Tenant::latest()->take(10)->get();

        $recentPayments = SubscriptionPayment::with(['tenant', 'plan'])
            ->latest()->take(10)->get();

        // Use a DB-portable date-truncation expression.
        // MySQL → DATE_FORMAT, SQLite → strftime (used in the test environment).
        $dateFormat = config('database.connections.' . config('database.default') . '.driver') === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyRevenue = SubscriptionPayment::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('super-admin.dashboard.index', compact(
            'totalTenants', 'activeTenants', 'suspendedTenants', 'expiredTenants',
            'mrr', 'recentTenants', 'recentPayments', 'monthlyRevenue'
        ));
    }
}
