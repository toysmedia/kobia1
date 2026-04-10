<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->integer('openvpn_port')->nullable()->default(443)->after('api_password');
            $table->string('ca_cert_filename')->nullable()->after('openvpn_port');
            $table->string('router_cert_filename')->nullable()->after('ca_cert_filename');
            $table->enum('service_mode', ['pppoe', 'hotspot', 'pppoe_hotspot', 'combined'])
                  ->default('pppoe_hotspot')->after('router_cert_filename');
            $table->string('pppoe_bridge_name')->nullable()->default('pppoe_bridge')->after('service_mode');
            $table->string('hotspot_bridge_name')->nullable()->default('hotspot_bridge')->after('pppoe_bridge_name');
            $table->string('hotspot_gateway_ip')->nullable()->default('11.220.0.1')->after('hotspot_bridge_name');
            $table->integer('hotspot_prefix')->nullable()->default(16)->after('hotspot_gateway_ip');
            $table->string('pppoe_gateway_ip')->nullable()->default('19.225.0.1')->after('hotspot_prefix');
            $table->string('timezone')->nullable()->default('Africa/Nairobi')->after('pppoe_gateway_ip');
            $table->string('billing_server_public_ip')->nullable()->after('timezone');
            $table->string('billing_server_vpn_ip')->nullable()->after('billing_server_public_ip');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn([
                'openvpn_port',
                'ca_cert_filename',
                'router_cert_filename',
                'service_mode',
                'pppoe_bridge_name',
                'hotspot_bridge_name',
                'hotspot_gateway_ip',
                'hotspot_prefix',
                'pppoe_gateway_ip',
                'timezone',
                'billing_server_public_ip',
                'billing_server_vpn_ip',
            ]);
        });
    }
};