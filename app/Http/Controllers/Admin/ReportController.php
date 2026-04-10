<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaPayment;
use App\Models\Subscriber;
use App\Models\IspPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function pppoeSales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to',   now()->format('Y-m-d'));

        $payments = MpesaPayment::where('connection_type', 'pppoe')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->with(['package', 'subscriber'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue      = $payments->sum('amount');
        $totalTransactions = $payments->count();

        // Daily breakdown for chart
        $dailyData = $payments->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map(fn($g) => $g->sum('amount'))
            ->sortKeys();

        $chartLabels = $dailyData->keys()->map(fn($d) => date('d M', strtotime($d)))->values();
        $chartData   = $dailyData->values();

        return view('admin.isp.reports.pppoe_sales', compact(
            'payments', 'totalRevenue', 'totalTransactions',
            'chartLabels', 'chartData', 'dateFrom', 'dateTo'
        ));
    }

    public function hotspotSales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to',   now()->format('Y-m-d'));

        $payments = MpesaPayment::where('connection_type', 'hotspot')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->with(['package', 'subscriber'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue      = $payments->sum('amount');
        $totalTransactions = $payments->count();

        $dailyData = $payments->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map(fn($g) => $g->sum('amount'))
            ->sortKeys();

        $chartLabels = $dailyData->keys()->map(fn($d) => date('d M', strtotime($d)))->values();
        $chartData   = $dailyData->values();

        return view('admin.isp.reports.hotspot_sales', compact(
            'payments', 'totalRevenue', 'totalTransactions',
            'chartLabels', 'chartData', 'dateFrom', 'dateTo'
        ));
    }

    public function monthlyCombined(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $pppoeData    = MpesaPayment::where('connection_type', 'pppoe')
            ->where('status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $hotspotData  = MpesaPayment::where('connection_type', 'hotspot')
            ->where('status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $rows        = [];
        $chartLabels = [];
        $pppoeChart  = [];
        $hotspotChart= [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date    = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $pppoe   = $pppoeData[$date]->total   ?? 0;
            $hotspot = $hotspotData[$date]->total  ?? 0;
            $rows[]        = ['date' => $date, 'pppoe' => $pppoe, 'hotspot' => $hotspot, 'combined' => $pppoe + $hotspot];
            $chartLabels[] = $d;
            $pppoeChart[]  = $pppoe;
            $hotspotChart[]= $hotspot;
        }

        return view('admin.isp.reports.monthly_combined', compact(
            'rows', 'chartLabels', 'pppoeChart', 'hotspotChart', 'month', 'year'
        ));
    }

    public function salesByPackage(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to',   now()->format('Y-m-d'));

        $pppoePackages = MpesaPayment::where('connection_type', 'pppoe')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->whereNotNull('isp_package_id')
            ->selectRaw('isp_package_id, COUNT(*) as total_sold, SUM(amount) as total_revenue')
            ->groupBy('isp_package_id')
            ->with('package')
            ->get();

        $hotspotPackages = MpesaPayment::where('connection_type', 'hotspot')
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->whereNotNull('isp_package_id')
            ->selectRaw('isp_package_id, COUNT(*) as total_sold, SUM(amount) as total_revenue')
            ->groupBy('isp_package_id')
            ->with('package')
            ->get();

        return view('admin.isp.reports.sales_by_package', compact(
            'pppoePackages', 'hotspotPackages', 'dateFrom', 'dateTo'
        ));
    }

    public function revenueSummary()
    {
        $today     = MpesaPayment::where('status', 'completed')->whereDate('created_at', today())->sum('amount');
        $yesterday = MpesaPayment::where('status', 'completed')->whereDate('created_at', now()->subDay())->sum('amount');
        $week      = MpesaPayment::where('status', 'completed')->where('created_at', '>=', now()->startOfWeek())->sum('amount');
        $month     = MpesaPayment::where('status', 'completed')
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('amount');
        $lastMonth = MpesaPayment::where('status', 'completed')
            ->whereYear('created_at', now()->subMonth()->year)->whereMonth('created_at', now()->subMonth()->month)->sum('amount');
        $year      = MpesaPayment::where('status', 'completed')->whereYear('created_at', now()->year)->sum('amount');

        // Last 12 months trend
        $trend = [];
        $trendLabels = [];
        $trendData   = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt    = now()->subMonths($i);
            $total = MpesaPayment::where('status', 'completed')
                ->whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->sum('amount');
            $trendLabels[] = $dt->format('M Y');
            $trendData[]   = $total;
        }

        // Top 5 packages
        $topPackages = MpesaPayment::where('status', 'completed')
            ->whereNotNull('isp_package_id')
            ->selectRaw('isp_package_id, SUM(amount) as total_revenue, COUNT(*) as count')
            ->groupBy('isp_package_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->with('package')
            ->get();

        // Top 5 PPPoE customers
        $topCustomers = MpesaPayment::where('status', 'completed')
            ->where('connection_type', 'pppoe')
            ->whereNotNull('subscriber_id')
            ->selectRaw('subscriber_id, SUM(amount) as total_spend, COUNT(*) as count')
            ->groupBy('subscriber_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->with('subscriber')
            ->get();

        return view('admin.isp.reports.revenue_summary', compact(
            'today', 'yesterday', 'week', 'month', 'lastMonth', 'year',
            'trendLabels', 'trendData', 'topPackages', 'topCustomers'
        ));
    }
}
