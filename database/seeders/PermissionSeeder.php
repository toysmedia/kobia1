<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['group' => 'dashboard',        'name' => 'view_dashboard',    'display_name' => 'View Dashboard'],

            // Subscribers
            ['group' => 'subscribers',      'name' => 'view_subscribers',  'display_name' => 'View Subscribers'],
            ['group' => 'subscribers',      'name' => 'create_subscriber', 'display_name' => 'Create Subscriber'],
            ['group' => 'subscribers',      'name' => 'edit_subscriber',   'display_name' => 'Edit Subscriber'],
            ['group' => 'subscribers',      'name' => 'delete_subscriber', 'display_name' => 'Delete Subscriber'],

            // Routers
            ['group' => 'routers',          'name' => 'view_routers',      'display_name' => 'View Routers'],
            ['group' => 'routers',          'name' => 'create_router',     'display_name' => 'Create Router'],
            ['group' => 'routers',          'name' => 'edit_router',       'display_name' => 'Edit Router'],
            ['group' => 'routers',          'name' => 'delete_router',     'display_name' => 'Delete Router'],
            ['group' => 'routers',          'name' => 'generate_script',   'display_name' => 'Generate Script'],

            // Packages
            ['group' => 'packages',         'name' => 'view_packages',     'display_name' => 'View Packages'],
            ['group' => 'packages',         'name' => 'create_package',    'display_name' => 'Create Package'],
            ['group' => 'packages',         'name' => 'edit_package',      'display_name' => 'Edit Package'],
            ['group' => 'packages',         'name' => 'delete_package',    'display_name' => 'Delete Package'],

            // Payments
            ['group' => 'payments',         'name' => 'view_payments',     'display_name' => 'View Payments'],
            ['group' => 'payments',         'name' => 'export_payments',   'display_name' => 'Export Payments'],

            // Expenses
            ['group' => 'expenses',         'name' => 'view_expenses',     'display_name' => 'View Expenses'],
            ['group' => 'expenses',         'name' => 'create_expense',    'display_name' => 'Create Expense'],
            ['group' => 'expenses',         'name' => 'edit_expense',      'display_name' => 'Edit Expense'],
            ['group' => 'expenses',         'name' => 'delete_expense',    'display_name' => 'Delete Expense'],

            // Reports
            ['group' => 'reports',          'name' => 'view_reports',      'display_name' => 'View Reports'],
            ['group' => 'reports',          'name' => 'export_reports',    'display_name' => 'Export Reports'],

            // Messaging
            ['group' => 'messaging',        'name' => 'send_sms',          'display_name' => 'Send SMS'],
            ['group' => 'messaging',        'name' => 'send_whatsapp',     'display_name' => 'Send WhatsApp'],
            ['group' => 'messaging',        'name' => 'send_email',        'display_name' => 'Send Email'],
            ['group' => 'messaging',        'name' => 'view_message_logs', 'display_name' => 'View Message Logs'],

            // Settings
            ['group' => 'settings',         'name' => 'manage_settings',   'display_name' => 'Manage Settings'],

            // Access Control
            ['group' => 'access_control',   'name' => 'manage_roles',      'display_name' => 'Manage Roles'],
            ['group' => 'access_control',   'name' => 'manage_users',      'display_name' => 'Manage Users'],

            // Maps
            ['group' => 'maps',             'name' => 'view_maps',         'display_name' => 'View Maps'],
            ['group' => 'maps',             'name' => 'manage_maps',       'display_name' => 'Manage Maps'],

            // MikroTik Monitor
            ['group' => 'mikrotik_monitor', 'name' => 'view_monitor',      'display_name' => 'View Monitor'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], [
                'display_name' => $perm['display_name'],
                'group'        => $perm['group'],
            ]);
        }

        // Create default roles
        $allPermissions = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin'], [
            'display_name' => 'Super Administrator',
            'description'  => 'Full access to all features',
        ]);
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        $admin = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description'  => 'Full access except user management',
        ]);
        $adminPerms = $allPermissions->whereNotIn('group', ['access_control'])->pluck('id');
        $admin->permissions()->sync($adminPerms);

        $manager = Role::firstOrCreate(['name' => 'manager'], [
            'display_name' => 'Manager',
            'description'  => 'View and limited edit access',
        ]);
        $managerPerms = $allPermissions->whereIn('name', [
            'view_dashboard', 'view_subscribers', 'view_routers', 'view_packages',
            'view_payments', 'view_expenses', 'view_reports', 'view_maps', 'view_monitor',
        ])->pluck('id');
        $manager->permissions()->sync($managerPerms);

        $reseller = Role::firstOrCreate(['name' => 'reseller'], [
            'display_name' => 'Reseller',
            'description'  => 'View subscribers and payments only',
        ]);
        $resellerPerms = $allPermissions->whereIn('name', [
            'view_dashboard', 'view_subscribers', 'view_payments',
        ])->pluck('id');
        $reseller->permissions()->sync($resellerPerms);

        $this->command->info('Permissions and roles seeded successfully.');
    }
}
