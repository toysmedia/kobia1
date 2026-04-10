<?php

namespace Database\Seeders;

use App\Models\System\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the default Super Admin user account.
 * Credentials should be changed immediately after first login.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SuperAdmin::firstOrCreate(
            ['email' => 'superadmin@oxnet.co.ke'],
            [
                'name'      => 'OxNet Super Admin',
                'email'     => 'superadmin@oxnet.co.ke',
                'password'  => Hash::make('ChangeMe@123'),
                'phone'     => null,
                'is_active' => true,
            ],
        );

        $this->command->info('✅ Super Admin seeded. Default email: superadmin@oxnet.co.ke');
        $this->command->warn('⚠️  Change the default password immediately!');
    }
}
