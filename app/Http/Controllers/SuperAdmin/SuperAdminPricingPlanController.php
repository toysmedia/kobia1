<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\FeatureFlag;
use App\Models\System\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminPricingPlanController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::withCount('tenants')->latest()->get();

        return view('super-admin.pricing-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.pricing-plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'billing_cycle'   => 'required|in:monthly,quarterly,yearly',
            'max_customers'   => 'nullable|integer|min:0',
            'max_routers'     => 'nullable|integer|min:0',
            'description'     => 'nullable|string',
            'is_active'       => 'boolean',
            'feature_flags'   => 'nullable|array',
            'feature_flags.*' => 'string',
        ]);

        $featureFlags = $data['feature_flags'] ?? [];
        unset($data['feature_flags']);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug']      = Str::slug($data['name']);

        $plan = PricingPlan::create($data);

        foreach ($featureFlags as $key => $name) {
            FeatureFlag::create([
                'plan_id'      => $plan->id,
                'feature_key'  => $key,
                'feature_name' => $name,
                'is_enabled'   => true,
            ]);
        }

        return redirect()->route('super-admin.pricing-plans.index')
            ->with('success', 'Pricing plan created.');
    }

    public function show(PricingPlan $pricingPlan)
    {
        $pricingPlan->load(['featureFlags', 'tenants']);

        return view('super-admin.pricing-plans.show', compact('pricingPlan'));
    }

    public function edit(PricingPlan $pricingPlan)
    {
        $pricingPlan->load('featureFlags');

        return view('super-admin.pricing-plans.edit', compact('pricingPlan'));
    }

    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'billing_cycle'   => 'required|in:monthly,quarterly,yearly',
            'max_customers'   => 'nullable|integer|min:0',
            'max_routers'     => 'nullable|integer|min:0',
            'description'     => 'nullable|string',
            'is_active'       => 'boolean',
            'feature_flags'   => 'nullable|array',
            'feature_flags.*' => 'string',
        ]);

        $featureFlags = $data['feature_flags'] ?? [];
        unset($data['feature_flags']);
        $data['is_active'] = $request->boolean('is_active');

        $pricingPlan->update($data);

        $pricingPlan->featureFlags()->delete();
        foreach ($featureFlags as $key => $name) {
            FeatureFlag::create([
                'plan_id'      => $pricingPlan->id,
                'feature_key'  => $key,
                'feature_name' => $name,
                'is_enabled'   => true,
            ]);
        }

        return redirect()->route('super-admin.pricing-plans.index')
            ->with('success', 'Pricing plan updated.');
    }

    public function destroy(PricingPlan $pricingPlan)
    {
        $pricingPlan->featureFlags()->delete();
        $pricingPlan->delete();

        return redirect()->route('super-admin.pricing-plans.index')
            ->with('success', 'Pricing plan deleted.');
    }
}
