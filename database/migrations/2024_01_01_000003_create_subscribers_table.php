<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('username')->unique();
            $table->string('password_hash'); // bcrypt for login
            $table->string('radius_password'); // cleartext for RADIUS (encrypted at rest)
            $table->foreignId('isp_package_id')->nullable()->constrained('isp_packages')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->enum('connection_type', ['pppoe', 'hotspot'])->default('pppoe');
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->enum('created_by', ['admin', 'self', 'mpesa'])->default('admin');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('subscribers'); }
};
