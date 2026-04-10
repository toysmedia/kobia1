<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->unsignedTinyInteger('provision_phase')->default(0)->after('is_active');
            $table->timestamp('last_heartbeat_at')->nullable()->after('provision_phase');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['provision_phase', 'last_heartbeat_at']);
        });
    }
};
