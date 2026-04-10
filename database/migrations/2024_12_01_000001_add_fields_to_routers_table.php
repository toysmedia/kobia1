<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->string('ref_code')->unique()->nullable()->after('id');
            $table->string('model')->nullable()->after('name');
            $table->string('routeros_version')->nullable()->after('model');
            $table->string('vpn_ip')->nullable()->after('wan_ip');
            $table->string('wan_ip')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropUnique(['ref_code']);
            $table->dropColumn(['ref_code', 'model', 'routeros_version', 'vpn_ip']);
            $table->string('wan_ip')->nullable(false)->change();
        });
    }
};
