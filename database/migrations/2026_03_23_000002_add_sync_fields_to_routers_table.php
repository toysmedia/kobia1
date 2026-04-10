<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // How often (seconds) the router syncs with the billing server.
            // Default 5s — admins can lower to 1s on capable hardware.
            $table->unsignedSmallInteger('sync_interval')->default(5)->after('last_heartbeat_at');

            // Timestamps and cached stats from the last successful sync.
            $table->timestamp('last_sync_at')->nullable()->after('sync_interval');
            $table->json('last_sync_stats')->nullable()->after('last_sync_at');

            // Current OpenVPN tunnel status as reported by the router.
            $table->string('ovpn_status', 30)->nullable()->after('last_sync_stats');

            // PPPoE authentication method configured on this router.
            $table->string('pppoe_auth_method', 20)->nullable()->default('pap')->after('ovpn_status');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['sync_interval', 'last_sync_at', 'last_sync_stats', 'ovpn_status', 'pppoe_auth_method']);
        });
    }
};