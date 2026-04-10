<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\MpesaPayment;
use App\Models\Radacct;
use App\Models\IspPackage;
use App\Models\Router;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- PPPoE & Hotspot active users ---
        $activePppoeUsers    = Subscriber::where('connection_type', 'pppoe')->where('status', 'active')->count();
        $activeHotspotUsers  = Subscriber::where('connection_type', 'hotspot')->where('status', 'active')->count();

        // --- Today's revenue split by type ---
        $todayRevenuePppoe   = MpesaPayment::where('connection_type', 'pppoe')
            ->where('status', 'completed')->whereDate('created_at', today())->sum('amount');
        $todayRevenueHotspot = MpesaPayment::where('connection_type', 'hotspot')
            ->where('status', 'completed')->whereDate('created_at', today())->sum('amount');

        // --- Monthly total revenue ---
        $totalRevenueMonth   = MpesaPayment::where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // --- New PPPoE customers today ---
        $newPppoeToday       = Subscriber::where('connection_type', 'pppoe')->whereDate('created_at', today())->count();

        // --- Totals ---
        $totalPppoeCustomers    = Subscriber::where('connection_type', 'pppoe')->count();
        $totalHotspotCustomers  = Subscriber::where('connection_type', 'hotspot')->count();

        // --- PPPoE expiring today ---
        $expiringSubscribers = Subscriber::where('connection_type', 'pppoe')
            ->whereBetween('expires_at', [today()->startOfDay(), today()->endOfDay()])
            ->with(['package', 'router'])
            ->orderBy('expires_at')
            ->get();

        $pppoeExpiringToday = $expiringSubscribers->count();

        // --- Expenses this month (10th card) ---
        $totalExpensesMonth = Expense::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');

        // --- Keep existing variables ---
        $activeSessions = Radacct::whereNull('acctstoptime')->count();
        $newToday = Subscriber::whereDate('created_at', today())->count();

        $recentPayments = MpesaPayment::with('package')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentSessions = Radacct::whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->limit(10)
            ->get();

        // Revenue chart (last 30 days)
        $revenueChart = MpesaPayment::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[]   = $revenueChart[$date]->total ?? 0;
        }

        // Sessions by router
        $sessionsByRouter = Radacct::whereNull('acctstoptime')
            ->selectRaw('nasipaddress, COUNT(*) as count')
            ->groupBy('nasipaddress')
            ->get();

        $routers   = Router::pluck('name', 'wan_ip');
        $pieLabels = $sessionsByRouter->map(fn($r) => $routers[$r->nasipaddress] ?? $r->nasipaddress)->values();
        $pieData   = $sessionsByRouter->pluck('count')->values();

        // Revenue vs Expenses (last 6 months)
        $revVsExpLabels   = [];
        $revVsExpRevenue  = [];
        $revVsExpExpenses = [];
        $revVsExpProfit   = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revVsExpLabels[]   = $month->format('M Y');
            $rev = MpesaPayment::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            $exp = Expense::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
            $revVsExpRevenue[]  = round($rev, 2);
            $revVsExpExpenses[] = round($exp, 2);
            $revVsExpProfit[]   = round($rev - $exp, 2);
        }

        return view('admin.isp.dashboard', compact(
            'activePppoeUsers', 'activeHotspotUsers',
            'todayRevenuePppoe', 'todayRevenueHotspot',
            'totalRevenueMonth', 'newPppoeToday',
            'totalPppoeCustomers', 'totalHotspotCustomers',
            'pppoeExpiringToday', 'expiringSubscribers',
            'totalExpensesMonth',
            'activeSessions', 'newToday',
            'recentPayments', 'recentSessions',
            'chartLabels', 'chartData', 'pieLabels', 'pieData',
            'revVsExpLabels', 'revVsExpRevenue', 'revVsExpExpenses', 'revVsExpProfit'
        ));
    }
}
