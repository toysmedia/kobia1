<?php

namespace App\Http\Controllers\Tenant;

use App\Models\System\PricingPlan;
use App\Models\System\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Shows current plan and available upgrade options to the tenant admin.
 */
class PricingController extends Controller
{
    /**
     * Return the pricing sidebar view or JSON data.
     */
    public function currentPlan(): View|JsonResponse
    {
        /** @var Tenant|null $tenant */
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        $currentPlan = $tenant?->plan;

        $allPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'current_plan' => $currentPlan,
                'plans'        => $allPlans,
            ]);
        }

        return view('partials.pricing-sidebar', compact('currentPlan', 'allPlans', 'tenant'));
    }
}
