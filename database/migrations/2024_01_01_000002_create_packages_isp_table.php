<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('isp_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('speed_upload')->default(1); // Mbps
            $table->unsignedInteger('speed_download')->default(1); // Mbps
            $table->decimal('price', 10, 2)->default(0); // KES
            $table->unsignedInteger('validity_days')->default(30);
            $table->unsignedInteger('validity_hours')->default(0);
            $table->enum('type', ['pppoe', 'hotspot', 'both'])->default('both');
            $table->unsignedBigInteger('data_limit_mb')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('isp_packages'); }
};
