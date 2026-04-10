<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Runs on each new tenant database after provisioning.
 * Seeds: default admin user, default roles/permissions, default ISP settings.
 *
 * This seeder is invoked by DatabaseManager::runTenantMigrations() with --seed flag.
 */
class TenantDefaultSeeder extends Seeder
{
    /**
     * Run the tenant database seeds.
     */
    public function run(): void
    {
        $this->seedDefaultAdmin();
        $this->seedDefaultRoles();
        $this->seedDefaultSettings();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function seedDefaultAdmin(): void
    {
        // Check if an admin already exists (idempotent)
        if (DB::connection('tenant')->table('admins')->exists()) {
            return;
        }

        DB::connection('tenant')->table('admins')->insert([
            'name'       => 'ISP Admin',
            'email'      => 'admin@isp.local',
            'password'   => Hash::make('ChangeMe@123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedDefaultRoles(): void
    {
        $roles = ['super_admin', 'admin', 'technician', 'billing', 'support'];

        foreach ($roles as $roleName) {
            DB::connection('tenant')->table('roles')->updateOrInsert(
                ['name' => $roleName],
                ['display_name' => ucfirst(str_replace('_', ' ', $roleName)), 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    private function seedDefaultSettings(): void
    {
        $defaults = [
            ['key' => 'isp_name',         'value' => 'My ISP',         'group' => 'general'],
            ['key' => 'isp_email',        'value' => '',               'group' => 'general'],
            ['key' => 'isp_phone',        'value' => '',               'group' => 'general'],
            ['key' => 'currency',         'value' => 'KES',            'group' => 'general'],
            ['key' => 'timezone',         'value' => 'Africa/Nairobi', 'group' => 'general'],
            ['key' => 'sms_enabled',      'value' => '0',              'group' => 'notifications'],
            ['key' => 'whatsapp_enabled', 'value' => '0',              'group' => 'notifications'],
            ['key' => 'mpesa_enabled',    'value' => '0',              'group' => 'payment_credentials'],
            ['key' => 'mpesa_env',        'value' => 'sandbox',        'group' => 'payment_credentials'],
            ['key' => 'theme_color',      'value' => '#2563eb',        'group' => 'branding'],
            ['key' => 'logo_url',         'value' => '',               'group' => 'branding'],
        ];

        foreach ($defaults as $setting) {
            DB::connection('tenant')->table('tenant_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()]),
            );
        }
    }
}
