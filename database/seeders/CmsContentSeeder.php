<?php

namespace Database\Seeders;

use App\Models\System\CmsContent;
use Illuminate\Database\Seeder;

/**
 * Seeds default guest/public page CMS content for the OxNet home page.
 * Super Admin can override all values from the CMS dashboard.
 */
class CmsContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = [
            // Hero section
            ['section' => 'hero', 'key' => 'headline',    'value' => 'The #1 ISP Management Platform for Kenyan Internet Providers', 'type' => 'text'],
            ['section' => 'hero', 'key' => 'subheadline', 'value' => 'Automate billing, manage PPPoE customers, accept M-Pesa payments, and grow your ISP business — all from one powerful platform.', 'type' => 'text'],
            ['section' => 'hero', 'key' => 'cta_primary', 'value' => 'Start Free Trial', 'type' => 'text'],
            ['section' => 'hero', 'key' => 'cta_secondary', 'value' => 'Watch Demo', 'type' => 'text'],

            // Features section
            ['section' => 'features', 'key' => 'title',       'value' => 'Everything Your ISP Needs', 'type' => 'text'],
            ['section' => 'features', 'key' => 'subtitle',    'value' => 'Built specifically for Kenyan ISPs, with M-Pesa integration, RADIUS management, and automated billing.', 'type' => 'text'],

            // Testimonials
            ['section' => 'testimonials', 'key' => 'title', 'value' => 'Trusted by ISPs Across Kenya', 'type' => 'text'],

            // Contact
            ['section' => 'contact', 'key' => 'phone',        'value' => '+254700000000', 'type' => 'text'],
            ['section' => 'contact', 'key' => 'email',        'value' => 'support@oxnet.co.ke', 'type' => 'text'],
            ['section' => 'contact', 'key' => 'whatsapp',     'value' => '+254700000000', 'type' => 'text'],

            // Branding
            ['section' => 'branding', 'key' => 'primary_color',   'value' => '#2563eb', 'type' => 'text'],
            ['section' => 'branding', 'key' => 'secondary_color', 'value' => '#1e40af', 'type' => 'text'],
            ['section' => 'branding', 'key' => 'logo_url',        'value' => '', 'type' => 'image'],
            ['section' => 'branding', 'key' => 'favicon_url',     'value' => '', 'type' => 'image'],

            // Footer
            ['section' => 'footer', 'key' => 'copyright', 'value' => '© ' . date('Y') . ' OxNet. All rights reserved.', 'type' => 'text'],
            ['section' => 'footer', 'key' => 'tagline',   'value' => 'Powering Kenyan ISPs', 'type' => 'text'],
        ];

        foreach ($content as $index => $item) {
            CmsContent::updateOrCreate(
                ['section' => $item['section'], 'key' => $item['key']],
                array_merge($item, ['sort_order' => $index, 'is_active' => true]),
            );
        }

        $this->command->info('✅ CMS content seeded (' . count($content) . ' entries).');
    }
}
