<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Main database seeder — runs all system-level seeders.
 * Run with: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            PricingPlanSeeder::class,
            CmsContentSeeder::class,
            CommunitySeeder::class,
        ]);
    }
}
