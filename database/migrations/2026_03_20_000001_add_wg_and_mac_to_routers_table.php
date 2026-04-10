<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // Add only the missing columns
            if (!Schema::hasColumn('routers', 'wg_public_key')) {
                $table->string('wg_public_key')->nullable()->after('vpn_ip');
            }
            
            if (!Schema::hasColumn('routers', 'mac_address')) {
                $table->string('mac_address')->nullable()->after('wg_public_key');
            }
            
            // Note: api_port, api_username, api_password already exist in your table
            // So we should NOT add them again
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['wg_public_key', 'mac_address']);
            // Don't drop api_port, api_username, api_password as they existed before
        });
    }
};