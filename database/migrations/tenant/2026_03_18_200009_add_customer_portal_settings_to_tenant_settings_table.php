<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds default customer portal settings into the tenant_settings KV store.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'customer_portal_enabled',        'value' => '0', 'group' => 'general', 'is_encrypted' => false],
            ['key' => 'customer_registration_enabled',  'value' => '0', 'group' => 'general', 'is_encrypted' => false],
            ['key' => 'customer_self_service_enabled',  'value' => '0', 'group' => 'general', 'is_encrypted' => false],
        ];

        foreach ($defaults as $setting) {
            DB::table('tenant_settings')->insertOrIgnore(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('tenant_settings')
            ->whereIn('key', [
                'customer_portal_enabled',
                'customer_registration_enabled',
                'customer_self_service_enabled',
            ])
            ->delete();
    }
};
