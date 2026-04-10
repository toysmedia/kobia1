<?php

namespace Database\Seeders;

use App\Models\System\FeatureFlag;
use App\Models\System\PricingPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the default OxNet pricing plans with feature flags.
 * Plans: Starter, Professional, Enterprise.
 */
class PricingPlanSeeder extends Seeder
{
    /**
     * Feature definitions keyed by feature_key.
     *
     * @var array<string, string>
     */
    private const FEATURES = [
        'pppoe_management'        => 'PPPoE User Management',
        'hotspot_management'      => 'HotSpot Management',
        'mpesa_payments'          => 'M-Pesa STK Push Payments',
        'bulk_sms'                => 'Bulk SMS Campaigns',
        'automated_billing'       => 'Automated Billing & Invoicing',
        'customer_portal'         => 'PPPoE Customer Self-Service Portal',
        'whitelabel'              => 'White-label Branding',
        'api_access'              => 'API Access',
        'advanced_reports'        => 'Advanced Analytics & Reports',
        'multi_router'            => 'Multiple Router Support',
        'reseller_module'         => 'Reseller Management',
        'support_tickets'         => 'Customer Support Tickets',
        'ai_assistant'            => 'AI Assistant',
        'voucher_management'      => 'Voucher / HotSpot Voucher Management',
        'map_view'                => 'Customer Map View',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Perfect for small ISPs just getting started. Manage up to 50 PPPoE customers.',
                'price'         => 1500.00,
                'billing_cycle' => 'monthly',
                'trial_days'    => 14,
                'max_customers' => 50,
                'max_routers'   => 2,
                'is_active'     => true,
                'sort_order'    => 1,
                'features'      => [
                    'pppoe_management'   => true,
                    'hotspot_management' => false,
                    'mpesa_payments'     => true,
                    'bulk_sms'           => false,
                    'automated_billing'  => true,
                    'customer_portal'    => true,
                    'whitelabel'         => false,
                    'api_access'         => false,
                    'advanced_reports'   => false,
                    'multi_router'       => false,
                    'reseller_module'    => false,
                    'support_tickets'    => true,
                    'ai_assistant'       => false,
                    'voucher_management' => false,
                    'map_view'           => false,
                ],
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'description'   => 'For growing ISPs. Unlimited customers, advanced features, and API access.',
                'price'         => 3500.00,
                'billing_cycle' => 'monthly',
                'trial_days'    => 14,
                'max_customers' => 500,
                'max_routers'   => 10,
                'is_active'     => true,
                'sort_order'    => 2,
                'features'      => [
                    'pppoe_management'   => true,
                    'hotspot_management' => true,
                    'mpesa_payments'     => true,
                    'bulk_sms'           => true,
                    'automated_billing'  => true,
                    'customer_portal'    => true,
                    'whitelabel'         => false,
                    'api_access'         => true,
                    'advanced_reports'   => true,
                    'multi_router'       => true,
                    'reseller_module'    => true,
                    'support_tickets'    => true,
                    'ai_assistant'       => true,
                    'voucher_management' => true,
                    'map_view'           => true,
                ],
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'Full-featured platform for large ISPs. Unlimited everything. White-label included.',
                'price'         => 7500.00,
                'billing_cycle' => 'monthly',
                'trial_days'    => 30,
                'max_customers' => 999999,
                'max_routers'   => 999,
                'is_active'     => true,
                'sort_order'    => 3,
                'features'      => array_fill_keys(array_keys(self::FEATURES), true),
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = PricingPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                array_merge($planData, ['feature_flags' => $features]),
            );

            // Also seed the feature_flags pivot table
            foreach ($features as $key => $isEnabled) {
                FeatureFlag::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $key],
                    ['feature_name' => self::FEATURES[$key] ?? $key, 'is_enabled' => $isEnabled],
                );
            }

            $this->command->info("✅ Plan '{$plan->name}' seeded.");
        }
    }
}
