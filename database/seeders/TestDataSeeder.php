<?php

namespace Database\Seeders;

use App\Models\Community\CommunityUser;
use App\Models\System\CmsContent;
use App\Models\System\PricingPlan;
use App\Models\System\SuperAdmin;
use App\Models\System\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TestDataSeeder — seeds deterministic test data for all user types.
 *
 * Useful for:
 *  - Local development quick-start
 *  - Manual testing of each portal
 *  - Automated feature tests that need a baseline
 *
 * Usage:
 *   php artisan db:seed --class=TestDataSeeder
 *
 * WARNING: Never run this on a production system.
 */
class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedPricingPlan();
        $this->seedDemoTenant();
        $this->seedCommunityUser();
        $this->seedCmsDefaults();

        $this->command->newLine();
        $this->command->info('✅  TestDataSeeder complete. See below for test credentials.');
        $this->command->table(
            ['Portal', 'URL', 'Email / Username', 'Password'],
            [
                ['Super Admin', '/super-admin/login',        'superadmin@oxnet.co.ke', 'password'],
                ['Admin (Tenant)', '{subdomain}/admin/login', 'admin@demo.oxnet.co.ke', 'password'],
                ['PPPoE Customer', '{subdomain}/customer/login', 'testcustomer', 'password'],
                ['Community',     '/community/login',         'member@community.test', 'password'],
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Super Admin
    // -------------------------------------------------------------------------

    private function seedSuperAdmin(): void
    {
        SuperAdmin::firstOrCreate(
            ['email' => 'superadmin@oxnet.co.ke'],
            [
                'name'      => 'OxNet Super Admin',
                'password'  => Hash::make('password'),
                'phone'     => '+254700000001',
                'is_active' => true,
            ]
        );

        $this->command->line('  Super Admin created: superadmin@oxnet.co.ke / password');
    }

    // -------------------------------------------------------------------------
    // Pricing plan (required before creating a tenant)
    // -------------------------------------------------------------------------

    private function seedPricingPlan(): void
    {
        PricingPlan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name'          => 'Starter',
                'description'   => 'Perfect for small ISPs getting started',
                'price'         => 2500.00,
                'billing_cycle' => 'monthly',
                'trial_days'    => 30,
                'max_customers' => 100,
                'max_routers'   => 3,
                'feature_flags' => json_encode([
                    'customer_portal' => true,
                    'ai_assistant'    => false,
                    'community'       => true,
                ]),
                'is_active'  => true,
                'sort_order' => 1,
            ]
        );

        $this->command->line('  Pricing plan created: Starter @ KES 2,500/mo');
    }

    // -------------------------------------------------------------------------
    // Demo tenant
    // -------------------------------------------------------------------------

    private function seedDemoTenant(): void
    {
        $plan = PricingPlan::where('slug', 'starter')->first();

        Tenant::firstOrCreate(
            ['subdomain' => 'demo'],
            [
                'name'                   => 'Demo ISP Ltd',
                'email'                  => 'admin@demo.oxnet.co.ke',
                'phone'                  => '+254700000002',
                'subdomain'              => 'demo',
                'database_name'          => 'oxnet_tenant_demo',
                'database_host'          => env('DB_HOST', '127.0.0.1'),
                'database_port'          => (int) env('DB_PORT', 3306),
                'database_username'      => env('DB_USERNAME', 'root'),
                'database_password'      => env('DB_PASSWORD', ''),
                'plan_id'                => $plan?->id,
                'status'                 => 'active',
                'subscription_expires_at'=> now()->addYear(),
            ]
        );

        $this->command->line('  Demo tenant created: subdomain=demo');
    }

    // -------------------------------------------------------------------------
    // Community user
    // -------------------------------------------------------------------------

    private function seedCommunityUser(): void
    {
        CommunityUser::firstOrCreate(
            ['email' => 'member@community.test'],
            [
                'name'              => 'Demo Community Member',
                'password'          => Hash::make('password'),
                'bio'               => 'ISP professional from Nairobi',
                'is_verified'       => true,
                'is_banned'         => false,
                'email_verified_at' => now(),
                'reputation'        => 10,
            ]
        );

        $this->command->line('  Community user created: member@community.test / password');
    }

    // -------------------------------------------------------------------------
    // Default CMS content
    // -------------------------------------------------------------------------

    private function seedCmsDefaults(): void
    {
        $defaults = [
            ['key' => 'site_name',        'value' => 'OxNet ISP Platform'],
            ['key' => 'site_tagline',     'value' => 'Reliable Internet for Kenya'],
            ['key' => 'support_email',    'value' => 'support@oxnet.co.ke'],
            ['key' => 'support_phone',    'value' => '+254700000000'],
        ];

        foreach ($defaults as $item) {
            CmsContent::firstOrCreate(
                ['key' => $item['key']],
                ['value' => $item['value'], 'section' => 'general']
            );
        }

        $this->command->line('  CMS defaults seeded.');
    }
}
