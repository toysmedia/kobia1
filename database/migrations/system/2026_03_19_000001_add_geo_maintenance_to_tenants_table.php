<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('maintenance_mode')->default(false)->after('status');
            $table->decimal('lat', 10, 7)->nullable()->after('maintenance_mode');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }
    public function down(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['maintenance_mode', 'lat', 'lng']);
        });
    }
};
