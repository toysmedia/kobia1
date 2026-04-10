<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('wan_ip');
            $table->string('radius_secret')->default('testing123');
            $table->string('wan_interface')->default('ether1');
            $table->string('customer_interface')->default('ether2');
            $table->string('pppoe_pool_range')->default('10.10.0.1-10.10.255.254');
            $table->string('hotspot_pool_range')->default('192.168.1.1-192.168.1.254');
            $table->string('billing_domain')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('routers'); }
};
