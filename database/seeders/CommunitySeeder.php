<?php

namespace Database\Seeders;

use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'General Discussion',  'icon' => 'chat-dots',     'color' => '#6c757d', 'description' => 'General topics for the ISP community'],
            ['name' => 'Technical Help',       'icon' => 'tools',         'color' => '#0d6efd', 'description' => 'Get help with technical issues'],
            ['name' => 'Network Setup',        'icon' => 'diagram-3',     'color' => '#198754', 'description' => 'Network infrastructure and setup guides'],
            ['name' => 'MikroTik Tips',        'icon' => 'router',        'color' => '#fd7e14', 'description' => 'Tips and tricks for MikroTik devices'],
            ['name' => 'Business & Growth',    'icon' => 'graph-up-arrow', 'color' => '#20c997', 'description' => 'Growing your ISP business'],
            ['name' => 'Feature Requests',     'icon' => 'lightbulb',     'color' => '#6f42c1', 'description' => 'Suggest features for OxNet'],
            ['name' => 'Announcements',        'icon' => 'megaphone',     'color' => '#dc3545', 'description' => 'Official OxNet announcements'],
        ];

        foreach ($categories as $index => $cat) {
            CommunityCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'        => $cat['name'],
                    'icon'        => $cat['icon'],
                    'color'       => $cat['color'],
                    'description' => $cat['description'],
                    'order'       => $index + 1,
                    'is_active'   => true,
                ]
            );
        }

        $tags = [
            'pppoe', 'mikrotik', 'mpesa', 'billing', 'network',
            'fiber', 'wireless', 'troubleshooting', 'setup', 'kenya-isp',
        ];

        foreach ($tags as $tagName) {
            CommunityTag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName, 'usage_count' => 0]
            );
        }

        $this->command->info('Community categories and tags seeded successfully.');
    }
}
